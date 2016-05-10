<?php

/**
 * @file
 * Contains \Drupal\Core\EventSubscriber\RouteNormalizerRequestSubscriber.
 */

namespace Drupal\Core\EventSubscriber;

use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\RequestHelper;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Normalizes GET requests performing a redirect if required.
 *
 * Not every but most of GET requests are processed. All conditions can be found
 * in shouldRedirect() method.
 *
 * The normalization can be disabled by setting the "_disable_route_normalizer"
 * request parameter to TRUE. However, this should be done before
 * onKernelRequestRedirect() method is executed.
 */
class RouteNormalizerRequestSubscriber implements EventSubscriberInterface {

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
   * The value of the route_normalizer_enabled container parameter.
   *
   * @var bool
   */
  protected $routeNormalizerEnabled;

  /**
   * Constructs a RouteNormalizerRequestSubscriber object.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator service.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   * @param bool $route_normalizer_enabled
   *   The value of the route_normalizer_enabled container parameter.
   */
  public function __construct(UrlGeneratorInterface $url_generator, PathMatcherInterface $path_matcher, $route_normalizer_enabled) {
    $this->urlGenerator = $url_generator;
    $this->pathMatcher = $path_matcher;
    $this->routeNormalizerEnabled = $route_normalizer_enabled;
  }

  /**
   * Performs a redirect if the URL changes in routing.
   *
   * The redirect happens if a URL constructed from the current route is
   * different from the requested one. Examples:
   * - Language negotiation system detected a language to use, and that language
   *   has a path prefix: perform a redirect to the language prefixed URL.
   * - A route that's set as the front page is requested: redirect to the front
   *   page.
   * - Requested path has an alias: redirect to alias.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function onKernelRequestRedirect(GetResponseEvent $event) {
    if ($this->shouldRedirect($event)) {
      $request = $event->getRequest();
      // The "<current>" placeholder can be used for all routes except the front
      // page because it's not a real route.
      $route_name = $this->pathMatcher->isFrontPage() ? '<front>' : '<current>';
      $options = [
        'query' => $request->query->all(),
        'absolute' => TRUE,
      ];
      $redirect_uri = $this->urlGenerator->generateFromRoute($route_name, [], $options);
      $original_uri = $request->getSchemeAndHttpHost() . $request->getRequestUri();
      if ($redirect_uri != $original_uri) {
        $response = new RedirectResponse($redirect_uri);
        $response->headers->set('X-Drupal-Route-Normalizer', 1);
        $event->setResponse($response);
      }
    }
  }

  /**
   * Detects if a redirect can be performed during the current request.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   *
   * @return bool
   */
  protected function shouldRedirect(GetResponseEvent $event) {
    return $this->routeNormalizerEnabled
      && $event->isMasterRequest()
      && ($request = $event->getRequest())
      && $request->isMethod('GET')
      && !$request->query->has('destination')
      && RequestHelper::isCleanUrl($request)
      && !$request->attributes->get('_disable_route_normalizer');
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('onKernelRequestRedirect');
    return $events;
  }

}
