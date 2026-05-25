<?php
namespace SchoolResultSaaS\Core;

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class BaseService {
	protected function sanitize_string( $value ) {
		return sanitize_text_field( $value );
	}

	protected function sanitize_email( $value ) {
		return sanitize_email( $value );
	}

	protected function validate_school_id( $school_id ) {
		return is_numeric( $school_id ) && $school_id > 0;
	}

	protected function get_user_school_id() {
		if ( ! is_user_logged_in() ) { return null; }
		$user_id = get_current_user_id();
		return intval( get_user_meta( $user_id, 'school_id', true ) );
	}
}
