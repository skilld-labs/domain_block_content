<?php

namespace Drupal\domain_block_content\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\domain_block_content\Form\DomainBlockContentCloneForm;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for Entity Clone routes.
 */
class DomainBlockContentRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $route = $collection->get('entity.block_content.clone_form');

    if ($route) {
      $route->setDefault('_form', DomainBlockContentCloneForm::class);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -100];
    return $events;
  }

}
