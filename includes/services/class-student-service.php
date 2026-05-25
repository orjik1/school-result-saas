<?php
namespace SchoolResultSaaS\Services;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class StudentService extends \SchoolResultSaaS\Core\BaseService {
	private $db;
	private $table;

	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
		$this->table = $this->db->prefix . 'srs_students';
	}

	public function create_student( $school_id, $data ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return false;
		}

		$student_data = array(
			'school_id'   => $school_id,
			'student_uid' => $this->sanitize_string( $data['student_uid'] ),
			'firstname'   => $this->sanitize_string( $data['firstname'] ),
			'lastname'    => $this->sanitize_string( $data['lastname'] ),
			'class'       => $this->sanitize_string( $data['class'] ?? '' ),
			'photo'       => $data['photo'] ?? '',
		);

		// Check for duplicate student_uid
		$existing = $this->db->get_row(
			$this->db->prepare(
				"SELECT id FROM {$this->table} WHERE school_id = %d AND student_uid = %s",
				$school_id, $student_data['student_uid']
			)
		);

		if ( $existing ) {
			return false; // Student UID already exists
		}

		if ( $this->db->insert( $this->table, $student_data ) ) {
			return $this->db->insert_id;
		}
		return false;
	}

	public function update_student( $school_id, $student_id, $data ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return false;
		}

		$update_data = array();
		if ( isset( $data['firstname'] ) ) {
			$update_data['firstname'] = $this->sanitize_string( $data['firstname'] );
		}
		if ( isset( $data['lastname'] ) ) {
			$update_data['lastname'] = $this->sanitize_string( $data['lastname'] );
		}
		if ( isset( $data['class'] ) ) {
			$update_data['class'] = $this->sanitize_string( $data['class'] );
		}
		if ( isset( $data['photo'] ) ) {
			$update_data['photo'] = $data['photo'];
		}

		if ( empty( $update_data ) ) {
			return false;
		}

		return $this->db->update(
			$this->table,
			$update_data,
			array( 'id' => $student_id, 'school_id' => $school_id )
		);
	}

	public function get_student( $school_id, $student_id ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return null;
		}

		return $this->db->get_row(
			$this->db->prepare(
				"SELECT * FROM {$this->table} WHERE id = %d AND school_id = %d",
				$student_id, $school_id
			),
			ARRAY_A
		);
	}

	public function get_student_by_uid( $school_id, $student_uid ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return null;
		}

		return $this->db->get_row(
			$this->db->prepare(
				"SELECT * FROM {$this->table} WHERE school_id = %d AND student_uid = %s",
				$school_id, $student_uid
			),
			ARRAY_A
		);
	}

	public function get_students_by_class( $school_id, $class, $limit = 100, $offset = 0 ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return array();
		}

		return $this->db->get_results(
			$this->db->prepare(
				"SELECT * FROM {$this->table} WHERE school_id = %d AND class = %s ORDER BY lastname ASC LIMIT %d OFFSET %d",
				$school_id, $class, $limit, $offset
			),
			ARRAY_A
		);
	}

	public function get_all_students( $school_id, $limit = 100, $offset = 0 ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return array();
		}

		return $this->db->get_results(
			$this->db->prepare(
				"SELECT * FROM {$this->table} WHERE school_id = %d ORDER BY lastname ASC LIMIT %d OFFSET %d",
				$school_id, $limit, $offset
			),
			ARRAY_A
		);
	}

	public function search_students( $school_id, $search_term, $limit = 50 ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return array();
		}

		$search = '%' . $this->db->esc_like( $search_term ) . '%';

		return $this->db->get_results(
			$this->db->prepare(
				"SELECT * FROM {$this->table} WHERE school_id = %d AND (firstname LIKE %s OR lastname LIKE %s OR student_uid LIKE %s) LIMIT %d",
				$school_id, $search, $search, $search, $limit
			),
			ARRAY_A
		);
	}

	public function delete_student( $school_id, $student_id ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return false;
		}

		return $this->db->delete(
			$this->table,
			array( 'id' => $student_id, 'school_id' => $school_id )
		);
	}

	public function get_class_list( $school_id ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return array();
		}

		return $this->db->get_col(
			$this->db->prepare(
				"SELECT DISTINCT class FROM {$this->table} WHERE school_id = %d AND class IS NOT NULL AND class != '' ORDER BY class ASC",
				$school_id
			)
		);
	}

	public function count_students( $school_id ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return 0;
		}

		return intval(
			$this->db->get_var(
				$this->db->prepare(
					"SELECT COUNT(*) FROM {$this->table} WHERE school_id = %d",
					$school_id
				)
			)
		);
	}

	public function bulk_create_students( $school_id, $students_data ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return false;
		}

		$success_count = 0;
		$error_count = 0;
		$errors = array();

		foreach ( $students_data as $index => $student ) {
			$result = $this->create_student( $school_id, $student );
			if ( $result ) {
				$success_count++;
			} else {
				$error_count++;
				$errors[] = array(
					'row'    => $index + 1,
					'uid'    => $student['student_uid'] ?? 'N/A',
					'reason' => 'Duplicate UID or invalid data',
				);
			}
		}

		return array(
			'success' => $success_count,
			'errors'  => $error_count,
			'details' => $errors,
		);
	}
}
