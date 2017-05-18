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
 * Provides custom block section before user registration form
 * @Block(
 *  id = "user_register_block",
 *  admin_label = @Translation("User Register block"),
 * )
 */
class RegisterBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $config = $this->getConfiguration();

    $build['user_register_block'] = array(
      '#theme' => 'register_block',
      '#content' => $config['content'],
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

    $form['body'] = array(
      '#type' => 'textarea',
      '#title' => "Body",
      '#default_value' => isset($config['content']['body']) ? $config['content']['body'] : ''
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
      'body' => $form_state->getValue("body"),
    ];

    $this->setConfigurationValue('content', $content);
  }

}
