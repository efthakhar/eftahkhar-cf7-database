<?php

namespace EfthakharCF7DB\Api;

use Throwable;
use WP_Error;

class Submissions {
	public function __construct() {
		add_action( 'rest_api_init', [$this, 'efthakharcf7db_submissions_routes'] );
		add_action('admin_init', [$this, 'efthakharcf7db_submissions_csv_export']);
	}

	public function efthakharcf7db_submissions_routes() {
		register_rest_route( 'efthakharcf7db/v1', '/submissions', [
			'methods'             => 'GET',
			'callback'            => [$this, 'get_submissions'],
			'permission_callback' => [ $this, 'get_submissions_permissions_check' ],
		]);

		register_rest_route('efthakharcf7db/v1', '/getcsv', [
			'methods'             => 'POST',
			'callback'            => [$this, 'get_csv_file'],
			'permission_callback' => [ $this, 'get_csv_file_permissions_check' ],
		]);

		register_rest_route('efthakharcf7db/v1', '/delete-submissions', [
			'methods'             => 'POST',
			'callback'            => [$this, 'delete_submissions'],
			'permission_callback' => [ $this, 'delete_submissions_permissions_check' ],
		]);
	}

	public function delete_submissions($request) {
		global $wpdb;
		// get the form id , fiedls visible in csv and submission ids
		$parameters     = $request->get_json_params();
		$submission_ids = $parameters['submission_ids'] ?? [];
		$submission_ids = implode( ',', array_map( 'intval', $submission_ids ) );

		$delete_submission = $wpdb->query( "DELETE FROM {$wpdb->efthakharcf7db_entries} WHERE submission_id IN ({$submission_ids})" );
		$delete_entries    = $wpdb->query( "DELETE FROM {$wpdb->efthakharcf7db_submissions} WHERE id IN ({$submission_ids})" );

		if($delete_submission&&$delete_entries){
			return rest_ensure_response( [
				'message' => 'submissions deleted',
			]);
		}
		return new WP_Error( );
	}

	public function delete_submissions_permissions_check( $request ) {
		if ( current_user_can( 'efthakharcf7db_delete_submissions' ) ) {
			return true;
		}

		return new WP_Error( 'rest_forbidden', 'you cannot view forms', [ 'status' => 403 ] );
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
		   AND s.form_id = {$form_id}	GROUP BY s.id ";

		// grouped entries based on conditions
		$grouped_entries = $wpdb->get_results("SELECT s.id {$sqlForDesiredEntries} LIMIT {$perpage} OFFSET {$offset}", ARRAY_A);

		// extract only ids as array from founded grouped entries
		$submission_ids_fulfilling_conditions = array_column($grouped_entries, 'id');
		// convert array to string format to use in sql
		$submission_ids_string = implode(',', $submission_ids_fulfilling_conditions);

		// total number of rows affected by the conditions
		$total_grouped_entries = $wpdb->query("SELECT COUNT(s.id) {$sqlForDesiredEntries}");

		// final entries based on founded submission ids which ids fulfill all conditions
		$final_entries = $wpdb->get_results("SELECT e.* FROM {$wpdb->efthakharcf7db_entries} as e WHERE e.submission_id IN ({$submission_ids_string})", ARRAY_A);

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
		if ( current_user_can( 'efthakharcf7db_view_submissions' ) ) {
			return true;
		}

		return new WP_Error( 'rest_forbidden', 'you cannot view forms', [ 'status' => 403 ] );
	}

	public function get_csv_file($request) {
		global $wpdb;
		// get the form id , fiedls visible in csv and submission ids
		$parameters     = $request->get_json_params();
		$visible_fields = $parameters['visible_fields'] ?? [];
		$fields_alias   = $parameters['fields_alias'] ?? [];
		$form_id        = $parameters['form_id'];
		$submission_ids = $parameters['submission_ids'];

		// create a temporary csv file in files folder
		$filename    = uniqid('submission-') . get_current_user_id() . '.csv';
		$file_path   = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/efthakhar-cf7-database/files' . '/' . $filename;
		$file_handle = fopen($file_path, 'a');

		// put the heading column info according to visible fields
		$csv_columns = '';

		foreach ($visible_fields as $vfield) {
			// $csv_columns .= ',' . $vfield;
			$csv_columns .= ',' . $fields_alias[$vfield];
		}
		$csv_columns = ltrim($csv_columns, ',');

		fwrite($file_handle, $csv_columns . PHP_EOL);

		if (empty($submission_ids)) {
			$total_submissions = $wpdb->get_var("SELECT COUNT(s.id) FROM {$wpdb->efthakharcf7db_submissions} as s WHERE s.form_id={$form_id}");

			$total_page = ceil($total_submissions / 100);

			for ($page = 1;$page <= $total_page;++$page) {
				$offset = 100 * ( $page - 1 );

				$submission_ids_chunk        = $wpdb->get_results($wpdb->prepare("SELECT s.id  FROM {$wpdb->efthakharcf7db_submissions} as s WHERE s.form_id={$form_id}  LIMIT %d OFFSET %d", 100, $offset), ARRAY_A);
				$submission_ids_chunk        = array_column($submission_ids_chunk, 'id');
				$submission_ids_chunk_string = implode(',', $submission_ids_chunk);

				$submision_entries_chunk = $wpdb->get_results("SELECT e.* FROM {$wpdb->efthakharcf7db_entries} as e 
				WHERE e.submission_id IN ({$submission_ids_chunk_string})", ARRAY_A);

				$structred_submisions_chunk = [];

				foreach ($submision_entries_chunk  as  $sr) {
					$structred_submisions_chunk[ $sr['submission_id'] ][$sr['field']] = $sr['value'];
				}

				foreach ($structred_submisions_chunk as $ssr) {
					$csv_line = '';

					foreach ($visible_fields as $vfield) {
						$value = $ssr[$vfield] ?? '';
						$csv_line .= ',' . $value;
					}
					$csv_line = ltrim($csv_line, ',');
					fwrite($file_handle, $csv_line . PHP_EOL);
				}
			}
		} else {
			$submission_ids_string = implode(',', $submission_ids);

			$submision_entries = $wpdb->get_results("SELECT e.* FROM {$wpdb->efthakharcf7db_entries} as e 
			WHERE e.submission_id IN ({$submission_ids_string})", ARRAY_A);

			$structred_submisions = [];

			foreach ($submision_entries  as  $sr) {
				$structred_submisions[ $sr['submission_id'] ][$sr['field']] = $sr['value'];
			}

			foreach ($structred_submisions as $ssr) {
				$csv_line = '';

				foreach ($visible_fields as $vfield) {
					$value = $ssr[$vfield] ?? '';
					$csv_line .= ',' . $value;
				}
				$csv_line = ltrim($csv_line, ',');
				fwrite($file_handle, $csv_line . PHP_EOL);
			}
		}

		return rest_ensure_response([
			'csv_download_link' => admin_url() . 'admin.php?page=efthakharcf7db-getcsv&file=' . $file_path,
		]);
	}

	public function get_csv_file_permissions_check( $request ) {
		if ( current_user_can( 'efthakharcf7db_view_submissions' ) ) {
			return true;
		}

		return new WP_Error( 'rest_forbidden', 'you cannot export esv file', [ 'status' => 403 ] );
	}

	/*
	 * Return the desired CSV file to download and after download delete the file
	 */
	public function efthakharcf7db_submissions_csv_export() {
		global $pagenow;

		// Check if the current page is the admin page
		if ('admin.php' === $pagenow
		&& isset($_GET['page']) && 'efthakharcf7db-getcsv' === $_GET['page']
		&& isset($_GET['file']) ) {
			// Set the appropriate headers for file download
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . basename('form-submissions-data.csv') . '"');
			header('Content-Length: ' . filesize($_GET['file']));

			// Read the file and output it to the browser
			readfile($_GET['file']);
			unlink($_GET['file']);

			exit;
		}
	}
}
