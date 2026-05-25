<?php
namespace SchoolResultSaaS\Core;

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class BaseRepository {
	protected $db;
	protected $table;

	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
	}

	public function get( $id ) {
		if ( ! $this->table ) { return null; }
		$query = $this->db->prepare( "SELECT * FROM {$this->table} WHERE id = %d", intval( $id ) );
		return $this->db->get_row( $query, ARRAY_A );
	}

	public function get_all( $args = array() ) {
		if ( ! $this->table ) { return array(); }
		$defaults = array( 'where' => array(), 'orderby' => 'id', 'order' => 'ASC', 'limit' => null, 'offset' => 0 );
		$args = wp_parse_args( $args, $defaults );
		$query = "SELECT * FROM {$this->table}";
		if ( ! empty( $args['where'] ) ) {
			$conditions = array();
			foreach ( $args['where'] as $column => $value ) {
				$conditions[] = $this->db->prepare( "{$column} = %s", $value );
			}
			if ( ! empty( $conditions ) ) { $query .= ' WHERE ' . implode( ' AND ', $conditions ); }
		}
		if ( ! empty( $args['orderby'] ) ) { $query .= " ORDER BY {$args['orderby']} {$args['order']}"; }
		if ( ! empty( $args['limit'] ) ) { $query .= $this->db->prepare( ' LIMIT %d, %d', intval( $args['offset'] ), intval( $args['limit'] ) ); }
		return $this->db->get_results( $query, ARRAY_A );
	}

	public function create( $data ) {
		if ( ! $this->table ) { return false; }
		if ( $this->db->insert( $this->table, $data ) ) { return $this->db->insert_id; }
		return false;
	}

	public function update( $id, $data ) {
		if ( ! $this->table ) { return false; }
		return $this->db->update( $this->table, $data, array( 'id' => $id ) );
	}

	public function delete( $id ) {
		if ( ! $this->table ) { return false; }
		return $this->db->delete( $this->table, array( 'id' => $id ) );
	}
}
