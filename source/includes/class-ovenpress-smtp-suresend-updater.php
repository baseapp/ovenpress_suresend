<?php

if (! defined('ABSPATH')) exit;

require_once  __dir__ . '/libraries/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$ovenpress_smtp_suresend_update_checker = PucFactory::buildUpdateChecker(
    'https://github.com/baseapp/ovenpress_suresend/',
    __FILE__,
    'ovenpress-smtp-suresend'
);
$ovenpress_smtp_suresend_update_checker->getVcsApi()->enableReleaseAssets();
