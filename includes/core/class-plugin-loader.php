<?php
/**
 * Plugin Loader Class
 */
namespace SchoolResultSaaS\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PluginLoader {
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function run() {
		$this->load_dependencies();
		$this->register_hooks();
	}

	private function load_dependencies() {
		require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'core/class-base-repository.php';
		require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'core/class-base-service.php';
		require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'database/class-database-manager.php';
		require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'services/class-result-service.php';
		require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'services/class-school-service.php';
		require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'services/class-student-service.php';
		require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'services/class-exam-service.php';
		require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'schools/class-school-repository.php';
		require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'students/class-student-repository.php';
		require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'results/class-result-repository.php';
		require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'pdf/class-pdf-generator.php';
		require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'templates/class-template-engine.php';
		require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'api/class-api-controller.php';
		
		if ( is_admin() ) {
			require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'admin/class-admin-menu.php';
			require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'admin/class-admin-pages.php';
		}
	}

	private function register_hooks() {
		add_action( 'plugins_loaded', array( $this, 'initialize_database' ) );
		add_action( 'rest_api_init', array( $this, 'register_api_routes' ) );
		
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		}
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
	}

	public function initialize_database() {
		$db = new \SchoolResultSaaS\Database\DatabaseManager();
		$db->init();
	}

	public function register_api_routes() {
		$api = new \SchoolResultSaaS\API\ApiController();
		$api->register_routes();
	}

	public function register_admin_menu() {
		$admin_menu = new \SchoolResultSaaS\Admin\AdminMenu();
		$admin_menu->register_menu();
	}

	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'school-result' ) !== false ) {
			wp_enqueue_style( 'srs-admin-style', SCHOOL_RESULT_SAAS_ASSETS_URL . 'css/admin.css', array(), SCHOOL_RESULT_SAAS_VERSION );
			wp_enqueue_script( 'srs-admin-script', SCHOOL_RESULT_SAAS_ASSETS_URL . 'js/admin.js', array( 'jquery' ), SCHOOL_RESULT_SAAS_VERSION, true );
		}
	}

	public function enqueue_public_assets() {
		wp_enqueue_style( 'srs-public-style', SCHOOL_RESULT_SAAS_ASSETS_URL . 'css/public.css', array(), SCHOOL_RESULT_SAAS_VERSION );
		wp_enqueue_script( 'srs-public-script', SCHOOL_RESULT_SAAS_ASSETS_URL . 'js/public.js', array( 'jquery' ), SCHOOL_RESULT_SAAS_VERSION, true );
	}
}
