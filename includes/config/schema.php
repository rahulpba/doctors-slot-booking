<?php
namespace RahulK\DSLB;

defined('ABSPATH') || exit;

class Schema {
	private static $constants;
	private static $service_labels;

	// Static initializer to handle dynamic operations
	static function init() {
		self::$constants = [
			'DOCTOR_META_KEY'					=> DSLB_TOKEN.'_doctor_meta',
			'BOOKING_META_KEY'					=> DSLB_TOKEN.'_booking_meta',
		];
	}

	/**
	 * Get schema constant by key.
	 */
	public static function getConstant($key, $default = false) {
		return isset(self::$constants[$key]) ? self::$constants[$key] : $default;
	}
}
