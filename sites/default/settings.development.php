<?php
/**
 * @file
 * AmazeeIO Drupal 8 development environment configuration file.
 *
 * This file will only be included on development environments.
 */
 // Disable Google Analytics from sending dev GA data.
$config['google_analytics.settings']['account'] = 'UA-XXXXXXXX-YY';

// Disable render caches
$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
