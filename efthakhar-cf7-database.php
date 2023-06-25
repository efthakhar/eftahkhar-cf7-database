<?php
/*
Plugin Name: Efthakhar CF7 Database
Plugin URI: https://wordpress.org/plugins/eftahkhar-cf7-database
Description: Save, Filter and Export Contact From 7 Submissions
Author: Efthakhar Bin Alam
Author URI: https://github.com/efthakhar
Text Domain: eftahkhar-cf7-database
Domain Path: /languages/
Version: 1.0.0
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';


final class EfthakharCF7DB {

	use EfthakharCF7DB\Traits\Singleton;

	public function __construct() {
		$this->define_constants();
		$this->wpdb_table_shortcuts();

		register_activation_hook( __FILE__, [ $this, 'activate' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );

		add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
	}

	public function define_constants() {
		define('EFTHAKHAR_CF7DB', __FILE__ );
		define('EFTHAKHAR_CF7DB_DIR', plugin_dir_url(__FILE__));
	}

	public function wpdb_table_shortcuts() {
		global $wpdb;
		$wpdb->efthakharcf7db_forms       = $wpdb->prefix . 'efthakharcf7db_forms';
		$wpdb->efthakharcf7db_submissions = $wpdb->prefix . 'efthakharcf7db_submissions';
		$wpdb->efthakharcf7db_entries     = $wpdb->prefix . 'efthakharcf7db_entries';
	}

	public function activate() {

		$this->efthakharcf7db_custom_capabilities('add');
		new EfthakharCF7DB\Database\CreateTables();

		do_action( 'efthakharcf7db_activated');
		wp_schedule_single_event( time() + 5, 'efthakharcf7db_sync_exsisting_cf7forms_event' );
	}

	public function init_plugin() {
		$this->init_classes();
		do_action( 'efthakharcf7db_loaded' );
	}

	

	public function init_classes() {

		// Setup Pages And Assets
		EfthakharCF7DB\Pages::instance();
		EfthakharCF7DB\Assets::instance();
		
		// Add Cf7 hook handler Classes
		EfthakharCF7DB\Actions\CF7Actions::instance();

		new EfthakharCF7DB\Api\Forms();
		// new FormCat\Api\Submissions();
	}


	public function deactivate() {
		$this->efthakharcf7db_custom_capabilities('remove');
		$this->remove_database_tables();
	}

	public function remove_database_tables() {
		global $wpdb;
		$tableArray = [
			$wpdb->efthakharcf7db_forms,
			$wpdb->efthakharcf7db_submissions,
			$wpdb->efthakharcf7db_entries,
		];

		foreach ($tableArray as $tablename) {
			$wpdb->query("DROP TABLE IF EXISTS {$tablename}");
		}
	}

	public function efthakharcf7db_custom_capabilities($action) {
		$capabilities = [
			'efthakharcf7db_create_forms',
			'efthakharcf7db_view_forms',
			'efthakharcf7db_edit_forms',
			'efthakharcf7db_delete_forms',
			'efthakharcf7db_create_submissions',
			'efthakharcf7db_view_submissions',
			'efthakharcf7db_edit_submissions',
			'efthakharcf7db_delete_submissions',
		];

		foreach ($capabilities as $capability) {
			$admin_role = get_role('administrator');

			if ($admin_role) {
				'add' == $action ? $admin_role->add_cap($capability) : $admin_role->remove_cap($capability);
			}
		}
	}
}

EfthakharCF7DB::instance();