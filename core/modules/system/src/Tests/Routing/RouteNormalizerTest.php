<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Routing\RouteNormalizerTest.
 */

namespace Drupal\system\Tests\Routing;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;
use Drupal\language\Plugin\LanguageNegotiation\LanguageNegotiationUrl;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the route normalizer functionality.
 *
 * @group Routing
 *
 * @see \Drupal\Core\EventSubscriber\RouteNormalizerRequestSubscriber
 */
class RouteNormalizerTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['language', 'path', 'menu_test'];

  /**
   * Tests redirects performed by the route normalizer.
   *
   * @see \Drupal\Core\EventSubscriber\RouteNormalizerRequestSubscriber::onKernelRequestRedirect()
   */
  public function testRedirects() {

    // Test front page redirect.
    $front_page_path = '/user/login';
    $this->config('system.site')->set('page.front', $front_page_path)->save();
    $this->drupalGet(Url::fromUri('base:' . $front_page_path));
    $this->assertNotEqual($this->redirectCount, 0);
    $this->assertUrl(Url::fromRoute('<front>'));

    // Test path alias redirect.
    $this->drupalLogin($this->drupalCreateUser([
      'administer url aliases',
    ]));
    $edit = [
      'source' => '/user/password',
      'alias' => '/my-cool/password/recovery/page',
    ];
    $this->drupalPostForm('admin/config/search/path/add', $edit, t('Save'));
    $this->drupalGet(Url::fromUri('base:' . $edit['source']));
    $this->assertNotEqual($this->redirectCount, 0);
    $this->assertUrl(Url::fromUri('base:' . $edit['alias']));

    // Test language redirect.
    $this->drupalLogin($this->drupalCreateUser([
      'administer languages',
    ]));
    // We need more than one language to make the redirect work.
    $edit = [
      'predefined_langcode' => 'fr',
    ];
    $this->drupalPostForm('admin/config/regional/language/add', $edit, t('Add language'));
    $edit = [
      'language_interface[enabled][language-url]' => 1,
    ];
    $this->drupalPostForm('admin/config/regional/language/detection', $edit, t('Save settings'));
    $edit = [
      'language_negotiation_url_part' => LanguageNegotiationUrl::CONFIG_PATH_PREFIX,
      'prefix[en]' => 'en',
      'prefix[fr]' => 'fr',
    ];
    $this->drupalPostForm('admin/config/regional/language/detection/url', $edit, t('Save configuration'));
    $this->rebuildContainer();
    $url = Url::fromUri('base:/admin/config/regional/language')->setAbsolute()->toString();
    $prefix_count = substr_count($url, '/en/');
    $this->drupalGet($url);
    $this->assertNotEqual($this->redirectCount, 0);
    $this->assertTrue(substr_count($this->url, '/en/') == $prefix_count + 1, 'The default language path prefix was added to the final URL.');
    $this->assertTrue(strpos($this->url, '/admin/config/regional/language') !== FALSE, 'Path preserved.');

    // Test a redirect having special characters in source/destination paths.
    /** @var \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager */
    $menu_link_manager = $this->container->get('plugin.manager.menu.link');
    /** @var \Drupal\Core\Menu\MenuLinkInterface $menu_link */
    $menu_link = $menu_link_manager->createInstance('menu_test.exotic_path');
    $exotic_path = rawurldecode($menu_link->getUrlObject()->getInternalPath());
    $this->drupalLogin($this->drupalCreateUser([
      'administer url aliases',
    ]));
    $edit = [
      'source' => '/' . $exotic_path,
      'alias' => '/' . $exotic_path . rawurlencode(rawurlencode('#%&+/?')),
    ];
    $this->drupalPostForm('admin/config/search/path/add', $edit, t('Save'));
    $this->drupalGet($exotic_path, ['alias' => TRUE]);
    $this->assertNotEqual($this->redirectCount, 0);
    $this->assertTrue(strpos($this->url, UrlHelper::encodePath($edit['alias'])) !== FALSE, 'Redirected to the alias.');
  }

}
