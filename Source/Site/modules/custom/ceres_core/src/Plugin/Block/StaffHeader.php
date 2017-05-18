<?php

namespace Drupal\ceres_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides Staff single page header section
 * @Block(
 *  id = "staff_header",
 *  admin_label = @Translation("Staff Header"),
 * )
 */
class StaffHeader extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * @var CurrentRouteMatch
   */
  protected $currentRoute;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentRouteMatch $currentRoute) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentRoute = $currentRoute;
  }

  /**
   * Override build() of the Signup Block
   * @return array
   */
  public function build() {
    $build = [];
    $config = $this->getConfiguration();
    $content = [];
    if ($node = $this->currentRoute->getParameter('node')) {
      if ('staff' == $node->getType()) {
        $content['image'] = ($node->field_staff_picture->entity) ? ImageStyle::load('staff')->buildUrl($node->field_staff_picture->entity->getFileUri()) : '/' . drupal_get_path('theme', 'ceres') . '/assets/images/expert/expert.png';
        $content['twitter'] = $node->field_staff_twitter->getString();
        $content['email'] = $node->field_staff_email->getString();
      }
      else if ('director' == $node->getType()) {
        $content['image'] = ($node->field_director_picture->entity) ? ImageStyle::load('staff')->buildUrl($node->field_director_picture->entity->getFileUri()) : '/' . drupal_get_path('theme', 'ceres') . '/assets/images/expert/expert.png';
//        $content['group'] = ($node->field_director_group->getString()) ? $node->field_director_group->getString() : NULL;
      }
      else if ('press_contact' == $node->getType()) {
        $content['image'] = ($node->field_staff_picture->entity) ? ImageStyle::load('staff')->buildUrl($node->field_staff_picture->entity->getFileUri()) : '/' . drupal_get_path('theme', 'ceres') . '/assets/images/expert/expert.png';
        $content['phone'] = ($node->field_press_contact_phone->getString()) ? $node->field_press_contact_phone->getString() : NULL;
        $content['email'] = ($node->field_press_contact_email->getString()) ? $node->field_press_contact_email->getString() : NULL;
      }
    }
    elseif ($term = $this->currentRoute->getParameter('taxonomy_term')) {
      $content['image'] = ($term->field_author_picture->entity) ? ImageStyle::load('staff')->buildUrl($term->field_author_picture->entity->getFileUri()) : '/' . drupal_get_path('theme', 'ceres') . '/assets/images/expert/expert.png';
      $content['twitter'] = $term->field_author_twitter->getString();
      $content['email'] = $term->field_author_email->getString();
    }

    /* @var $node \Drupal\node\Entity\Node */
    $build['staff_header'] = array(
            '#theme' => 'staff_header_section',
            '#content' => $content,
            '#cache' => [
                    'contexts' => ['url.path'],
                    'max-age' => 0,
            ],
    );

    return $build;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration, $plugin_id, $plugin_definition, $container->get('current_route_match')
    );
  }

}
