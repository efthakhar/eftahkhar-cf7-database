<?php

namespace EfthakharCF7DB\Database;

class CreateTables {
	
	public function __construct() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$this->create_efthakharcf7db_forms_table();
		$this->create_efthakharcf7db_submissions_table();
		$this->create_efthakharcf7db_entries_table();
	}

	private function prefix() {
		global $wpdb;

		return $wpdb->prefix;
	}

	private function create_efthakharcf7db_forms_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}efthakharcf7db_forms` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`cf7_id` bigint(20) unsigned NOT NULL,
			`name` varchar(255) NOT NULL,
			`fields` text,
			`created_by` bigint(20) unsigned,
			`updated_by` bigint(20) unsigned,
			`created_at` datetime,
			`updated_at` datetime,
			PRIMARY KEY (`id`)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	private function create_efthakharcf7db_submissions_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}efthakharcf7db_submissions` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`form_id` bigint(20) unsigned NOT NULL,
			`date` datetime,
            `custom_data` longtext,
			PRIMARY KEY (`id`)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	private function create_efthakharcf7db_entries_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}efthakharcf7db_entries` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`form_id` bigint(20) unsigned NOT NULL,
			`submission_id` bigint(20) unsigned NOT NULL,
			`field` varchar(100) NOT NULL,
			`value` longtext,
			
			PRIMARY KEY (`id`)
		) {$charset_collate};";

		dbDelta( $sql );
	}

}
