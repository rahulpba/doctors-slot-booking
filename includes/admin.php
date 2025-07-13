<?php
namespace Dudlewebs\DSLB;

defined('ABSPATH') || exit;

class Admin {
    private static $instance = null;
    private $assets_url;
    private $version;
    private $token;
    private $script_suffix;
    private $dir;

    protected $hook_suffix = [];
    /**
     * Admin constructor.
     * @since 1.0.0
     */
    public function __construct() {
        $this->assets_url       = DSLB_ASSETS_URL;
        $this->version          = DSLB_VERSION;
        $this->token            = DSLB_TOKEN;
        $this->dir              = DSLB_PATH;
        $this->script_suffix    = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        $plugin = plugin_basename(DSLB_FILE);

        // add action links to link to link list display on the plugins page.
        add_filter("plugin_action_links_$plugin", [$this, 'plugin_action_links']);

        // add our custom CSS classes to <body>
		add_filter( 'admin_body_class', [ $this, 'admin_body_class' ] );
        // Admin Init
        add_action('admin_init', [$this, 'adminInit']);

        add_action('admin_menu', [$this, 'add_menu'], 10);

        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'], 10, 1);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_styles'], 10, 1);
    }


    /**
     * Method that is used on plugin initialization time
     * @since 1.0.0
     */
    public function adminInit() {
    }

    
    /**
     * Load admin Javascript.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function admin_enqueue_scripts($hook = '') {
        
    }



    /**
     * Load admin CSS.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function admin_enqueue_styles($hook = '') {
        wp_register_style($this->token.'-backend',
            esc_url($this->assets_url).'css/backend.css?nocache='.rand(0, 10000), array(), $this->version);
        wp_enqueue_style($this->token.'-backend');
    }

    /**
     * Show action links on the plugin screen.
     *
     * @param mixed $links Plugin Action links.
     *
     * @return array
     */
    public function plugin_action_links($links){
        $action_links = array(
            'settings' => '<a href="' . admin_url('admin.php?page=' . $this->token . '-admin-ui/') . '">' . esc_html__('Settings', 'doctors-slot-booking') . '</a>',
        );

        return array_merge($action_links, $links);
    }




    /**
     * Add Admin Menu
     */
    public function add_menu() {
        // Add main menu
        $this->hook_suffix[] = add_submenu_page(
            'edit.php?post_type='.$this->token.'_bookings',
            esc_html__('Doctors', 'doctors-slot-booking'),
            esc_html__('Doctors', 'doctors-slot-booking'),
            'manage_options',
            'edit.php?post_type='.$this->token.'_doctor',
            false
        );
    }

    public function settings_UI() {
        include $this->dir.'/templates/settings.php';
    }



    /**
	 * Add custom classes to the HTML body tag
	 *
	 * @param string $classes
	 *
	 * @return string
	 */
	public function admin_body_class( $classes ) {
		if ( ! $classes ) {
			$classes = array();
		} else {
			$classes = explode( ' ', $classes );
		}
		$classes[] = $this->token.'_page';
		/**
         *  Recommended way to target WP 3.8+
         *  http://make.wordpress.org/ui/2013/11/19/targeting-the-new-dashboard-design-in-a-post-mp6-world/
         * 
         */
		if ( version_compare( $GLOBALS['wp_version'], '3.8-alpha', '>' ) ) {
			if ( ! in_array( 'mp6', $classes ) ) {
				$classes[] = 'mp6';
			}
		}
		return implode( ' ', $classes );
	}
    

    /**
     * Ensures only one instance of Class is loaded or can be loaded.
     *
     * @return Main Class instance
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