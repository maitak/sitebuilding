<?php
/**
 * @file
 * AmazeeIO Drupal 8 production environment configuration file.
 *
 * This file will only be included on production environments.
 */
 
# On production, we enable the cache and the css and js aggregation.
$config['system.performance']['cache']['page']['max_age'] = 86400;
$config['system.performance']['css']['preprocess'] = 1;
$config['system.performance']['js']['preprocess'] = 1;
