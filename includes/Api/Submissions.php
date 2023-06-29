<?php

namespace EfthakharCF7DB\Api;

use WP_Error;

class Submissions {
	public function __construct() {
		add_action( 'rest_api_init', [$this, 'efthakharcf7db_submissions_routes'] );
	}

	public function efthakharcf7db_submissions_routes() {
		register_rest_route( 'efthakharcf7db/v1', '/submissions/', [
			'methods'             => 'GET',
			'callback'            => [$this, 'get_submissions'],
			'permission_callback' => [ $this, 'get_submissions_permissions_check' ],
		]);
	}

	public function get_submissions( $request ) {
		global $wpdb;
         
		$form_id = $request->get_param('form_id');
		$page    = $request->get_param('page') ?? 1;
		$perpage = $request->get_param('perpage') ?? 10;
		$offset  = $perpage * ( $page - 1 );

		// get the form details information
		$form_details = $wpdb->get_row("SELECT * FROM {$wpdb->efthakharcf7db_forms} WHERE `cf7_id` = {$form_id} ", ARRAY_A);

		// get the details information of form fields
		$form_fields_details = json_decode($form_details['fields'], true);
		// get the names of all possible fileds of this form
		$form_fields = array_column($form_fields_details, 'name');

		$joiningSQL = '';
		// creating the sql join statement based on all possible fileds
		for ($i = 0;$i < count($form_fields);++$i) {
			$joiningSQL .= " INNER JOIN `{$wpdb->efthakharcf7db_entries}` AS e{$i} ON (s.`id` = e{$i}.`submission_id`) ";
		}

		// the main sql part for finding desired grouped entries
		// based on condition
		$sqlForDesiredEntries = "FROM {$wpdb->efthakharcf7db_submissions} AS s {$joiningSQL} WHERE 1=1
		   AND s.form_id = {$form_id}
			--    AND 
			-- 	(
			-- 	  (e1.field = 'your-email' AND e1.value LIKE '%11%')
			--   )
		GROUP BY s.id ";

		// grouped entries based on conditions
		$grouped_entries = $wpdb->get_results("SELECT s.id {$sqlForDesiredEntries} LIMIT {$perpage} OFFSET {$offset}", ARRAY_A);

		// extract only ids as array from founded grouped entries
		$submission_ids_fulfilling_conditions = array_column($grouped_entries, 'id');
		// convert array to string format to use in sql
		$submission_ids_string = implode(',', $submission_ids_fulfilling_conditions);

		// total number of rows affected by the conditions
		$total_grouped_entries = $wpdb->query("SELECT COUNT(s.id) {$sqlForDesiredEntries}");

		// final entries based on founded submission ids which ids fullfill all conditions
		$final_entries = $wpdb->get_results("SELECT e.* 
		FROM {$wpdb->efthakharcf7db_entries} as e 
		WHERE e.submission_id IN ({$submission_ids_string})", ARRAY_A);

		$data = [
			'current_page'   => $page,
			'last_page'      => ceil($total_grouped_entries / $perpage),
			'entries'        => $final_entries,
			'submission_ids' => $submission_ids_fulfilling_conditions,
			'fields'         => $form_fields_details,
		];

		return rest_ensure_response( $data);
	}

	public function get_submissions_permissions_check( $request ) {
		// if ( current_user_can( 'efthakharcf7db_view_submissions' ) ) {
		return true;
		// }

		return new WP_Error( 'rest_forbidden', 'you cannot view forms', [ 'status' => 403 ] );
	}
}
