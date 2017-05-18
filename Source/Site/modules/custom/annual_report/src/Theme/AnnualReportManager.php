<?php

namespace Drupal\annual_report\Theme;

use Drupal\annual_report\AnnualReportParams;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Class AnnualReportManager.
 *
 * @package Drupal\annual_report
 */
class AnnualReportManager implements ThemeNegotiatorInterface {

  /**
   * @var AnnualReportParams
   */
  protected $AnnualReportParams;

  public function __construct() {
    $this->AnnualReportParams = new AnnualReportParams('ceres_annual_report', '/annual-report');
  }

  public function applies(RouteMatchInterface $route_match) {
    // Use this theme on a path pattern.
    $current_path = \Drupal::service('path.current')->getPath();
    $current_alias = \Drupal::service('path.alias_manager')->getAliasByPath($current_path);
    return preg_match($this->AnnualReportParams->getPathPattern(), $current_alias);
  }

  public function determineActiveTheme(RouteMatchInterface $route_match) {
    // Here you return the actual theme name.
    return $this->AnnualReportParams->getThemeName();
  }

}
