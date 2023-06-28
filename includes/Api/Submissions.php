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

       	// 	// Inserting 5 wp_efthakharcf7db_submissions into the "submissions" table
		// for ($i=0; $i < 1000; $i++) { 
		// 	$wpdb->query("INSERT INTO wp_efthakharcf7db_submissions (form_id) VALUES (12)");
		// }



		// // Retrieve the last inserted submission IDs
		// $last_submission_id = $wpdb->insert_id;

		// for ($i=1; $i <= 1000; $i++) { 
		// 	$wpdb->insert( $wpdb->efthakharcf7db_entries, [
		// 		'submission_id' => $i,
		// 		'form_id'       => 12,
		// 		'field'         => 'your-name',
		// 		'value'         => 'name'.$i,
		// 	] );
		// 	$wpdb->insert( $wpdb->efthakharcf7db_entries, [
		// 		'submission_id' => $i,
		// 		'form_id'       => 12,
		// 		'field'         => 'your-email',
		// 		'value'         => 'email@gm.com'.$i,
		// 	] );
		
			
		// }
		
		// return;


          
		$form_id = $request->get_param('form_id');
		$page    = $request->get_param('page') ?? 1;
		$perpage = $request->get_param('perpage') ?? 10;
		$offset  = $perpage * ( $page - 1 );
		global $wpdb;

		$form_details = $wpdb->get_row("SELECT * FROM {$wpdb->efthakharcf7db_forms} WHERE `cf7_id` = {$form_id} ", ARRAY_A);

		$visible_fields = json_decode($form_details['fields'], true);
		$sqlColoms      = '';
		$conditions     = '';

		foreach ($visible_fields as $vf) {
			if (true == $vf['visible']) {
				$sqlColoms .= "MAX(CASE WHEN e.field = '" . $vf['name'] . "' THEN e.value END) AS '" . $vf['name'] . "',";
			}
		}

		$sqlColoms = rtrim($sqlColoms, ',');

		$matched_results = $wpdb->get_results("SELECT sr.submission_id FROM
			(
				SELECT
				e.submission_id, {$sqlColoms}
				FROM (
					SELECT *
					FROM {$wpdb->efthakharcf7db_entries}
					WHERE form_id = {$form_id}
					-- AND
					-- (
					-- 	(field = 'your-name' AND value LIKE '%55%')
					-- 	OR
					-- 	(field = 'your-email' AND value LIKE '%55%')
					-- )
				) AS e

		   		GROUP BY e.submission_id
		   		LIMIT {$perpage} OFFSET {$offset}
		   ) as sr");

		$total_rows = $wpdb->get_var("SELECT COUNT(pt.id) FROM (SELECT e.id, {$sqlColoms}
		FROM (
			SELECT *
			FROM {$wpdb->efthakharcf7db_entries}
			WHERE form_id = {$form_id}
			-- AND
			-- (
			-- 	(field = 'your-name' AND value LIKE '%55%')
			-- 	OR
			-- 	(field = 'your-email' AND value LIKE '%55%')
			-- )
		) AS e

	    GROUP BY e.submission_id) as pt" );

		$matchd_ids = [];

		foreach ($matched_results as $row) {
			$matchd_ids[] = $row->submission_id;
		}

		$matchd_ids = implode( ',', $matchd_ids );

		$final_entries = $wpdb->get_results("SELECT * FROM {$wpdb->efthakharcf7db_entries} WHERE submission_id IN ({$matchd_ids}) ");

		$data = [
			'current_page' => $page,
			'last_page'    => ceil($total_rows / $perpage),
			'entries'      => $final_entries,
			// 'submission_ids'              => $submission_ids,
			// 'fields_visible_in_datatable' => $fields_visible_in_datatable,
			// 'fields_alias'                => $fields_alias,
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
