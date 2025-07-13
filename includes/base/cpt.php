<?php
namespace Dudlewebs\DSLB;

defined('ABSPATH') || exit;

class Cpt {
    private static $instance = null;
    private string $assets_url;
    private string $version;
    private string $token;


    /**
     * Admin constructor.
     * @since 1.0.0
     */
    public function __construct() {
        $this->assets_url = DSLB_ASSETS_URL;
        $this->version    = DSLB_VERSION;
        $this->token      = DSLB_TOKEN;

        add_action( 'init', [$this, 'init'] );
    }

    /**
     * Initialize the custom post type.
     *
     * Sets up the necessary WordPress hooks and actions for the custom post type.
     *
     * @since 1.0.0
     */

    public function init() {
        $labels = array(
            'name'                  => _x( 'Appointments', 'Post type general name', 'doctors-slot-booking' ),
            'singular_name'         => _x( 'Appointment', 'Post type singular name', 'doctors-slot-booking' ),
            'menu_name'             => _x( 'Hospital Appointments', 'Admin Menu text', 'doctors-slot-booking' ),
            'name_admin_bar'        => _x( 'Appointment', 'Add New on Toolbar', 'doctors-slot-booking' ),
            'add_new'               => __( 'Add New', 'doctors-slot-booking' ),
            'add_new_item'          => __( 'Add New Appointment', 'doctors-slot-booking' ),
            'new_item'              => __( 'New Appointment', 'doctors-slot-booking' ),
            'edit_item'             => __( 'Edit Appointment', 'doctors-slot-booking' ),
            'view_item'             => __( 'View Appointment', 'doctors-slot-booking' ),
            'all_items'             => __( 'All Appointments', 'doctors-slot-booking' ),
            'search_items'          => __( 'Search Appointments', 'doctors-slot-booking' ),
            'parent_item_colon'     => __( 'Parent Appointments:', 'doctors-slot-booking' ),
            'not_found'             => __( 'No Appointments found.', 'doctors-slot-booking' ),
            'not_found_in_trash'    => __( 'No Appointments found in Trash.', 'doctors-slot-booking' ),
            'featured_image'        => _x( 'Appointment Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'doctors-slot-booking' ),
            'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'doctors-slot-booking' ),
            'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'doctors-slot-booking' ),
            'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'doctors-slot-booking' ),
            'archives'              => _x( 'Appointment archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'doctors-slot-booking' ),
            'insert_into_item'      => _x( 'Insert into Appointment', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'doctors-slot-booking' ),
            'uploaded_to_this_item' => _x( 'Uploaded to this Appointment', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'doctors-slot-booking' ),
            'filter_items_list'     => _x( 'Filter Appointments list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'doctors-slot-booking' ),
            'items_list_navigation' => _x( 'Appointments list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'doctors-slot-booking' ),
            'items_list'            => _x( 'Appointments list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'doctors-slot-booking' ),
        );
    
        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'bookings' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-archive',
            'supports'           => array( 'title' ),
        );

        register_post_type( $this->token . '_bookings', $args );


        $labels = array(
            'name'                  => _x( 'Doctors', 'Post type general name', 'doctors-slot-booking' ),
            'singular_name'         => _x( 'Doctor', 'Post type singular name', 'doctors-slot-booking' ),
            'menu_name'             => _x( 'Doctors', 'Admin Menu text', 'doctors-slot-booking' ),
            'name_admin_bar'        => _x( 'Doctor', 'Add New on Toolbar', 'doctors-slot-booking' ),
            'add_new'               => __( 'Add New', 'doctors-slot-booking' ),
            'add_new_item'          => __( 'Add New Doctor', 'doctors-slot-booking' ),
            'new_item'              => __( 'New Doctor', 'doctors-slot-booking' ),
            'edit_item'             => __( 'Edit Doctor', 'doctors-slot-booking' ),
            'view_item'             => __( 'View Doctor', 'doctors-slot-booking' ),
            'all_items'             => __( 'All Doctors', 'doctors-slot-booking' ),
            'search_items'          => __( 'Search Doctors', 'doctors-slot-booking' ),
            'parent_item_colon'     => __( 'Parent Doctors:', 'doctors-slot-booking' ),
            'not_found'             => __( 'No Doctors found.', 'doctors-slot-booking' ),
            'not_found_in_trash'    => __( 'No Doctors found in Trash.', 'doctors-slot-booking' ),
            'featured_image'        => _x( 'Doctor Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'doctors-slot-booking' ),
            'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'doctors-slot-booking' ),
            'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'doctors-slot-booking' ),
            'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'doctors-slot-booking' ),
            'archives'              => _x( 'Doctor archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'doctors-slot-booking' ),
            'insert_into_item'      => _x( 'Insert into Doctor', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'doctors-slot-booking' ),
            'uploaded_to_this_item' => _x( 'Uploaded to this Doctor', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'doctors-slot-booking' ),
            'filter_items_list'     => _x( 'Filter Doctors list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'doctors-slot-booking' ),
            'items_list_navigation' => _x( 'Doctors list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'doctors-slot-booking' ),
            'items_list'            => _x( 'Doctors list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'doctors-slot-booking' ),
        );
    
        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'doctor' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-groups',
            'supports'           => array( 'title'),
        );

        register_post_type( $this->token . '_doctor', $args );
    }

    /**
     * Ensures only one instance of Class is loaded or can be loaded.
     *
     * @return Db Class instance
     * @since 1.0.0
     * @static
     */
    public static function instance(){
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}