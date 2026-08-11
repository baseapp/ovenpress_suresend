<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.wpoven.com/plugins/ovenpress-smtp-suresend
 * @since             1.0.0
 * @package           Ovenpress_Smtp_Suresend
 *
 * @wordpress-plugin
 * Plugin Name:       Ovenpress SMTP Suresend
 * Plugin URI:        https://www.wpoven.com/plugins/ovenpress-smtp-suresend
 * Description:       Activate the SMTP plugin to secure your site's email delivery by configuring the SMTP server of your preferred mail service.
 * Version:           1.0.3
 * Author:            WPOven
 * Author URI:        https://www.wpoven.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ovenpress-smtp-suresend
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('OVENPRESS_SMTP_SURESEND_VERSION', '1.0.3');
if (!defined('OVENPRESS_SMTP_SURESEND_SLUG'))
	define('OVENPRESS_SMTP_SURESEND_SLUG', 'ovenpress-smtp-suresend');

define('OVENPRESS_SMTP_SURESEND', 'Ovenpress SMTP Suresend');
define('OVENPRESS_SMTP_SURESEND_ROOT_PL', __FILE__);
define('OVENPRESS_SMTP_SURESEND_ROOT_URL', plugins_url('', OVENPRESS_SMTP_SURESEND_ROOT_PL));
define('OVENPRESS_SMTP_SURESEND_ROOT_DIR', dirname(OVENPRESS_SMTP_SURESEND_ROOT_PL));
define('OVENPRESS_SMTP_SURESEND_PLUGIN_DIR', plugin_dir_path(__DIR__));
define('OVENPRESS_SMTP_SURESEND_PLUGIN_BASE', plugin_basename(OVENPRESS_SMTP_SURESEND_ROOT_PL));
define('OVENPRESS_SURESEND_PATH', realpath(plugin_dir_path(OVENPRESS_SMTP_SURESEND_ROOT_PL)) . '/');


if (file_exists(plugin_dir_path(__FILE__) . 'includes/class-ovenpress-smtp-suresend-updater.php')) {
	require_once plugin_dir_path(__FILE__) . 'includes/class-ovenpress-smtp-suresend-updater.php';
}

function ovenpress_smtp_suresend_create_logs_table()
{
	global $wpdb;

	$table_name = $wpdb->prefix . 'ovenpress_smtp_suresend_logs';

	if ($wpdb->get_var(
		$wpdb->prepare(
			'SHOW TABLES LIKE %s',
			$table_name
		)
	) !== $table_name) {

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
            id INT NOT NULL AUTO_INCREMENT,
					time DATETIME NOT NULL,
					recipient VARCHAR(255) NOT NULL,
					subject VARCHAR(255) NOT NULL,
					headers VARCHAR(255) NOT NULL,
					status VARCHAR(20) NOT NULL,
					message TEXT NOT NULL,
					smtplogs TEXT,
					PRIMARY KEY (id)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta($sql);
	}
}

register_activation_hook(__FILE__, 'ovenpress_smtp_suresend_create_logs_table');


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ovenpress-smtp-suresend-activator.php
 */
function ovenpress_smtp_suresend_activate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-ovenpress-smtp-suresend-activator.php';
	Ovenpress_Smtp_Suresend_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ovenpress-smtp-suresend-deactivator.php
 */
function ovenpress_smtp_suresend_deactivate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-ovenpress-smtp-suresend-deactivator.php';
	Ovenpress_Smtp_Suresend_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'ovenpress_smtp_suresend_activate');
register_deactivation_hook(__FILE__, 'ovenpress_smtp_suresend_deactivate');

register_activation_hook(__FILE__, function () {
	if (!wp_next_scheduled('ovenpress_smtp_log_cleanup_event')) {
		wp_schedule_event(time(), 'hourly', 'ovenpress_smtp_log_cleanup_event');
	}
});

register_deactivation_hook(__FILE__, function () {
	wp_clear_scheduled_hook('ovenpress_smtp_log_cleanup_event');
});


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-ovenpress-smtp-suresend.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
add_action('plugins_loaded', function () {
	$plugin = new Ovenpress_Smtp_Suresend();
	$plugin->run();
});


function ovenpress_smtp_suresend_plugin_settings_link($links)
{
	$settings_link = '<a href="' . admin_url('admin.php?page=' . OVENPRESS_SMTP_SURESEND_SLUG) . '">Settings</a>';

	array_push($links, $settings_link);
	return $links;
}
add_filter('plugin_action_links_' . OVENPRESS_SMTP_SURESEND_PLUGIN_BASE, 'ovenpress_smtp_suresend_plugin_settings_link');
