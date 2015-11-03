<?php

/**
 * @file
 * This script updates UUIDs of the configuration stored in the sync
 * directory with UUIDs from the current configuration, so the existing
 * configuration could be imported into the current installation.
 *
 * Run with "drush php-script profiles/amazee/config_prepare.php"
 */

use Drupal\Component\Serialization\Yaml;

if (PHP_SAPI !== 'cli') {
  return;
}

if (!isset($GLOBALS['config_directories'][CONFIG_SYNC_DIRECTORY])) {
  drupal_set_message('Config sync directory is not set in settings.php!', 'error');
  return;
}
$sync_directory = DRUPAL_ROOT . '/' . $GLOBALS['config_directories'][CONFIG_SYNC_DIRECTORY];
if (!is_dir($sync_directory)) {
  drupal_set_message('Config sync directory does not exist!', 'error');
  return;
}

$yaml_files = array_keys(file_scan_directory($sync_directory, '/.*\.yml$/'));
foreach($yaml_files as $file_path) {
  $yaml = Yaml::decode(file_get_contents($file_path));
  if (isset($yaml['uuid'])) {
    $config_name = basename($file_path, '.yml');
    $current_uuid = \Drupal::config($config_name)->get('uuid');
    if ($current_uuid !== NULL) {
      $yaml['uuid'] = $current_uuid;
      file_put_contents($file_path, Yaml::encode($yaml));
      echo "Updated uuid of $config_name configuration.\n";
    }
  }
}
