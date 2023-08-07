<?php
/**
 * @wordpress-plugin
 * Plugin Name:       W3 Total Cache Activator
 * Plugin URI:        https://github.com/wp-activators/w3-total-cache-activator
 * Description:       W3 Total Cache Plugin Activator
 * Version:           1.0.0
 * Requires at least: 3.1.0
 * Requires PHP:      7.1
 * Author:            mohamedhk2
 * Author URI:        https://github.com/mohamedhk2
 **/

defined( 'ABSPATH' ) || exit;
const W3_TOTAL_CACHE_ACTIVATOR_NAME   = 'W3 Total Cache Activator';
const W3_TOTAL_CACHE_ACTIVATOR_DOMAIN = 'w3-total-cache-activator';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
if (
	activator_admin_notice_ignored()
	|| activator_admin_notice_plugin_install( 'w3-total-cache/w3-total-cache.php', 'w3-total-cache', 'W3 Total Cache', W3_TOTAL_CACHE_ACTIVATOR_NAME, W3_TOTAL_CACHE_ACTIVATOR_DOMAIN )
	|| activator_admin_notice_plugin_activate( 'w3-total-cache/w3-total-cache.php', W3_TOTAL_CACHE_ACTIVATOR_NAME, W3_TOTAL_CACHE_ACTIVATOR_DOMAIN )
) {
	return;
}

use W3TC\Dispatcher;

if ( ! defined( 'W3TC_ENTERPRISE' ) ) {
	define( 'W3TC_ENTERPRISE', true );
}
if ( ! defined( 'W3TC_PRO' ) ) {
	define( 'W3TC_PRO', true );
}
ini_set( 'w3tc.license_key', 'free4all-free4all-free4all-free4all' );
set_transient( 'w3tc_license_status', 'valid', 60 * 60 * 24 * 365 * 1000 );
add_action( 'plugins_loaded', function () {
	$config = Dispatcher::config();
	$config->set( 'plugin.license_key', 'free4all-free4all-free4all-free4all' );
	$config->set( 'license.next_check', time() + 60 * 60 * 24 * 365 * 1000 );
	$config->save();
	if ( ! defined( 'W3TC_DIR' ) || ! file_exists( W3TC_DIR . '/ConfigKeys.php' ) ) {
		return;
	}
	include W3TC_DIR . '/ConfigKeys.php';
	foreach ( $overloading_keys_scope as $item ) {
		$option_id = $item['key'];
		$section   = substr( $option_id, 0, strrpos( $option_id, '.' ) );
		$config->set( $section . '.enabled', true );
	}
	$config->save();
} );
add_filter( 'pre_http_request', function ( $pre, $parsed_args, $url ) {
	if ( ! defined( 'W3TC_LICENSE_API_URL' ) ) {
		return $pre;
	}
	if ( strpos( $url, W3TC_LICENSE_API_URL ) !== false ) {
		return activator_json_response( [
			'license_status' => 'active',
			'license_terms'  => 'accept',
		] );
	}

	return $pre;
}, 99, 3 );
