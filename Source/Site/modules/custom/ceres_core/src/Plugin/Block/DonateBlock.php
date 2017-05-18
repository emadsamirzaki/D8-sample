<?php

namespace Drupal\ceres_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\FileUsage\FileUsageInterface;

/**
 * Provides donate custom block
 * @Block(
 *  id = "donate_block",
 *  admin_label = @Translation("Donation block"),
 * )
 */
class DonateBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * @var FileUsageInterface
   */
  protected $fileUsage;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, FileUsageInterface $fileUsage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileUsage = $fileUsage;
  }

  /**
   * Override build() of the donate Block
   * @return array
   */
  public function build() {
    $build = [];
    $config = $this->getConfiguration();

    $build['donate_block'] = array(
      '#theme' => 'donate_block',
      '#content' => $config['content'],
      '#label' => $this->label(),
      '#form' => \Drupal::formBuilder()->getForm('\Drupal\ceres_core\Form\DonateForm')
    );
    return $build;
  }

  /**
   * Add form fields
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['banner_image'] = array(
      '#type' => 'managed_file',
      '#title' => $this->t('Banner Image'),
      '#required' => TRUE,
      '#upload_location' => 'public://images',
      '#file_validate_size' => array(5 * 1024 * 1024),
      '#upload_validators' => array('file_validate_extensions' => array('jpeg jpg png')),
      '#progress_indicator' => 'bar',
      '#progress_message' => 'One moment while file is uploading...',
      '#default_value' => isset($config['imageFid']) ? $config['imageFid'] : '',
    );

    $form['body'] = array(
      '#type' => 'textarea',
      '#title' => "Body",
      '#default_value' => isset($config['content']['body']) ? $config['content']['body'] : ''
    );

    $form['right_column_first'] = array(
      '#type' => 'fieldset',
      '#required' => TRUE,
      '#title' => 'Right Column First Section'
    );

    $form['right_column_first']['title'] = array(
      '#type' => 'textfield',
      '#title' => "Title",
      '#default_value' => isset($config['content']['right_column_first']['title']) ? $config['content']['right_column_first']['title'] : ''
    );

    $form['right_column_first']['body'] = array(
      '#type' => 'textarea',
      '#title' => "Body",
      '#default_value' => isset($config['content']['right_column_first']['body']) ? $config['content']['right_column_first']['body'] : ''
    );

    $form['right_column_first']['link'] = array(
      '#type' => 'textfield',
      '#title' => "Link",
      '#default_value' => isset($config['content']['right_column_first']['link']) ? $config['content']['right_column_first']['link'] : ''
    );

    $form['right_column_second'] = array(
      '#type' => 'fieldset',
      '#required' => TRUE,
      '#title' => 'Right Column Second Section'
    );

    $form['right_column_second']['title'] = array(
      '#type' => 'textfield',
      '#title' => "Title",
      '#default_value' => isset($config['content']['right_column_second']['title']) ? $config['content']['right_column_second']['title'] : ''
    );

    $form['right_column_second']['body'] = array(
      '#type' => 'textarea',
      '#title' => "Body",
      '#default_value' => isset($config['content']['right_column_second']['body']) ? $config['content']['right_column_second']['body'] : ''
    );

    $form['right_column_second']['phone'] = array(
      '#type' => 'textfield',
      '#title' => "Phone",
      '#default_value' => isset($config['content']['right_column_second']['phone']) ? $config['content']['right_column_second']['phone'] : ''
    );
    $form['right_column_second']['email'] = array(
      '#type' => 'email',
      '#title' => "Email",
      '#default_value' => isset($config['content']['right_column_second']['email']) ? $config['content']['right_column_second']['email'] : ''
    );

    return $form;
  }

  /**
   * Handle block save submission
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

    $imageFid = $form_state->getValue('banner_image');
    $image = File::load($imageFid[0]);
    $image->setPermanent();
    $image->save();
    $this->fileUsage->add($image, 'ceres_core', 'block', $imageFid[0]);
    $this->setConfigurationValue('imageFid', $imageFid);

    $content = [
      'banner_image' => $image->getFileUri(),
      'body' => $form_state->getValue("body"),
      'right_column_first' => [
        'title' => $form_state->getValue('right_column_first')['title'],
        'body' => $form_state->getValue('right_column_first')['body'],
        'link' => $form_state->getValue('right_column_first')['link'],
      ],
      'right_column_second' => [
        'title' => $form_state->getValue('right_column_second')['title'],
        'body' => $form_state->getValue('right_column_second')['body'],
        'phone' => $form_state->getValue('right_column_second')['phone'],
        'email' => $form_state->getValue('right_column_second')['email'],
      ],
    ];

    $this->setConfigurationValue('content', $content);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration, $plugin_id, $plugin_definition, $container->get('file.usage')
    );
  }

}
