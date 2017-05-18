<?php

namespace Drupal\ceres_core\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * To serve static templates with ceres theme
 * */
class staticController implements ContainerInjectionInterface {

  /**
   * Twig variable
   * @var \Twig_Environment
   */
  protected $twig;

  /**
   * @param \Twig_Environment $twig
   */
  public function __construct(\Twig_Environment $twig) {
    $this->twig = $twig;
  }

  /**
   * Serve static templates
   * @param string $template_name
   * @return array
   */
  public function viewAction($template_name) {
    $templatePath = drupal_get_path('theme', 'ceres') . '/templates/static/' . $template_name . '.html.twig';
    if (file_exists($templatePath)) {
      $template = $this->twig->loadTemplate($templatePath);
      $markup = $template->render([ 'directory' => \Drupal::theme()->getActiveTheme()->getPath()]);
      $allowed_tags = array_merge(Xss::getAdminTagList(), ["iframe", "form", "input", "button", "select", "option"]);
      return [
        '#markup' => $markup,
        '#allowed_tags' => $allowed_tags,
        '#attached' => ['library' => ['ceres_core/addthis']],
      ];
    }
    else {
      return [
              '#markup' => '<pre>Sorry this template is not defined.<pre>',
      ];
    }
  }

  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('twig')
    );
  }

}
