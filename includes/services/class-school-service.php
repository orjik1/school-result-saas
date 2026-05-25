<?php
namespace SchoolResultSaaS\Services;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class SchoolService extends \SchoolResultSaaS\Core\BaseService {
	private $db;
	private $table;

	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
		$this->table = $this->db->prefix . 'srs_schools';
	}

	public function create_school( $data ) {
		$data = array(
			'name'                 => $this->sanitize_string( $data['name'] ),
			'slug'                 => sanitize_title( $data['slug'] ?? $data['name'] ),
			'logo'                 => $data['logo'] ?? '',
			'address'              => $data['address'] ?? '',
			'email'                => $this->sanitize_email( $data['email'] ?? '' ),
			'phone'                => $this->sanitize_string( $data['phone'] ?? '' ),
			'motto'                => $data['motto'] ?? '',
			'school_color'         => $data['school_color'] ?? '#000000',
			'principal_name'       => $this->sanitize_string( $data['principal_name'] ?? '' ),
			'principal_signature'  => $data['principal_signature'] ?? '',
			'result_template'      => $data['result_template'] ?? 'classic',
		);

		if ( $this->db->insert( $this->table, $data ) ) {
			return $this->db->insert_id;
		}
		return false;
	}

	public function update_school( $school_id, $data ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return false;
		}

		$update_data = array();
		if ( isset( $data['name'] ) ) {
			$update_data['name'] = $this->sanitize_string( $data['name'] );
		}
		if ( isset( $data['email'] ) ) {
			$update_data['email'] = $this->sanitize_email( $data['email'] );
		}
		if ( isset( $data['phone'] ) ) {
			$update_data['phone'] = $this->sanitize_string( $data['phone'] );
		}
		if ( isset( $data['motto'] ) ) {
			$update_data['motto'] = $data['motto'];
		}
		if ( isset( $data['school_color'] ) ) {
			$update_data['school_color'] = $data['school_color'];
		}
		if ( isset( $data['principal_name'] ) ) {
			$update_data['principal_name'] = $this->sanitize_string( $data['principal_name'] );
		}
		if ( isset( $data['result_template'] ) ) {
			$update_data['result_template'] = $this->sanitize_string( $data['result_template'] );
		}
		if ( isset( $data['logo'] ) ) {
			$update_data['logo'] = $data['logo'];
		}
		if ( isset( $data['principal_signature'] ) ) {
			$update_data['principal_signature'] = $data['principal_signature'];
		}

		if ( empty( $update_data ) ) {
			return false;
		}

		return $this->db->update( $this->table, $update_data, array( 'id' => $school_id ) );
	}

	public function get_school( $school_id ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return null;
		}

		return $this->db->get_row(
			$this->db->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $school_id ),
			ARRAY_A
		);
	}

	public function get_school_by_slug( $slug ) {
		return $this->db->get_row(
			$this->db->prepare( "SELECT * FROM {$this->table} WHERE slug = %s", $slug ),
			ARRAY_A
		);
	}

	public function get_all_schools( $limit = 100, $offset = 0 ) {
		return $this->db->get_results(
			$this->db->prepare(
				"SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$limit, $offset
			),
			ARRAY_A
		);
	}

	public function delete_school( $school_id ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return false;
		}

		return $this->db->delete( $this->table, array( 'id' => $school_id ) );
	}

	public function get_school_stats( $school_id ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return null;
		}

		$stats = array(
			'total_students' => 0,
			'total_classes'  => 0,
			'total_subjects' => 0,
			'total_exams'    => 0,
		);

		$stats['total_students'] = $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->db->prefix}srs_students WHERE school_id = %d",
				$school_id
			)
		);

		$stats['total_classes'] = $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->db->prefix}srs_classes WHERE school_id = %d",
				$school_id
			)
		);

		$stats['total_subjects'] = $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->db->prefix}srs_subjects WHERE school_id = %d",
				$school_id
			)
		);

		$stats['total_exams'] = $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->db->prefix}srs_exams WHERE school_id = %d",
				$school_id
			)
		);

		return $stats;
	}
}
