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
	}

    /*
     * Fetch forms from database
     */

	public function get_forms( $request ) {

		global $wpdb;

        $page    = $request->get_param('page') ?? 1;
		$perpage = $request->get_param('perpage') ?? 10;
        $offset = $perpage * ( $page - 1 );

		$forms = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->efthakharcf7db_forms} LIMIT %d OFFSET %d",
                $perpage, $offset
            )
        , ARRAY_A);

        $total_results = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->efthakharcf7db_forms}" );

		return rest_ensure_response( [
            'forms' => $forms,
            'total'=> $total_results,
            'current_page' => $page,
            'last_page'=> ceil($total_results/$perpage)
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
}
