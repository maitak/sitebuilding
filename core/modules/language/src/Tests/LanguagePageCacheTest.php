<?php

/**
 * @file
 * Contains \Drupal\language\Tests\LanguagePageCacheTest.
 */

namespace Drupal\language\Tests;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\language\Plugin\LanguageNegotiation\LanguageNegotiationUrl;
use Drupal\simpletest\WebTestBase;

/**
 * It tests browser language detection and page caching.
 *
 * @group language
 */
class LanguagePageCacheTest extends WebTestBase {

  /**
   * The added languages.
   *
   * @var array
   */
  protected $langcodes;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('locale', 'language', 'block', 'language_test');

  /**
   * Adds additional languages.
   */
  protected function setupLanguages() {
    $this->langcodes = array('fr');
    foreach ($this->langcodes as $langcode) {
      ConfigurableLanguage::createFromLangcode($langcode)->save();
    }
    array_unshift($this->langcodes, \Drupal::languageManager()->getDefaultLanguage()->getId());
  }

  protected function setUp() {
    parent::setUp();
    $this->setupLanguages();

    // Create and login user.
    $admin_user = $this->drupalCreateUser(array('administer blocks', 'administer languages', 'access administration pages'));
    $this->drupalLogin($admin_user);
  }

  /**
   * It tests browser language detection and page caching.
   */
  function testPageCache() {
    $this->drupalGet('admin/config/regional/language/detection');
    $this->setLanguageNegotiationOrder();
    $edit = array(
      'language_negotiation_url_part' => LanguageNegotiationUrl::CONFIG_PATH_PREFIX,
      'prefix[en]' => 'en',
      'prefix[fr]' => 'fr',
    );
    $this->drupalPostForm('admin/config/regional/language/detection/url', $edit, t('Save configuration'));

    // Enable the language switching block.
    $block = $this->drupalPlaceBlock('language_block:' . LanguageInterface::TYPE_INTERFACE, array(
      'id' => 'test_language_block',
      // Ensure a 2-byte UTF-8 sequence is in the tested output.
      'label' => $this->randomMachineName(8) . 'Ã—',
    ));

    $this->drupalLogout();
    $this->setCache();

    $path = 'language_test/type-link-active-class';

    $this->drupalGet('language_test/type-link-active-class', array(
      'language' => $this->container->get('language_manager')->getLanguage('fr'),
    ));
    $links = $this->getLinks($path);
    $this->assert($links['fr']['class'] == 'is-active', 'The "en" is defied');

    $this->getPage($path, 'en');
    $links = $this->getLinks($path);
    $this->assert($links['en']['class'] == 'is-active', 'The "en" is defied');

    // It should be redirected to the fr version.
    $this->getPage($path, 'fr');
    $links = $this->getLinks($path);
    $this->assert($links['fr']['class'] == 'is-active', 'The "fr" is defied');
  }

  public function setCache($period=300) {
    $config = $this->config('system.performance');
    $config->set('cache.page.max_age', $period);
    $config->save();
  }

  public function setLanguageNegotiationOrder() {
    $method_weights = [
      'language-url' => '-20',
      'language-browser' => '-19',
      'language-selected' => '-15'
    ];
    $language_negotiator = \Drupal::service('language_negotiator');
    $language_negotiator->saveConfiguration(LanguageInterface::TYPE_INTERFACE, $method_weights);
    $this->config('language.types')->set('negotiation.' . LanguageInterface::TYPE_INTERFACE . '.method_weights', $method_weights)->save();
    $this->rebuildContainer();
    \Drupal::service('router.builder')->rebuild();
  }

  public function getPage($path, $langcode_browser_fallback='en') {
    $http_header = array('Accept-Language: ' . $langcode_browser_fallback . ';q=1');
    $language = new Language(array('id' => ''));
    $this->drupalGet($path, array('language' => $language), $http_header);
  }

  public function getLinks($path) {
    return [
      'no_lang' => $this->xpath('//a[@id = :id and @data-drupal-link-system-path = :path]', array(':id' => 'no_lang_link', ':path' => $path))[0],
      'en' => $this->xpath('//a[@id = :id and @hreflang = :lang and @data-drupal-link-system-path = :path]', array(':id' => 'en_link', ':lang' => 'en', ':path' => $path))[0],
      'fr' => $this->xpath('//a[@id = :id and @hreflang = :lang and @data-drupal-link-system-path = :path]', array(':id' => 'fr_link', ':lang' => 'fr', ':path' => $path))[0]
    ];
  }

}