<?php

if (getenv('AMAZEEIO_SITE_URL')) {
  $options['uri'] = 'http://' . getenv('AMAZEEIO_SITE_URL');
}
$options['package-handler'] = 'git_drupalorg';
$options['gitsubmodule'] = TRUE;
$options['structure-tables']['common'] = array('cache', 'cache_*', 'history', 'search_*', 'sessions', 'watchdog', 'feeds_log');
$command_specific['rsync'] = array('verbose' => TRUE);
