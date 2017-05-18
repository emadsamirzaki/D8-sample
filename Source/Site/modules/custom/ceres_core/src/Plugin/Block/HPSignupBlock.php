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
 * Provides homepage signup custom block section
 * @Block(
 *  id = "hp_signup_block",
 *  admin_label = @Translation("Homepage Signup block"),
 * )
 */
class HPSignupBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * @var FileUsageInterface
   */
  protected $fileUsage;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, FileUsageInterface $fileUsage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileUsage = $fileUsage;
  }

  /**
   * Override build() of the Signup Block
   * @return array
   */
  public function build() {
    $build = [];
    $config = $this->getConfiguration();

    $build['hp_signup_block'] = array(
      '#theme' => 'hp_signup',
      '#content' => $config['content'],
      '#label' => $this->label(),
      '#form' => \Drupal::formBuilder()->getForm('\Drupal\ceres_core\Form\EmailSignup', $config['content']['type'])
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

    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => "Title",
      '#default_value' => isset($config['content']['title']) ? $config['content']['title'] : ''
    );

    $form['body'] = array(
      '#type' => 'textarea',
      '#title' => "Body",
      '#default_value' => isset($config['content']['body']) ? $config['content']['body'] : ''
    );

    $form['type'] = array(
      '#type' => 'select',
      '#options' => array(
        'Ceres eCommunity' => 'Main Newsletter',
        'Corporate Leadership Newsletter' => 'Corporate leadership program newsletter',
        'Investor Leadership Newsletter' => 'Investor leadership program newsletter',
      ),
      '#default' => isset($config['content']['type']) ? $config['content']['type'] : '',
    );

    $form['url'] = array(
      '#type' => 'textfield',
      '#title' => "Newsletter Url",
      '#description' => "Start your path with '/'. An example path is /about-us ",
      '#default_value' => isset($config['content']['url']) ? $config['content']['url'] : ''
    );

    $form['submit_text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Submit Text'),
      '#description' => 'Submit button text',
      '#default_value' => isset($config['content']['submitText']) ? $config['content']['submitText'] : ''
    );

     $form['image'] = array(
      '#type' => 'managed_file',
      '#title' => $this->t('Image'),
      '#upload_location' => 'public://images',
      '#file_validate_size' => array(5 * 1024 * 1024),
      '#upload_validators' => array('file_validate_extensions' => array('jpeg jpg png')),
      '#progress_indicator' => 'bar',
      '#progress_message' => 'One moment while file is uploading...',
      '#default_value' => isset($config['imageFid']) ? $config['imageFid'] : '',
    );

    return $form;
  }

  /**
   * Handle block save submission
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $content = [
      'title' => $form_state->getValue("title"),
      'body' => $form_state->getValue("body"),
      'type' => $form_state->getValue("type"),
      'url' => $form_state->getValue("url"),
      'submitText' => $form_state->getValue('submit_text'),
    ];

     if ($form_state->getValue('image')) {
      $imageFid = $form_state->getValue('image');
      $image = File::load($imageFid[0]);
      $image->setPermanent();
      $image->save();
      $this->fileUsage->add($image, 'ceres_core', 'block', $imageFid[0]);
      $content['image'] = $image->getFileUri();
      $this->setConfigurationValue('imageFid', $imageFid);
    }

    $this->setConfigurationValue('content',  $content);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration, $plugin_id, $plugin_definition, $container->get('file.usage')
    );
  }

}
