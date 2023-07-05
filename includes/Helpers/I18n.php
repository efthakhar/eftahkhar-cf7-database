<?php

namespace EfthakharCF7DB\Helpers;

class I18n
{
    static function translations()
    {
        return [
            // common 
            "welcome" => esc_html__("Welcome", "efthakhar-cf7-database"), 
            "cancle" => esc_html__("Cancle", "efthakhar-cf7-database"), 
            "save" => esc_html__("Save", "efthakhar-cf7-database"), 
            "delete" => esc_html__("Delete", "efthakhar-cf7-database"), 
            "action" => esc_html__("Action", "efthakhar-cf7-database"), 
            "submissions" => esc_html__("Submissions", "efthakhar-cf7-database"),
            "id" => esc_html__("ID", "efthakhar-cf7-database"),
             
            // form
            "form" => esc_html__("Form", "efthakhar-cf7-database"), 
            "forms" => esc_html__("Forms", "efthakhar-cf7-database"), 
            "form_list" => esc_html__("Form List", "efthakhar-cf7-database"), 
            "form_id" => esc_html__("Form ID", "efthakhar-cf7-database"), 
            "form_name" => esc_html__("Form Name", "efthakhar-cf7-database"), 

            // Submissions
            "export_csv" => esc_html__("Export CSV", "efthakhar-cf7-database"), 
            "field_settings" => esc_html__("Field Settings", "efthakhar-cf7-database"), 
            "fields" => esc_html__("Fields", "efthakhar-cf7-database"), 
            "field" => esc_html__("Field", "efthakhar-cf7-database"), 
            "alias" => esc_html__("Alias", "efthakhar-cf7-database"), 
            "visible" => esc_html__("Visible", "efthakhar-cf7-database"), 
        ];
    }
}
