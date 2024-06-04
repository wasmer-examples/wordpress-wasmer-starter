<?php
/**
 * The main plugin file
 *
 * @package WordPress_Plugins
 * @subpackage OS_Disable_WordPress_Updates
 */

/*
Plugin Name: Disable All WordPress Updates
Description: Disables the theme, plugin and core update checking, the related cronjobs and notification system.
Plugin URI:  https://wordpress.org/plugins/disable-wordpress-updates/
Version:     1.7.1
Author:      Oliver Schlöbe
Author URI:  https://www.schloebe.de/
Text Domain: disable-wordpress-updates
Domain Path: /languages
License:	 GPL2

Copyright 2013-2021 Oliver Schlöbe (email : wordpress@schloebe.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



/**
 * Define the plugin version
 */
define("OSDWPUVERSION", "1.7.1");


/**
 * The OS_Disable_WordPress_Updates class
 *
 * @package 	WordPress_Plugins
 * @subpackage 	OS_Disable_WordPress_Updates
 * @since 		1.3
 * @author 		scripts@schloebe.de
 */
class OS_Disable_WordPress_Updates {
	/**
	 * The OS_Disable_WordPress_Updates class constructor
	 * initializing required stuff for the plugin
	 *
	 * PHP 5 Constructor
	 *
	 * @since 		1.3
	 * @author 		scripts@schloebe.de
	 */
	function __construct() {
		add_action( 'admin_init', array(&$this, 'admin_init') );

		/*
		 * Disable Theme Updates
		 * 2.8 to 3.0
		 */
		add_filter( 'pre_transient_update_themes', array($this, 'last_checked_atm') );
		/*
		 * 3.0
		 */
		add_filter( 'pre_site_transient_update_themes', array($this, 'last_checked_atm') );


		/*
		 * Disable Plugin Updates
		 * 2.8 to 3.0
		 */
		add_action( 'pre_transient_update_plugins', array($this, 'last_checked_atm') );
		/*
		 * 3.0
		 */
		add_filter( 'pre_site_transient_update_plugins', array($this, 'last_checked_atm') );


		/*
		 * Disable Core Updates
		 * 2.8 to 3.0
		 */
		add_filter( 'pre_transient_update_core', array($this, 'last_checked_atm') );
		/*
		 * 3.0
		 */
		add_filter( 'pre_site_transient_update_core', array($this, 'last_checked_atm') );
		
		
		/*
		 * Filter schedule checks
		 *
		 * @link https://wordpress.org/support/topic/possible-performance-improvement/#post-8970451
		 */
		add_action('schedule_event', array($this, 'filter_cron_events'));
		
		add_action( 'pre_set_site_transient_update_plugins', array($this, 'last_checked_atm'), 21, 1 );
		add_action( 'pre_set_site_transient_update_themes', array($this, 'last_checked_atm'), 21, 1 );

		/*
		 * Disable All Automatic Updates
		 * 3.7+
		 *
		 * @author	sLa NGjI's @ slangji.wordpress.com
		 */
		add_filter( 'auto_update_translation', '__return_false' );
		add_filter( 'automatic_updater_disabled', '__return_true' );
		add_filter( 'allow_minor_auto_core_updates', '__return_false' );
		add_filter( 'allow_major_auto_core_updates', '__return_false' );
		add_filter( 'allow_dev_auto_core_updates', '__return_false' );
		add_filter( 'auto_update_core', '__return_false' );
		add_filter( 'wp_auto_update_core', '__return_false' );
		add_filter( 'auto_core_update_send_email', '__return_false' );
		add_filter( 'send_core_update_notification_email', '__return_false' );
		add_filter( 'auto_update_plugin', '__return_false' );
		add_filter( 'auto_update_theme', '__return_false' );
		add_filter( 'automatic_updates_send_debug_email', '__return_false' );
		add_filter( 'automatic_updates_is_vcs_checkout', '__return_true' );

		remove_action( 'init', 'wp_schedule_update_checks' );
		remove_all_filters( 'plugins_api' );

		add_filter( 'automatic_updates_send_debug_email ', '__return_false', 1 );
		if( !defined( 'AUTOMATIC_UPDATER_DISABLED' ) ) define( 'AUTOMATIC_UPDATER_DISABLED', true );
		if( !defined( 'WP_AUTO_UPDATE_CORE') ) define( 'WP_AUTO_UPDATE_CORE', false );

		add_filter( 'pre_http_request', array($this, 'block_request'), 10, 3 );
	}


	/**
	 * Initialize and load the plugin stuff
	 *
	 * @since 		1.3
	 * @author 		scripts@schloebe.de
	 */
	function admin_init() {
		if ( !function_exists("remove_action") ) return;

		if ( current_user_can( 'update_core' ) ) {
			add_action( 'admin_bar_menu', array($this, 'add_adminbar_items'), 100 );
			add_action( 'admin_enqueue_scripts', array($this, 'admin_css_overrides') );
		}
		
		/*
		 * Remove 'update plugins' option from bulk operations select list
		 */
		global $current_user;
		$current_user->allcaps['update_plugins'] = 0;
		
		/*
		 * Hide maintenance and update nag
		 */
		add_filter( 'site_status_tests', array($this, 'site_status_tests') );
		remove_action( 'admin_notices', 'update_nag', 3 );
		remove_action( 'network_admin_notices', 'update_nag', 3 );
		remove_action( 'admin_notices', 'maintenance_nag' );
		remove_action( 'network_admin_notices', 'maintenance_nag' );
		

		/*
		 * Disable Theme Updates
		 * 2.8 to 3.0
		 */
		remove_action( 'load-themes.php', 'wp_update_themes' );
		remove_action( 'load-update.php', 'wp_update_themes' );
		remove_action( 'admin_init', '_maybe_update_themes' );
		remove_action( 'wp_update_themes', 'wp_update_themes' );
		wp_clear_scheduled_hook( 'wp_update_themes' );


		/*
		 * 3.0
		 */
		remove_action( 'load-update-core.php', 'wp_update_themes' );
		wp_clear_scheduled_hook( 'wp_update_themes' );


		/*
		 * Disable Plugin Updates
		 * 2.8 to 3.0
		 */
		remove_action( 'load-plugins.php', 'wp_update_plugins' );
		remove_action( 'load-update.php', 'wp_update_plugins' );
		remove_action( 'admin_init', '_maybe_update_plugins' );
		remove_action( 'wp_update_plugins', 'wp_update_plugins' );
		wp_clear_scheduled_hook( 'wp_update_plugins' );

		/*
		 * 3.0
		 */
		remove_action( 'load-update-core.php', 'wp_update_plugins' );
		wp_clear_scheduled_hook( 'wp_update_plugins' );


		/*
		 * Disable Core Updates
		 * 2.8 to 3.0
		 */
		add_filter( 'pre_option_update_core', '__return_null' );

		remove_action( 'wp_version_check', 'wp_version_check' );
		remove_action( 'admin_init', '_maybe_update_core' );
		wp_clear_scheduled_hook( 'wp_version_check' );


		/*
		 * 3.0
		 */
		wp_clear_scheduled_hook( 'wp_version_check' );


		/*
		 * 3.7+
		 */
		remove_action( 'wp_maybe_auto_update', 'wp_maybe_auto_update' );
		remove_action( 'admin_init', 'wp_maybe_auto_update' );
		remove_action( 'admin_init', 'wp_auto_update_core' );
		wp_clear_scheduled_hook( 'wp_maybe_auto_update' );
		
		remove_all_filters( 'plugins_api' );
	}



	/**
	 * Hide update checks in the Site Health screen
	 *
	 * @since 		1.6.8
	 */
	public function site_status_tests($tests) {
		unset( $tests['async']['background_updates'] );
		unset( $tests['direct']['plugin_theme_auto_updates'] );
		return $tests;
	}



	/**
	 * Add notice to admin bar when plugin is active
	 *
	 * @since 		1.7.0
	 */
	public function add_adminbar_items($admin_bar) {
		$plugin_data = get_plugin_data( __FILE__ );

		$admin_bar->add_menu( array(
			'id'    => 'dwuos-notice',
			'title' => '<span class="dashicons dashicons-info" aria-hidden="true"></span>',
			'href'  => network_admin_url('plugins.php'),
			'meta'  => array(
				'class' => 'wp-admin-bar-dwuos-notice',
				'title' => sprintf(
					/* translators: %s: Name of the plugin */
					__( '"%s" plugin is enabled!', 'disable-wordpress-updates' ),
					$plugin_data['Name']
				)
			),
		));
	}



	/**
	 * Apply CSS styles to admin bar notice
	 *
	 * @since 		1.7.0
	 */
	public function admin_css_overrides() {
		wp_add_inline_style( 'admin-bar', '.wp-admin-bar-dwuos-notice { background-color: rgba(190, 0, 0, 0.4) !important; } .wp-admin-bar-dwuos-notice .dashicons { font-family: dashicons !important; }' );
	}


	/**
	 * Check the outgoing request
	 *
	 * @since 		1.4.4
	 */
	public function block_request($pre, $args, $url) {
		/* Empty url */
		if( empty( $url ) ) {
			return $pre;
		}

		/* Invalid host */
		if( !$host = parse_url($url, PHP_URL_HOST) ) {
			return $pre;
		}

		$url_data = parse_url( $url );

		/* block request */
		if( false !== stripos( $host, 'api.wordpress.org' ) &&
		    isset( $url_data['path'] ) &&
		    (false !== stripos( $url_data['path'], 'update-check' ) ||
		     false !== stripos( $url_data['path'], 'version-check' ) ||
		     false !== stripos( $url_data['path'], 'browse-happy' ) ||
		     false !== stripos( $url_data['path'], 'serve-happy' )) ) {
			return true;
		}

		return $pre;
	}


	/**
	 * Filter cron events
	 *
	 * @since 		1.5.0
	 */
	public function filter_cron_events($event) {
		switch( $event->hook ) {
			case 'wp_version_check':
			case 'wp_update_plugins':
			case 'wp_update_themes':
			case 'wp_maybe_auto_update':
				$event = false;
				break;
		}
		return $event;
	}
	
	
	/**
	 * Override version check info
	 *
	 * @since 		1.6.0
	 */
	public function last_checked_atm( $t ) {
		include( ABSPATH . WPINC . '/version.php' );
		
		$current = new stdClass;
		$current->updates = array();
		$current->version_checked = $wp_version;
		$current->last_checked = time();
		
		return $current;
	}
}

if ( class_exists('OS_Disable_WordPress_Updates') ) {
	$OS_Disable_WordPress_Updates = new OS_Disable_WordPress_Updates();
}