<?php

namespace RahulK\DSLB;


defined('ABSPATH') || exit;

/**
 * Doctors Slot Booking Plugin by RahulK
 *
 * The main plugin handler class is responsible for initializing Plugin.
 *
 * @since 1.0.0
 */
class Main {
    /**
     * Instance.
     *
     * Holds the plugin instance.
     *
     * @since 1.0.0
     * @access public
     * @static
     *
     * @var Plugin
     */
    public static $instance = null;

    private $token;

    /**
     * Plugin constructor.+
     *
     * Initializing  plugin.
     *
     * @since 1.0.0
     * @access private
     */
    private function __construct(){
        $this->token = DSLB_TOKEN;

        $this->register_autoloader();

        add_action('init', [$this, 'init'], 0);
        add_action('rest_api_init', [$this, 'on_rest_api_init'], 9);

        // Create file directory on action do
        add_action( $this->token.'_create_plugin_dir', [ $this, 'create_plugin_dir' ] );
        // reg activation hook.
        register_activation_hook(DSLB_FILE, [$this, 'install']);
        // reg deactivation hook.
        register_deactivation_hook(DSLB_FILE, [$this, 'deactivation']);
    }


    /**
     * Installation. Runs on activation.
     *
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function install(){
        
    }

   

    /**
     * Deactivation Hook
     */
    public function deactivation() {
        // deactivation hook.
    }

    
    /**
     * Register autoloader.
     *
     * Elementor autoloader loads all the classes needed to run the plugin.
     *
     * @since 1.6.0
     * @access private
     */
    private function register_autoloader(){
        require_once DSLB_INCLUDES_PATH.'autoloader.php';

        Autoloader::run();
    }


    /**
     * Instance.
     *
     * Ensures only one instance of the plugin class is loaded or can be loaded.
     *
     * @return Plugin An instance of the class.
     * @since 1.0.0
     * @access public
     * @static
     *
     */
    public static function instance(){
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Init.
     *
     * Initialize  Plugin. Register  support for all the
     * supported post types and initialize  components.
     *
     * @since 1.0.0
     * @access public
     */
    public function init(){
        $this->init_components();
    }

    /**
     * Init components.
     *
     * @since 1.0.0
     * @access private
     */
    private function init_components(){
        /**
         * All backend API has to initiallize outside is_admin(), as REST URL is not part of wp_admin
         */
        Schema::init();

        Cpt::instance();
        AjaxHandler::instance();
        Shortcodes::instance();
        Post::instance();
        if (is_admin()) {
            Admin::instance();
            MetaBoxes::instance();
        }
    }


    /**
     * @since 1.0.0
     * @access public
     */
    public function on_rest_api_init(){
    }

    /**
     * Clone.
     *
     * Disable class cloning and throw an error on object clone.
     *
     * The whole idea of the singleton design pattern is that there is a single
     * object. Therefore, we don't want the object to be cloned.
     *
     * @access public
     * @since 1.0.0
     */
    public function __clone(){
        // Cloning instances of the class is forbidden.
        _doing_it_wrong(__FUNCTION__, esc_html__('Something went wrong.', 'doctors-slot-booking'), '1.0.0');
    }

    /**
     * Wakeup.
     *
     * Disable unserializing of the class.
     *
     * @access public
     * @since 1.0.0
     */
    public function __wakeup(){
        // Unserializing instances of the class is forbidden.
        _doing_it_wrong(__FUNCTION__, esc_html__('Something went wrong.', 'doctors-slot-booking'), '1.0.0');
    }
}

Main::instance();