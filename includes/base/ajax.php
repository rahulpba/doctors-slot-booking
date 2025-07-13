<?php
namespace Dudlewebs\DSLB;

defined('ABSPATH') || exit;

use WP_Query;
use DateTime;

/**
 * Ajax Handler for Doctors Slot Booking Plugin.
 *
 * Handles AJAX requests for booking appointments and fetching available slots.
 *
 * @since 1.0.0
 */

class AjaxHandler {
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

        add_action('wp_ajax_get_available_slots', [ $this, 'get_available_slots' ]);
        add_action('wp_ajax_nopriv_get_available_slots', [ $this, 'get_available_slots' ]);
        add_action('wp_ajax_dslb_book_appointment', [ $this, 'book_appointment' ]);
        add_action('wp_ajax_nopriv_dslb_book_appointment', [ $this, 'book_appointment' ]);
        add_action( 'wp_ajax_nopriv_get_available_days', [ $this, 'get_available_days' ]);
        add_action( 'wp_ajax_get_available_days', [ $this, 'get_available_days' ]);
    }


    /**
     * Get available days for a selected doctor.
     *
     * @since 1.0.0
     */
    public function get_available_days() {
        $doctor_id = isset($_POST['doctor']) ? intval(sanitize_text_field($_POST['doctor'])) : 0;
        $result = [
            'success' => false,
            'message' => 'No available days found.',
            'days'    => []
        ];
        if (!$doctor_id) {
            wp_send_json_error($result);
            return;
        }

        $values    = get_post_meta( $doctor_id, Schema::getConstant('DOCTOR_META_KEY'), true );
        $days      = isset($values['available_days']) ? $values['available_days'] : [];
        
        if (empty($days)) {
            wp_send_json_error($result);
            return;
        }
        $result['success'] = true;
        $result['message'] = 'Available days found.';
        $result['days']    = $days;
        wp_send_json($result);
    }

    /**
     * Get available time slots for a selected doctor and date.
     *
     * @since 1.0.0
     */
    public function get_available_slots() {
        $doctor_id = isset($_POST['doctor']) ? intval(sanitize_text_field($_POST['doctor'])) : 0;
        $date = sanitize_text_field($_POST['booking_date']);

        if (!$doctor_id || !$date) {
            wp_send_json_error('Invalid doctor or date.');
            return;
        }

        $settings = get_post_meta($doctor_id, Schema::getConstant('DOCTOR_META_KEY'), true);

        $start    = isset($settings['start_time']) ? strtotime($settings['start_time']) : strtotime('00:00');
        $end      = isset($settings['end_time']) ? strtotime($settings['end_time']) : strtotime('23:59');
        $duration = isset($settings['slot_duration']) ? intval($settings['slot_duration']) : 15;

        // Step 1: Get all booked time slots for the doctor on that date
        $query = new WP_Query([
            'post_type'      => 'dslb_bookings',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [
                ['key' =>  Schema::getConstant('BOOKING_META_KEY') . '_doctor_id', 'value' => $doctor_id],
                ['key' =>  Schema::getConstant('BOOKING_META_KEY') . '_appointment_date', 'value' => $date],
                ['key' =>  Schema::getConstant('BOOKING_META_KEY') . '_status', 'value' => 'confirmed']
            ]
        ]);

        $booked_slots = [];

        if ($query->have_posts()) {
            foreach ($query->posts as $post_id) {
                $time = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY') . '_appointment_time', true);
                if ($time) {
                    $booked_slots[] = $time;
                }
            }
        }

        // Step 2: Generate available slots
        $slots = [];
        while ($start < $end) {
            $time = date('H:i', $start);
            if (!in_array($time, $booked_slots, true)) {
                $slots[] = $time;
            }
            $start = strtotime("+$duration minutes", $start);
        }

        wp_send_json($slots);
    }


    /**
     * Submit a booking.
     *
     * @since 1.0.0
     */
    public function book_appointment() {
        $doctor_id = isset ($_POST['doctor']) ? intval(sanitize_text_field($_POST['doctor'])) : 0;
        $date = isset($_POST['booking_date']) ? sanitize_text_field($_POST['booking_date']) : '';
        $time = isset($_POST['booking_time']) ? sanitize_text_field($_POST['booking_time']) : '';
        $name = isset($_POST['patient_name']) ? sanitize_text_field($_POST['patient_name']) : '';
        $email = isset($_POST['patient_email']) ? sanitize_email($_POST['patient_email']) : '';
        $phone = isset($_POST['patient_phone']) ? sanitize_text_field($_POST['patient_phone']) : '';
        $symptoms = isset($_POST['symptoms']) ? sanitize_textarea_field($_POST['symptoms']) : '';
        if (!$doctor_id || !$date || !$time || !$name || !$email) {
            wp_send_json_error('Please fill all required fields.');
            return;
        }
        // Validate doctor exists
        $doctor = get_post($doctor_id);
        if (!$doctor || $doctor->post_type !== 'dslb_doctor') {
            wp_send_json_error('Invalid doctor selected.');
            return;
        }
        // Validate date format and check if it's not a previous date than today
        $date_format = 'Y-m-d';
        $date_obj = DateTime::createFromFormat($date_format, $date);
        $today = new DateTime();
        if (!$date_obj || $date_obj->format($date_format) !== $date || $date_obj < $today) {
            wp_send_json_error('Invalid date format or the date is in the past. Use YYYY-MM-DD.');
            return;
        }
        // Validate time format
        $time_format = 'H:i';
        $time_obj = DateTime::createFromFormat($time_format, $time);
        if (!$time_obj || $time_obj->format($time_format) !== $time) {
            wp_send_json_error('Invalid time format.');
            return;
        }
        // Validate email
        if (!is_email($email)) {
            wp_send_json_error('Invalid email address.');
            return;
        }
        // Validate phone number (optional, but can be added)
        if ($phone && !preg_match('/^\+?[0-9\s\-()]+$/', $phone)) {
            wp_send_json_error('Invalid phone number format.');
            return;
        }
        // Check if the slot is already booked
        $existing = new WP_Query([
            'post_type' => 'dslb_bookings',
            'posts_per_page' => 1,
            'meta_query'     => [
                ['key' =>  Schema::getConstant('BOOKING_META_KEY') . '_doctor_id', 'value' => $doctor_id],
                ['key' =>  Schema::getConstant('BOOKING_META_KEY') . '_appointment_date', 'value' => $date],
                ['key' =>  Schema::getConstant('BOOKING_META_KEY') . '_status', 'value' => 'confirmed'],
                ['key' =>  Schema::getConstant('BOOKING_META_KEY') . '_appointment_time', 'value' => $time]
            ]
        ]);

        if ($existing->have_posts()) {
            wp_send_json_error('This slot is already booked.');
        }

        // Create the booking post
        $post_data = [
            'post_type'   => 'dslb_bookings',
            'post_title'  => "#$name | " . date('d/m/Y h:i A', strtotime("$date $time")) . " | " . get_the_title($doctor_id),
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
            'meta_input' => [
                Schema::getConstant('BOOKING_META_KEY') . '_doctor_id' => $doctor_id,
                Schema::getConstant('BOOKING_META_KEY') . '_appointment_date' => $date,
                Schema::getConstant('BOOKING_META_KEY') . '_appointment_time' => $time,
                Schema::getConstant('BOOKING_META_KEY') . '_name' => $name,
                Schema::getConstant('BOOKING_META_KEY') . '_email' => $email,
                Schema::getConstant('BOOKING_META_KEY') . '_phone' => $phone,
                Schema::getConstant('BOOKING_META_KEY') . '_symptoms' => $symptoms,
                Schema::getConstant('BOOKING_META_KEY') . '_status' => 'confirmed',
            ],
        ];
        $post_id = wp_insert_post($post_data);

        wp_send_json_success('Appointment booked successfully!');
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