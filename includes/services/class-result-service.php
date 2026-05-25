<?php
namespace SchoolResultSaaS\Services;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class ResultService extends \SchoolResultSaaS\Core\BaseService {
	private $db;
	private $table;
	private $grades = array(
		'A' => array( 'min' => 70, 'max' => 100, 'remark' => 'Excellent' ),
		'B' => array( 'min' => 60, 'max' => 69, 'remark' => 'Good' ),
		'C' => array( 'min' => 50, 'max' => 59, 'remark' => 'Credit' ),
		'D' => array( 'min' => 45, 'max' => 49, 'remark' => 'Pass' ),
		'E' => array( 'min' => 40, 'max' => 44, 'remark' => 'Pass' ),
		'F' => array( 'min' => 0, 'max' => 39, 'remark' => 'Fail' ),
	);

	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
		$this->table = $this->db->prefix . 'srs_results';
	}

	public function compute_total( $ca_score, $exam_score ) {
		return floatval( $ca_score ) + floatval( $exam_score );
	}

	public function compute_grade( $total ) {
		$total = floatval( $total );
		foreach ( $this->grades as $grade => $range ) {
			if ( $total >= $range['min'] && $total <= $range['max'] ) {
				return $grade;
			}
		}
		return 'F';
	}

	public function get_grade_remark( $grade ) {
		return isset( $this->grades[ $grade ] ) ? $this->grades[ $grade ]['remark'] : 'Unknown';
	}

	public function save_result( $school_id, $student_id, $subject_id, $exam_id, $ca_score, $exam_score ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return false;
		}

		$total = $this->compute_total( $ca_score, $exam_score );
		$grade = $this->compute_grade( $total );

		$data = array(
			'school_id'  => $school_id,
			'student_id' => $student_id,
			'subject_id' => $subject_id,
			'exam_id'    => $exam_id,
			'ca_score'   => $ca_score,
			'exam_score' => $exam_score,
			'total'      => $total,
			'grade'      => $grade,
		);

		$existing = $this->db->get_row(
			$this->db->prepare(
				"SELECT id FROM {$this->table} WHERE school_id = %d AND student_id = %d AND subject_id = %d AND exam_id = %d",
				$school_id, $student_id, $subject_id, $exam_id
			)
		);

		if ( $existing ) {
			return $this->db->update( $this->table, $data, array( 'id' => $existing->id ) );
		} else {
			return $this->db->insert( $this->table, $data );
		}
	}

	public function get_student_results( $school_id, $student_id, $exam_id = null ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return array();
		}

		$query = $this->db->prepare(
			"SELECT r.*, s.name as subject_name, st.firstname, st.lastname 
			FROM {$this->table} r
			JOIN {$this->db->prefix}srs_subjects s ON r.subject_id = s.id
			JOIN {$this->db->prefix}srs_students st ON r.student_id = st.id
			WHERE r.school_id = %d AND r.student_id = %d",
			$school_id, $student_id
		);

		if ( $exam_id ) {
			$query = $this->db->prepare(
				"SELECT r.*, s.name as subject_name, st.firstname, st.lastname 
				FROM {$this->table} r
				JOIN {$this->db->prefix}srs_subjects s ON r.subject_id = s.id
				JOIN {$this->db->prefix}srs_students st ON r.student_id = st.id
				WHERE r.school_id = %d AND r.student_id = %d AND r.exam_id = %d",
				$school_id, $student_id, $exam_id
			);
		}

		return $this->db->get_results( $query, ARRAY_A );
	}

	public function get_exam_results( $school_id, $exam_id ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return array();
		}

		$query = $this->db->prepare(
			"SELECT r.*, s.name as subject_name, st.firstname, st.lastname, st.class
			FROM {$this->table} r
			JOIN {$this->db->prefix}srs_subjects s ON r.subject_id = s.id
			JOIN {$this->db->prefix}srs_students st ON r.student_id = st.id
			WHERE r.school_id = %d AND r.exam_id = %d
			ORDER BY r.grade DESC, r.total DESC",
			$school_id, $exam_id
		);

		return $this->db->get_results( $query, ARRAY_A );
	}

	public function calculate_positions( $school_id, $exam_id, $class = null ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return false;
		}

		$results = $this->get_exam_results( $school_id, $exam_id );

		if ( empty( $results ) ) {
			return false;
		}

		// Group by class if specified
		$grouped = array();
		foreach ( $results as $result ) {
			$class_name = $class ?: $result['class'];
			if ( ! isset( $grouped[ $class_name ] ) ) {
				$grouped[ $class_name ] = array();
			}
			$grouped[ $class_name ][] = $result;
		}

		// Calculate positions per class
		foreach ( $grouped as $class_name => $class_results ) {
			usort( $class_results, function( $a, $b ) {
				$a_total = floatval( $a['total'] );
				$b_total = floatval( $b['total'] );
				return $b_total <=> $a_total;
			});

			$position = 1;
			foreach ( $class_results as $result ) {
				$this->db->update(
					$this->table,
					array( 'position' => $position ),
					array( 'id' => $result['id'] )
				);
				$position++;
			}
		}

		return true;
	}

	public function get_student_average( $school_id, $student_id, $exam_id = null ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return 0;
		}

		$query = $this->db->prepare(
			"SELECT AVG(total) as average FROM {$this->table}
			WHERE school_id = %d AND student_id = %d",
			$school_id, $student_id
		);

		if ( $exam_id ) {
			$query = $this->db->prepare(
				"SELECT AVG(total) as average FROM {$this->table}
				WHERE school_id = %d AND student_id = %d AND exam_id = %d",
				$school_id, $student_id, $exam_id
			);
		}

		$result = $this->db->get_row( $query );
		return $result ? floatval( $result->average ) : 0;
	}

	public function get_class_statistics( $school_id, $exam_id, $class ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return array();
		}

		$results = $this->db->get_results(
			$this->db->prepare(
				"SELECT r.*, st.class
				FROM {$this->table} r
				JOIN {$this->db->prefix}srs_students st ON r.student_id = st.id
				WHERE r.school_id = %d AND r.exam_id = %d AND st.class = %s",
				$school_id, $exam_id, $class
			),
			ARRAY_A
		);

		return array(
			'total_students' => count( array_unique( array_column( $results, 'student_id' ) ) ),
			'avg_score'      => array_sum( array_column( $results, 'total' ) ) / max( count( $results ), 1 ),
			'pass_count'     => count( array_filter( $results, function( $r ) { return $r['grade'] !== 'F'; } ) ),
			'fail_count'     => count( array_filter( $results, function( $r ) { return $r['grade'] === 'F'; } ) ),
		);
	}

	public function bulk_upload_results( $school_id, $exam_id, $results_data ) {
		if ( ! $this->validate_school_id( $school_id ) ) {
			return false;
		}

		$success_count = 0;
		$error_count = 0;

		foreach ( $results_data as $data ) {
			$result = $this->save_result(
				$school_id,
				$data['student_id'],
				$data['subject_id'],
				$exam_id,
				$data['ca_score'],
				$data['exam_score']
			);

			if ( $result ) {
				$success_count++;
			} else {
				$error_count++;
			}
		}

		$this->calculate_positions( $school_id, $exam_id );

		return array(
			'success' => $success_count,
			'errors'  => $error_count,
		);
	}
}
