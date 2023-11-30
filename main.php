<?php
/**
 * @wordpress-plugin
 * Plugin Name:       W3 Total Cache Activ@tor
 * Plugin URI:        https://bit.ly/wtc-act
 * Description:       W3 Total Cache Plugin Activ@tor
 * Version:           1.3.0
 * Requires at least: 5.9.0
 * Requires PHP:      7.2
 * Author:            moh@medhk2
 * Author URI:        https://bit.ly/medhk2
 **/

defined( 'ABSPATH' ) || exit;
$PLUGIN_NAME   = 'W3 Total Cache Activ@tor';
$PLUGIN_DOMAIN = 'w3-total-cache-activ@tor';
extract( require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php' );
if (
	$admin_notice_ignored()
	|| $admin_notice_plugin_install( 'w3-total-cache/w3-total-cache.php', 'w3-total-cache', 'W3 Total Cache', $PLUGIN_NAME, $PLUGIN_DOMAIN )
	|| $admin_notice_plugin_activate( 'w3-total-cache/w3-total-cache.php', $PLUGIN_NAME, $PLUGIN_DOMAIN )
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
add_filter( 'pre_http_request', function ( $pre, $parsed_args, $url ) use ( $json_response ) {
	if ( ! defined( 'W3TC_LICENSE_API_URL' ) ) {
		return $pre;
	}
	if ( strpos( $url, W3TC_LICENSE_API_URL ) !== false ) {
		return $json_response( [
			'license_status' => 'active',
			'license_terms'  => 'accept',
		] );
	}

	return $pre;
}, 99, 3 );
