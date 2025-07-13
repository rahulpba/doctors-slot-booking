<?php
namespace Dudlewebs\DSLB;

defined('ABSPATH') || exit;

class Shortcodes {
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

        add_action( 'init', [ $this, 'init_shortcodes' ]);
    }



    /**
     * Initialize shortcodes.
     *
     * Sets up and registers all shortcodes used by the plugin.
     *
     * @since 1.0.0
     */

    public function init_shortcodes(){
        add_shortcode('hospital_appointment_form', [$this, 'show_booking_form']);
    }


    public function show_booking_form($atts){
        wp_enqueue_style($this->token.'_shortcodes', $this->assets_url.'css/shortcodes.css', [], $this->version);
        wp_enqueue_style($this->token.'_flatpickr', $this->assets_url.'css/flatpickr.min.css', [], $this->version);
        wp_enqueue_script($this->token.'_flatpickr', $this->assets_url.'js/flatpickr.js', ['jquery'], $this->version);
        wp_enqueue_script($this->token.'_shortcodes', $this->assets_url.'js/shortcodes.js', ['jquery'], $this->version);
        wp_localize_script($this->token.'_shortcodes', $this->token.'_ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    
        ob_start();
        include(DSLB_PATH.'templates/shortcodes/booking-form.php');
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
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