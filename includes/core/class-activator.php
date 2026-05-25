<?php
namespace SchoolResultSaaS\Core;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Activator {
	public static function activate() {
		require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'database/class-database-manager.php';
		$db = new DatabaseManager();
		$db->create_tables();
		self::add_capabilities();
		self::set_default_options();
		flush_rewrite_rules();
	}

	private static function add_capabilities() {
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( 'manage_schools' );
			$admin->add_cap( 'manage_school_results' );
		}
	}

	private static function set_default_options() {
		add_option( 'srs_plugin_version', SCHOOL_RESULT_SAAS_VERSION );
		add_option( 'srs_db_version', SCHOOL_RESULT_SAAS_VERSION );
	}
}
