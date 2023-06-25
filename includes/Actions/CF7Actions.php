<?php

namespace EfthakharCF7DB\Actions;

use DateTime;
use EfthakharCF7DB\Traits\Singleton;

class CF7Actions {
	use Singleton;
	public function __construct() {
		// Save form info after saving or editing cf7 form
		add_action('wpcf7_after_save', [$this, 'handle_cf7_save']);
		add_action( 'efthakharcf7db_sync_exsisting_cf7forms_event', [$this, 'sync_exsisting_cf7forms'] );
	}

	public function sync_exsisting_cf7forms() {
		$contact_forms = \WPCF7_ContactForm::find();

		foreach ($contact_forms as $cf7_form) {
			$cf7_form->save();
		}
	}

	public function handle_cf7_save($cf7) {
		global $wpdb;
		date_default_timezone_set('UTC');

		$currentDateTime         = new DateTime();
		$currentFormatedDateTime = $currentDateTime->format('Y-m-d H:i:s');

		$form = $wpdb->get_row("SELECT * FROM  {$wpdb->efthakharcf7db_forms} WHERE `cf7_id` = {$cf7->id} ", ARRAY_A);

		if ($form) { // in case of update
			$exsisted_cf7_form_fields = json_decode($form['fields'], true);
			$updated_cf7_form_fields  = $cf7->scan_form_tags();
			$fields                   = [];

			foreach ($updated_cf7_form_fields as $field) {
				if ('submit' !== $field->type) {
					$fields[$field->name] = [
						'name'     => $field->name,
						'alias'    => $exsisted_cf7_form_fields[$field->name]['alias'] ?? $field->name,
						'type'     => $field->type,
						'visible'  => $exsisted_cf7_form_fields[$field->name]['visible'] ?? true,
						'required' => $exsisted_cf7_form_fields[$field->name]['required'] ?? false,
					];
				}
			}

			$all_possible_unique_fields = array_unique(array_merge($exsisted_cf7_form_fields, $fields), SORT_REGULAR);

			$wpdb->update($wpdb->efthakharcf7db_forms, [
				'cf7_id'     => $cf7->id,
				'name'       => $cf7->title(),
				'fields'     => json_encode($all_possible_unique_fields),
				'updated_by' => get_current_user_id(),
				'updated_at' => $currentFormatedDateTime,
			], ['id' => $form['id']]);
		} else { // in case of creating new form
			$created_cf7_form_fields = $cf7->scan_form_tags();
			$fields                  = [];

			foreach ($created_cf7_form_fields as $field) {
				if ('submit' !== $field->type) {
					$fields[$field->name] = [
						'name'     => $field->name,
						'alias'    => $field->name,
						'type'     => $field->type,
						'visible'  => true,
						'required' => $field->is_required(),
					];
				}
			}
			$wpdb->insert($wpdb->efthakharcf7db_forms, [
				'cf7_id'     => $cf7->id,
				'name'       => $cf7->title(),
				'fields'     => json_encode($fields),
				'created_by' => get_current_user_id()==0?NULL:get_current_user_id(),
				'created_at' => $currentFormatedDateTime,
			]);
		}
	}
}
