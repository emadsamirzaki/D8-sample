<?php

namespace Drupal\ceres_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * AddThis social share depending on the page meta information.
 * @Block(
 *  id = "page_social_share",
 *  admin_label = @Translation("Page Social Share"),
 * )
 */
class PageSocialShare extends BlockBase {
  public function build() {
    return ['page_social_share' => [
      '#theme' => 'page_social_share',
      '#attached' => ['library' => ['ceres_core/addthis']],
    ]];
  }

}
