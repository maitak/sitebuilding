<?php

/**
 * @file
 * Contains \Drupal\language\EventSubscriber\LanguageRequestSubscriber.
 */

namespace Drupal\language\EventSubscriber;

use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\Translator\TranslatorInterface;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\language\LanguageNegotiatorInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Sets the $request property on the language manager.
 */
class LanguageRequestSubscriber implements EventSubscriberInterface {

  /**
   * The language manager service.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The language negotiator.
   *
   * @var \Drupal\language\LanguageNegotiatorInterface
   */
  protected $negotiator;

  /**
   * The translation service.
   *
   * @var \Drupal\Core\StringTranslation\Translator\TranslatorInterface;
   */
  protected $translation;

  /**
   * The current active user.
   *
   * @return \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

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
   * The current route match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Constructs a LanguageRequestSubscriber object.
   *
   * @param \Drupal\language\ConfigurableLanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\language\LanguageNegotiatorInterface $negotiator
   *   The language negotiator.
   * @param \Drupal\Core\StringTranslation\Translator\TranslatorInterface $translation;
   *   The translation service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current active user.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator service.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   *   The current route match service.
   */
  public function __construct(ConfigurableLanguageManagerInterface $language_manager, LanguageNegotiatorInterface $negotiator, TranslatorInterface $translation, AccountInterface $current_user, UrlGeneratorInterface $url_generator, PathMatcherInterface $path_matcher, CurrentRouteMatch $current_route_match) {
    $this->languageManager = $language_manager;
    $this->negotiator = $negotiator;
    $this->translation = $translation;
    $this->currentUser = $current_user;
    $this->urlGenerator = $url_generator;
    $this->pathMatcher = $path_matcher;
    $this->currentRouteMatch = $current_route_match;
  }

  /**
   * Sets the request on the language manager.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function onKernelRequestSetLanguage(GetResponseEvent $event) {
    if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
      $this->negotiator->setCurrentUser($this->currentUser);
      $this->negotiator->reset();
      if ($this->languageManager instanceof ConfigurableLanguageManagerInterface) {
        $this->languageManager->setNegotiator($this->negotiator);
        $this->languageManager->setConfigOverrideLanguage($this->languageManager->getCurrentLanguage());
      }
      // After the language manager has initialized, set the default langcode
      // for the string translations.
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
      $this->translation->setDefaultLangcode($langcode);
    }
  }

  /**
   * Performs the language redirect if required.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function onKernelRequestLanguageRedirect(GetResponseEvent $event) {
    $request = $event->getRequest();
    $can_redirect = $event->getRequestType() == HttpKernelInterface::MASTER_REQUEST
      && $request->isMethod('GET')
      && !$request->query->has('destination');
    if ($can_redirect) {
      // Construct URL to the current route. If it is different from the request
      // URL, then we assume that it was changed to match the detected language.
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
    $events[KernelEvents::REQUEST][] = array('onKernelRequestSetLanguage', 255);
    // Execute after routes are initialized in
    /* @see \Drupal\Core\Routing\RoutePreloader::onRequest() */
    $events[KernelEvents::REQUEST][] = array('onKernelRequestLanguageRedirect', -1);

    return $events;
  }

}
