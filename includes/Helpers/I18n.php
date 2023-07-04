<?php

namespace EfthakharCF7DB\Helper;

class I18n
{
    static function translations()
    {
        return [
    
            "welcome" => esc_html__("Welcome", "efthakhar-cf7-database"), 
            "export-csv" => esc_html__("Export CSV", "efthakhar-cf7-database"), 
            "field-settings" => esc_html__("Field Settings", "efthakhar-cf7-database"), 

        ];
    }
}
