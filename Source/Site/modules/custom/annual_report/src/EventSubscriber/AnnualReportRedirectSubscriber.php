<?php

// for help https://www.thirdandgrove.com/redirecting-node-pages-drupal-8
// for help https://www.drupal.org/node/2655732#comment-10781558

namespace Drupal\annual_report\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AnnualReportRedirectSubscriber implements EventSubscriberInterface {

  public function checkForRedirection(GetResponseEvent $event) {
    $baseUrl = $event->getRequest()->getBaseUrl();
    $pathInfo = $event->getRequest()->getPathInfo();
    if (null !== $pathInfo && ('/annual-report/' == $pathInfo || '/annual-report' == $pathInfo)) {
      $response = new RedirectResponse($baseUrl . '/annual-report/2016');
      $response->send();
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    return([
      KernelEvents::REQUEST => [
        ['checkForRedirection'],
      ]
    ]);
  }

}
