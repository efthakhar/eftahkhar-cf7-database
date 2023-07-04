<?php


namespace EfthakharCF7DB;

use EfthakharCF7DB\Helper\I18n;
use EfthakharCF7DB\Traits\Singleton;

class Assets{
    
     use Singleton;

    function __construct()
    {
        add_action( 'admin_enqueue_scripts', [$this,'load_assets'] );
        add_filter( 'script_loader_tag', [$this,'filter_script'], 10, 3 );
    }

    function load_assets($hook)
    {
	
        if( $hook != 'toplevel_page_efthakharcf7db' ) 
        {
            return;
        }
        // wp_deregister_style('wp-admin');
        wp_enqueue_style( 'efthakharcf7db_main_css',  EFTHAKHAR_CF7DB_DIR.'assets/dist/index.css' );
        wp_enqueue_script('efthakharcf7db_main_js',EFTHAKHAR_CF7DB_DIR.'assets/dist/index.js',[],time() ); 
        
        wp_localize_script('efthakharcf7db_main_js','efthakharcf7db',
            [
                'api_url' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'admin_url' => get_admin_url(),
                'tr' => I18n::translations()
            ]
        );
    }

    
    function filter_script( $tag, $handle, $source ) 
    {

        if ( 'efthakharcf7db_main_js' === $handle ) {
            $tag = '<script type="module" crossorigin src="' . $source . '" ></script>';
        }
         
        return $tag;
    }

    
}

    


?>