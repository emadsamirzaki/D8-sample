<?php

namespace Drupal\ceres_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * AddThis social share depending on the page meta information.
 * @Block(
 *  id = "header_call_to_action",
 *  admin_label = @Translation("Header Call-to-action Menu"),
 * )
 */
class HeaderCallToAction extends BlockBase {
  public function build() {
    if (\Drupal::currentUser()->isAnonymous()) {
      $items[] = [
        'url' => Url::fromRoute('user.register'),
        'title' => $this->t('Sign Up'),
      ];
    }
    else {
      $items[] = [
        'url' => Url::fromRoute('user.logout'),
        'title' => $this->t('Log Out'),
      ];
    }
    $items[] = [
      'url' => Url::fromUri('internal:/donate'),
      'title' =>  $this->t('Donate'),
    ];
    return ['call_to_action' => [
      '#theme' => 'header_call_to_action',
      '#items' => $items,
      '#cache' => [
        'contexts' => ['user.roles'],
      ],
    ]];
  }

}
