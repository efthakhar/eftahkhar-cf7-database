<?php

namespace EfthakharCF7DB\Api;

use WP_Error;

class Forms {
	public function __construct() {
		add_action( 'rest_api_init', [$this, 'efthakharcf7db_forms_routes'] );
	}

	public function efthakharcf7db_forms_routes() {
		register_rest_route( 'efthakharcf7db/v1', '/forms/', [
			'methods'             => 'GET',
			'callback'            => [$this, 'get_forms'],
			'permission_callback' => [ $this, 'get_forms_permissions_check' ],
		]);

		register_rest_route( 'efthakharcf7db/v1', '/form-fields/', [
			'methods'             => 'PUT',
			'callback'            => [$this, 'edit_form_fields'],
			'permission_callback' => [ $this, 'edit_form_fields_permissions_check' ],
		]);
	}

	/*
	 * Fetch forms from database
	 */

	public function get_forms( $request ) {
		global $wpdb;

		$page    = $request->get_param('page') ?? 1;
		$perpage = $request->get_param('perpage') ?? 10;
		$offset  = $perpage * ( $page - 1 );

		$forms = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->efthakharcf7db_forms} LIMIT %d OFFSET %d", $perpage, $offset), ARRAY_A);

		$total_results = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->efthakharcf7db_forms}" );

		return rest_ensure_response( [
			'forms'        => $forms,
			'total'        => $total_results,
			'current_page' => $page,
			'last_page'    => ceil($total_results / $perpage),
		] );
	}

	/*
	 * Determine if a user has permission to see forms
	 */
	public function get_forms_permissions_check( $request ) {
		if ( current_user_can( 'efthakharcf7db_view_forms' ) ) {
			return true;
		}

		return new WP_Error( 'rest_forbidden', 'you have no permission to view forms', [ 'status' => 403 ] );
	}

	/*
	 * Edit form fiedls
	 */
	public function edit_form_fields( $request ) {
		global $wpdb;
		date_default_timezone_set('UTC');

		$currentDateTime         = new \DateTime();
		$currentFormatedDateTime = $currentDateTime->format('Y-m-d H:i:s');
		$parameters = $request->get_json_params();
		$fields     = $parameters['fields'] ?? [];
		$form_id    = $parameters['form_id'];

		$form                     = $wpdb->get_row("SELECT * FROM  {$wpdb->efthakharcf7db_forms} WHERE `cf7_id` = {$form_id} ", ARRAY_A);
		$exsisted_cf7_form_fields = json_decode($form['fields'], true);

		$modified_fields = [];

		foreach ($exsisted_cf7_form_fields as $ex_field) {
			$ex_field['alias']   = $fields[$ex_field['name']]['alias'] ?? $ex_field['alias'];
			$ex_field['visible'] = $fields[$ex_field['name']]['visible'] ?? $ex_field['visible'];

			$modified_fields[ $ex_field['name'] ] = $ex_field;
		}

		$wpdb->update($wpdb->efthakharcf7db_forms, [
			'cf7_id'     => $form_id,
			'fields'     => json_encode($modified_fields),
			'updated_by' => get_current_user_id(),
			'updated_at' => $currentFormatedDateTime,
		], ['cf7_id' => $form_id]);

		return rest_ensure_response( [
			'fields' => 'fields updated',
		] );
	}

	/*
	 * Deterimine if a user has permission to edit form fiedls
	 *
	 */
	public function edit_form_fields_permissions_check($request) {
		// if ( current_user_can( 'efthakharcf7db_edit_forms' ) ) {
		return true;
		// }

		return new WP_Error( 'rest_forbidden', 'you have no permission to edit form fields', [ 'status' => 403 ] );
	}
}
