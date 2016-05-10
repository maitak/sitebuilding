<?php

/**
 * @file
 * Contains \Drupal\system\EventSubscriber\RequestSubscriber.
 */

namespace Drupal\system\EventSubscriber;

use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Sets the $request property on the language manager.
 */
class RequestSubscriber implements EventSubscriberInterface {

  /**
   * The URL generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The path matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * Constructs a RequestSubscriber object.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator service.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   */
  public function __construct(UrlGeneratorInterface $url_generator, PathMatcherInterface $path_matcher) {
    $this->urlGenerator = $url_generator;
    $this->pathMatcher = $path_matcher;
  }

  /**
   * Performs a redirect if the path changed in routing.
   *
   * For example, when language negotiation selected a different language for
   * the page.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function onKernelRequestRedirect(GetResponseEvent $event) {
    $request = $event->getRequest();
    $can_redirect = $event->isMasterRequest()
      && $request->isMethod('GET')
      && !$request->query->has('destination');
    if ($can_redirect) {
      // Construct URL to the current route. If it is different from the request
      // URL, then we assume that it was changed on a purpose (for example, to
      // match the detected language) and perform a redirect.
      $route_name = $this->pathMatcher->isFrontPage() ? '<front>' : '<current>';
      $options = [
        'query' => $request->query->all(),
        'absolute' => TRUE,
      ];
      $redirect_uri = $this->urlGenerator->generateFromRoute($route_name, [], $options);
      $original_uri = $request->getSchemeAndHttpHost() . $request->getRequestUri();
      if ($redirect_uri != $original_uri) {
        $event->setResponse(new RedirectResponse($redirect_uri));
      }
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  static function getSubscribedEvents() {
    // Execute after routes are initialized in
    // \Drupal\Core\Routing\RoutePreloader::onRequest().
    $events[KernelEvents::REQUEST][] = array('onKernelRequestRedirect', -1);

    return $events;
  }

}
