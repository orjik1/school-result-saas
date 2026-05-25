<?php
namespace SchoolResultSaaS\Database;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class DatabaseManager {
	private $db;
	private $prefix = 'srs_';

	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
	}

	public function init() {
		$this->create_tables_if_not_exists();
	}

	public function create_tables() {
		$this->create_tables_if_not_exists();
	}

	private function create_tables_if_not_exists() {
		$schools_table = $this->db->prefix . $this->prefix . 'schools';
		$students_table = $this->db->prefix . $this->prefix . 'students';
		$classes_table = $this->db->prefix . $this->prefix . 'classes';
		$subjects_table = $this->db->prefix . $this->prefix . 'subjects';
		$exams_table = $this->db->prefix . $this->prefix . 'exams';
		$results_table = $this->db->prefix . $this->prefix . 'results';
		$subscriptions_table = $this->db->prefix . $this->prefix . 'subscriptions';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// Schools table
		$sql = "CREATE TABLE IF NOT EXISTS {$schools_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			slug varchar(255) NOT NULL UNIQUE,
			logo varchar(500),
			address text,
			email varchar(255),
			phone varchar(20),
			motto text,
			school_color varchar(7),
			principal_name varchar(255),
			principal_signature varchar(500),
			result_template varchar(50) DEFAULT 'classic',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY slug (slug)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
		dbDelta( $sql );

		// Students table
		$sql = "CREATE TABLE IF NOT EXISTS {$students_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			school_id bigint(20) NOT NULL,
			student_uid varchar(100) NOT NULL,
			firstname varchar(255) NOT NULL,
			lastname varchar(255) NOT NULL,
			class varchar(100),
			photo varchar(500),
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY unique_student_uid (school_id, student_uid),
			KEY school_id (school_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
		dbDelta( $sql );

		// Classes table
		$sql = "CREATE TABLE IF NOT EXISTS {$classes_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			school_id bigint(20) NOT NULL,
			name varchar(255) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY school_id (school_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
		dbDelta( $sql );

		// Subjects table
		$sql = "CREATE TABLE IF NOT EXISTS {$subjects_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			school_id bigint(20) NOT NULL,
			name varchar(255) NOT NULL,
			code varchar(50),
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY school_id (school_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
		dbDelta( $sql );

		// Exams table
		$sql = "CREATE TABLE IF NOT EXISTS {$exams_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			school_id bigint(20) NOT NULL,
			name varchar(255) NOT NULL,
			term varchar(50),
			session varchar(50),
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY school_id (school_id),
			KEY term_session (term, session)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
		dbDelta( $sql );

		// Results table
		$sql = "CREATE TABLE IF NOT EXISTS {$results_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			school_id bigint(20) NOT NULL,
			student_id bigint(20) NOT NULL,
			subject_id bigint(20) NOT NULL,
			exam_id bigint(20) NOT NULL,
			ca_score decimal(5,2) DEFAULT 0,
			exam_score decimal(5,2) DEFAULT 0,
			total decimal(5,2) DEFAULT 0,
			grade varchar(2),
			position int(11),
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY unique_result (school_id, student_id, subject_id, exam_id),
			KEY school_id (school_id),
			KEY student_id (student_id),
			KEY exam_id (exam_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
		dbDelta( $sql );

		// Subscriptions table
		$sql = "CREATE TABLE IF NOT EXISTS {$subscriptions_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			school_id bigint(20) NOT NULL,
			plan varchar(100),
			status varchar(50) DEFAULT 'active',
			amount decimal(10,2),
			reference varchar(255),
			starts_at datetime,
			expires_at datetime,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY school_id (school_id),
			KEY status (status)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
		dbDelta( $sql );
	}

	public function get_table_name( $table ) {
		return $this->db->prefix . $this->prefix . $table;
	}
}
