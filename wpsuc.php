<?php
/*
Plugin Name: Single User Content
Plugin URI: https://github.com/maurodf0/wpsuc
Author: Mauro De Falco
Author URI: https://github.com/maurodf0
Description: Create a private page for single user registered on your site. Only the designed user can see the post, fast, secure, free.
Version: 1.1
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wpsuc
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 



class wpsuc_SingleUserContent {
    function __construct() {
        add_action('init', array($this, 'wpsuc_add_cpt'));
        add_action('wp_enqueue_scripts', array($this, 'wpsuc_add_style'));
        add_filter('single_template', array($this, 'wpsuc_load_single_template'));
        add_action('add_meta_boxes', array($this, 'wpsuc_add_metaboxes'));
        add_action('save_post', array($this, 'wpsuc_save_metabox_field'));
        
        // Flush permalinks when plugin is activated
        register_activation_hook(__FILE__, array($this, 'wpsuc_flush_rewrite_rules_on_activation'));
    }

    // Add CPT
    function wpsuc_add_cpt() {
        register_post_type('private-user-content',
            array(
                'labels' => array(
                    'name' => __('Single User Content', 'wpsuc'),
                    'singular_name' => __('Single User Content', 'wpsuc'),
                    'add_new' => __('Add New', 'wpsuc'),
                    'add_new_item' => __('Add New Single User Content', 'wpsuc'),
                    'edit_item' => __('Edit Single User Content', 'wpsuc'),
                    'new_item' => __('New Single User Content', 'wpsuc'),
                    'view_item' => __('View Single User Content', 'wpsuc'),
                    'search_items' => __('Search Single User Content', 'wpsuc'),
                    'not_found' => __('No Single User Content found', 'wpsuc'),
                    'not_found_in_trash' => __('No Single User Content found in Trash', 'wpsuc'),
                    'all_items' => __('All Single User Content', 'wpsuc'),
                    'archives' => __('Single User Content Archives', 'wpsuc'),
                    'insert_into_item' => __('Insert into Single User Content', 'wpsuc'),
                    'uploaded_to_this_item' => __('Uploaded to this Single User Content', 'wpsuc'),
                    'filter_items_list' => __('Filter Single User Content list', 'wpsuc'),
                    'items_list_navigation' => __('Single User Content list navigation', 'wpsuc'),
                    'items_list' => __('Single User Content list', 'wpsuc'),
                ),
                'public' => true,
                'has_archive' => true,
                'show_in_rest' => true, 
                'supports' => array('title', 'editor', 'custom-fields', 'thumbnail', 'excerpt', 'comments', 'revisions'), 
                'rewrite' => array('slug' => 'private-user-content'), 
                'menu_position' => 5, 
                'menu_icon' => 'dashicons-lock', 
            )
        );
    }

    // Add Style
    function wpsuc_add_style() {
        //Load CSS only if is a private-user-content CPT
        if (is_singular('private-user-content')) {
        $version = '1.0.1'; // Aggiorna questa versione manualmente per miglior compatibiktò con WP
    wp_enqueue_style('wpsuc-style', plugins_url('style.css', __FILE__), array(), $version);
        }
    }

    // Override CPT single template
    function wpsuc_load_single_template($single) {
        global $post;

        // Verify is post type is private-user-content
        if ($post->post_type == 'private-user-content') {
            // Find template file
            $plugin_template = plugin_dir_path(__FILE__) . 'wpsuc-single-content.php';

            // Se il file esiste, sovrascrivi il template del tema
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }

        return $single;
    }

    // Add Metabox
    function wpsuc_add_metaboxes() {
        add_meta_box(
            'single-user-content-metabox', // id
            __('User for Private User Content', 'wpsuc'), // title
            array($this, 'wpsuc_metabox_content'), // callback
            'private-user-content', // post type
            'normal', // position
            'high' // priority
        );
    }

    // HTML for metabox
    function wpsuc_metabox_content() {
        global $post;
        // Use nonce for verification to secure data sending
        wp_nonce_field(basename(__FILE__), 'loginname_nonce');
        ?>
        <div style="margin-top:25px">
            <label for="user"><?php esc_html_e('Select User', 'wpsuc'); ?></label>
            <select name="user" id="user">
                <?php 
                $all_users = get_users(); 
                foreach($all_users as $user) { ?>
                    <option value="<?php echo esc_attr($user->user_login); ?>" <?php selected($user->user_login, get_post_meta($post->ID, 'username', true)); ?>>
                        <?php echo esc_html($user->user_login); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <?php
    }

    // Salvataggio dei dati della metabox
    function wpsuc_save_metabox_field($post_id) {
        // Verify nonce
        if (!isset($_POST['loginname_nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash( $_POST['loginname_nonce'] ) ), basename(__FILE__))) {
            return $post_id;
        }

        // Check autosave
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return $post_id;
        }

        // Check permissions
        if (isset($_POST['post_type']) && $_POST['post_type'] == 'private-user-content') {
            if (!current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        } elseif (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }

        // Username Autosave
        if (isset($_POST['user'])) {
            $user_login = sanitize_text_field($_POST['user']);
            update_post_meta($post_id, 'username', $user_login);

        }
    }

    // Flush permalink
    function wpsuc_flush_rewrite_rules_on_activation() {
        // Registrazione del Custom Post Type
        $this->wpsuc_add_cpt();
        // Flush permalinks
        flush_rewrite_rules();
    }
}

// Inizializzazione della classe
$wpsuc_singleUserContent = new wpsuc_SingleUserContent();
?>
