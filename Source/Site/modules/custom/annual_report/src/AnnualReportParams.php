<?php

namespace Drupal\annual_report;

/**
 * Parameters for Banner theme
 */
class AnnualReportParams {

  const THEME_NAME = 'ceres_annual_report';

  protected $themeName;
  protected $pathBase;
  protected $pathPattern;

  public function __construct($theme_name = THEME_NAME, $path_base = "/") {
    $this->themeName = $theme_name;
    $this->pathBase = $path_base;
    $this->generatePathPattern($path_base);
  }

  public function getThemeName() {
    return $this->themeName;
  }

  public function getPathPattern() {
    return $this->pathPattern;
  }
  public function getPathBase() {
    return $this->pathBase;
  }

  public function setPathBase($path_base) {
    $this->pathBase = $path_base;
    $this->generatePathPattern($path_base);
  }
  protected function generatePathPattern($path_base) {
    $pattern = '#^' . $path_base . '#';
    $this->pathPattern = $pattern;
  }

}
