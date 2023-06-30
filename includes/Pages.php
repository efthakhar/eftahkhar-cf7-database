<?php

namespace EfthakharCF7DB;

use EfthakharCF7DB\Actions\CF7Actions;
use EfthakharCF7DB\Traits\Singleton;

class Pages {
	use Singleton;

	public function __construct() {
		add_action( 'admin_menu', [$this, 'efthakharcf7db_register_admin_pages'] );
	}

	public function efthakharcf7db_register_admin_pages() {

		add_menu_page(
            __( 'efthakharcf7db', 'efthakharcf7db' ),
            __( 'Efthkhar CF7 DB', 'efthakharcf7db' ), 
            'manage_options', 
            'efthakharcf7db', 
            [$this, 'efthakharcf7db_admin_page_contents'], 
            'dashicons-database-import', 
            2000
        );


		add_submenu_page(
            'efthakharcf7db', 
            __( 'overview', 'efthakharcf7db' ),
            __( 'Overview', 'efthakharcf7db' ), 
            'manage_options', 
            'admin.php?page=efthakharcf7db#/', 
            NULL
        );

		add_submenu_page(
            'efthakharcf7db', 
            __( 'forms', 'efthakharcf7db' ), 
            __( 'Forms', 'efthakharcf7db' ), 
            'manage_options', 
            'admin.php?page=efthakharcf7db#/forms',
            NULL
        );

		remove_submenu_page('efthakharcf7db', 'efthakharcf7db');
        
        
        add_menu_page(
            __( 'efthakharcf7db-getcsv', 'efthakharcf7db' ),
            __( 'efthakharcf7db-getcsv', 'efthakharcf7db' ), 
            'manage_options', 
            'efthakharcf7db-getcsv', 
            NULL, 
            'dashicons-database-import', 
            2000
        );
        remove_menu_page('efthakharcf7db-getcsv');

	}

	public function efthakharcf7db_admin_page_contents() {
		?>
            <div id="efcf7db_app"></div>
        <?php

	}
}

?>