<?php

namespace Drupal\ceres_core\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Routing\TrustedRedirectResponse;
use \Drupal\node\Entity\Node;

/**
 * @author yousab
 */
class NewsEventSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['handleResponse'];
    return $events;
  }

  public function handleResponse(GetResponseEvent $responseEvent) {
    $request = $responseEvent->getRequest();
    $pathInfo = $request->getPathInfo();

    /* @var $node  Node */
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      $type = $node->getType();
      if ('news' == $type && !empty($node->field_news_url->getValue()) &&
          !(strpos($pathInfo, 'edit'))) {
        $url = file_create_url($node->field_news_url->getValue()[0]['uri']);
        $responseEvent->setResponse(new TrustedRedirectResponse($url));
      }
      else if ('press' == $type && !empty($node->field_press_link->getValue()) &&
          !(strpos($pathInfo, 'edit'))) {
        $url = file_create_url($node->field_press_link->getValue()[0]['uri']);
        $responseEvent->setResponse(new TrustedRedirectResponse($url));
      }
    }
  }

}
