<?php
/*
Plugin Name: Amenities Plugin
Plugin URI: http://www.drawandcode.com/plugins/amenities-plugin
Description: Add a list of available amenities that your Pub/Hotel/Hostel/B&B caters for.
Version: 0.0.5
Author: Paul Canning
Author URI: http://www.drawandcode.com
License: GPLv2 or later
*/

// Plugin hooks and actions
register_activation_hook(__FILE__, 'amenities_install');
//register_deactivation_hook(__FILE__, 'amenities_remove');
//add_action( 'wpmu_new_blog', 'new_blog', 10, 6 ); 		
add_action( 'widgets_init', 'theWidgetInit' );
add_shortcode( 'amenities', 'amenityShortcode');
if ( is_admin() ){
    add_action( 'admin_menu', 'amenities_admin_menu' );
}

// Install for blog(s)
function amenities_install() {
    global $wpdb;

    if ( function_exists( 'is_multisite' ) && is_multisite() ) {
        // check if it is a network activation - if so, run the activation function for each blog id
        if ( isset( $_GET['networkwide'] ) && ( $_GET['networkwide'] == 1 ) ) {
            $old_blog = $wpdb->blogid;
            // Get all blog ids
            $blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
            foreach ($blogids as $blog_id) {
                switch_to_blog($blog_id);
                _amenities_install();
            }
            switch_to_blog($old_blog);
            return;
        }	
    } 
    _amenities_install();	
}
// Create database entries
function _amenities_install() {
    global $wpdb;
    
    $structure = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."amenities (
        `id` INT NOT NULL AUTO_INCREMENT,
        `title` VARCHAR(255) NOT NULL,
        `sort` INT NULL DEFAULT 0,
        PRIMARY KEY (`id`)
        )";
    $wpdb->query($structure);
}
// Install for new blogs
 
function new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta) {
    global $wpdb;

    if (is_plugin_active_for_network('amenities/amenities_function.php')) {
        $old_blog = $wpdb->blogid;
        switch_to_blog($blog_id);
        _amenities_install();
        switch_to_blog($old_blog);
    }
}

// Clean up on de-activate
function amenities_remove() {
    global $wpdb;

    if (function_exists('is_multisite') && is_multisite()) {
        // check if it is a network activation - if so, run the activation function for each blog id
        if (isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
            $old_blog = $wpdb->blogid;
            // Get all blog ids
            $blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
            foreach ($blogids as $blog_id) {
                switch_to_blog($blog_id);
                _amenities_remove();
            }
            switch_to_blog($old_blog);
            return;
        }	
    } 
    _amenities_remove();		
}
// Remove tables
function _amenities_remove() {
    global $wpdb;
    
    $table = $wpdb->prefix . 'amenities';
    $sql = "DROP TABLE IF EXISTS " . $table;
    $wpdb->query($sql);
}

function amenities_admin_menu() {
    add_menu_page('Amenities', 'Amenities', 'administrator', 'amenities', 'amenities_html_page');
    add_submenu_page('amenities', 'Amenities - Edit', 'Edit Amenity', 'administrator', 'edit_amenity', 'edit_amenity_html');
}

// Show list of amenites and CRUD options
function amenities_html_page() {
    if(isset($_POST['action']) && ($_POST['action'] == 'add') ) {
        $errors = array();
        
        global $wpdb;
        
        if($_POST['title'] != '') {
            $title =  $wpdb->escape( $_POST['title'] );
        } else {
            $errors['title'] = 'You must provide a title.'; 
        }
        
        if(isset($_POST['sort']) && is_int($_POST['sort'])) {
            $sort = $wpdb->escape( $_POST['sort'] );
        } else {
            $sort = '0';
        }
        
        if(count($errors) == 0) {
            $sql = 'INSERT INTO '.$wpdb->prefix.'amenities (title, sort) VALUES ("'.$title.'", "'.$sort.'")';
            $wpdb->query($sql);

            echo '<div class="updated"><p><strong>Amenity successfully added.</strong></p></div>';
        }
    }
    
    if(isset($_POST['action']) && ($_POST['action'] == 'delete') ) {
        global $wpdb;
        
        $id = $_POST['delete-id'];
        
        $sql = 'DELETE FROM '.$wpdb->prefix.'amenities WHERE id = "'.$id.'"';
        $wpdb->query($sql);
        
        echo '<div class="updated"><p><strong>Amenity successfully deleted.</strong></p></div>';
    }
?>
<div class="wrap">
    <h2>Amenities</h2>
    
    <table class="wp-list-table widefat page fixed">
        <thead>
            <tr>
                <th class="manage-column">Title</th>
                <th class="manage-column">Sort</th>
                <th class="manage-column">Edit</th>
                <th class="manage-column">Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb;
            $sql = 'SELECT * FROM '.$wpdb->prefix.'amenities';
            $result = $wpdb->get_results($sql);

            foreach($result as $row) {
            ?>
                <tr>
                    <td><?php echo $row->title; ?></td>
                    <td><?php echo $row->sort; ?></td>
                    <td>
                        <a class="button-primary" href="admin.php?page=edit_amenity&id=<?php echo $row->id; ?>">Edit</a>
                    </td>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="action" value="delete" />
                            <input name="delete-id" type="hidden" value="<?php echo $row->id; ?>" />
                            <input class="button-primary" name="delete" type="submit" value="Delete"/>
                        </form>
                    </td>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
    
    <h3>Add New Amenity</h3>
    <form method="post" action="">
        <table>
            <tr>
                <th width="150">Amenity:</th>
                <td>
                    <input name="title" type="text" id="title" />
                    <?php if(isset($errors['title'])) { echo $errors['title']; } ?>
                </td>
            </tr>
            <tr>
                <th width="150">Sort Order (0 - highest):</th>
                <td>
                    <input name="sort" type="text" id="sort" />
                </td>
            </tr>
        </table>
        <input type="hidden" name="action" value="add" />
        <input type="hidden" name="page_options" value="amenities_data" />
        <p>
            <input class="button-primary" type="submit" value="Add amenity" />
        </p>
    </form>
</div>
<?php
}

function edit_amenity_html() {
    if(isset($_GET['id'])) {
        global $wpdb;
        
        $id = $_GET['id'];
        $sql = 'SELECT * FROM '.$wpdb->prefix.'amenities WHERE id = "'.$id.'"';
        $row = $wpdb->get_row($sql);
        
        if(isset($_POST['action']) && $_POST['action'] == 'edit') {
            if(isset($_POST['title'])) {
                $title = $wpdb->escape($_POST['title']);
            }
            if(isset($_POST['sort'])) {
                $sort = $wpdb->escape($_POST['sort']);
            }
            
            $sql = 'UPDATE '.$wpdb->prefix.'amenities SET title = "'.$title.'", sort = "'.$sort.'" WHERE id = "'.$id.'"';
            $wpdb->query($sql);
            
            echo '<div class="updated"><p><strong>Amenity successfully updated.</strong></p></div>';
        }
        
?>
    <h3>Edit Amenity</h3>
    <form method="post" action="">
        <table>
            <tr>
                <th width="150">Amenity:</th>
                <td>
                    <input name="title" type="text" id="title" value="<?php echo $row->title; ?>" />
                    <?php if(isset($errors['title'])) { echo $errors['title']; } ?>
                </td>
            </tr>
            <tr>
                <th width="150">Sort Order (0 - highest):</th>
                <td>
                    <input name="sort" type="text" id="sort" value="<?php echo $row->sort; ?>" />
                </td>
            </tr>
        </table>
        <input type="hidden" name="action" value="edit" />
        <input type="hidden" name="page_options" value="amenities_data" />
        <p>
            <input class="button-primary" type="submit" value="Update" />
        </p>
    </form>
<?php
    }
}

function theWidgetInit() {
    require_once dirname(__FILE__) . '/widget.class.php';
    register_widget( 'theWidget' );
}

function amenityShortcode() {
    require_once dirname(__FILE__) . '/shortcode.output.php';
}
?>