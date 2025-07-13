<?php
namespace RahulK\DSLB;

defined('ABSPATH') || exit;

class Post {
    private static $instance = null;
    private string $assets_url;
    private string $version;
    private string $token;

    /**
     * Post constructor.
     * @since 1.0.0
     */
    public function __construct() {
        $this->assets_url = DSLB_ASSETS_URL;
        $this->version    = DSLB_VERSION;
        $this->token      = DSLB_TOKEN;

        add_action('save_post_dslb_bookings', [$this, 'save_post'], 10, 3);
        add_action('updated_post_meta', [$this, 'send_email_on_booking_status_change'], 10, 4);
    }

    /**
     * Save post callback.
     *
     * @param int $post_id
     * @param WP_Post $post
     * @param bool $update
     * @since 1.0.0
     */
    public function save_post($post_id, $post, $update = false) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (wp_is_post_revision($post_id)) {
            return;
        }

        $is_new_booking = !$update;
        if ($is_new_booking) {
            $recipient = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY') . '_email', true);
            $name = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY') . '_name', true);
            $phone = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY') . '_phone', true);
            $symptoms = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY') . '_symptoms', true);
            $status = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY') . '_status', true);
            $date = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY') . '_appointment_date', true);
            $time = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY') . '_appointment_time', true);
            $doctor = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY') . '_doctor_id', true);
            $doctor_name = get_the_title($doctor);

            $email_content = "<p style='font-weight: bold'>Dear $name,</p>
                <p>Thank you for booking an appointment with us. Your appointment details are as follows</p>
                <table>
                    <tr><th>Doctor</th><td>$doctor_name</td></tr>
                    <tr><th>Date</th><td>$date</td></tr>
                    <tr><th>Time</th><td>$time</td></tr>
                    <tr><th>Symptoms</th><td>$symptoms</td></tr>
                    <tr><th>Status</th><td style='color: " . ($status === 'confirmed' ? 'green' : 'red') . "; font-weight: bold'>$status</td></tr>
                </table>
                <p>If you have any questions or concerns, please don't hesitate to contact us.</p>
                <p>Best regards,</p>
                <p>" . get_bloginfo('name') . "</p>";

            $email_subject = 'Appointment Booking Confirmation';
            $email_content = apply_filters('dslb_booking_confirmed_email_content', $email_content, $recipient, $post_id);
            $email_subject = apply_filters('dslb_booking_confirmed_email_subject', $email_subject, $recipient, $post_id);

            $email = new SendMail(
                $recipient,
                $email_subject,
                $email_content,
                [
                    'Content-Type: text/html; charset=UTF-8',
                    'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>',
                ]
            );
            $email->set_header('<h1 style="color: #333;">Appointment Confirmation</h1>');
            $email->send();

            $admin_email = get_option('admin_email');
            $admin_subject = 'New Appointment Booking';
            $admin_content = "<p>A new appointment has been booked:</p>" . $email_content . 
                "<p>To view the booking, click <a href='" . get_edit_post_link($post_id) . "'>here</a>.</p>";

            $admin_content = apply_filters('dslb_booking_confirmed_admin_email_content', $admin_content, $post_id);
            $admin_subject = apply_filters('dslb_booking_confirmed_admin_email_subject', $admin_subject, $post_id);

            $admin_email = new SendMail(
                $admin_email,
                $admin_subject,
                $admin_content,
                [
                    'Content-Type: text/html; charset=UTF-8',
                    'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>',
                ]
            );
            $admin_email->set_header('<h1 style="color: #333;">New Appointment Booking</h1>');
            $admin_email->send();
        }
    }

    /**
     * Send email on booking status change.
     *
     * @param int $meta_id
     * @param int $post_id
     * @param string $meta_key
     * @param mixed $meta_value
     * @since 1.0.0
     */
    public function send_email_on_booking_status_change($meta_id, $post_id, $meta_key, $meta_value) {
        if ($meta_key !== Schema::getConstant('BOOKING_META_KEY') . '_status') {
            return;
        }

        $recipient = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY') . '_email', true);
        $status = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY') . '_status', true);
        $name = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY') . '_name', true);
        $date = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY') . '_appointment_date', true);
        $time = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY') . '_appointment_time', true);
        $doctor = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY') . '_doctor_id', true);
        $doctor_name = get_the_title($doctor);

        if ($status === 'completed') {
            $subject = 'Your Appointment has been Completed';
            $content = "<p style='font-weight: bold'>Dear $name,</p>
                <p>Your appointment with Dr. $doctor_name on <strong>$date</strong> at <strong>$time</strong> has been completed.</p>
                <p>Thank you for visiting us. If you have any further questions or need assistance, please feel free to contact us.</p>";
        } elseif ($status === 'cancelled') {
            $subject = 'Your Appointment has been Cancelled';
            $content = "<p style='font-weight: bold'>Dear $name,</p>
                <p>We regret to inform you that your appointment with Dr. $doctor_name on <strong>$date</strong> at <strong>$time</strong> has been cancelled.</p>
                <p>If you have any questions or would like to reschedule, please contact us.</p>";
        } else {
            return;
        }

        $content = apply_filters('dslb_booking_status_change_email_content', $content, $recipient, $post_id, $status);
        $subject = apply_filters('dslb_booking_status_change_email_subject', $subject, $recipient, $post_id, $status);

        $email = new SendMail(
            $recipient,
            $subject,
            $content,
            [
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>',
            ]
        );
        $email->set_header('<h1 style="color: #333;">' . $subject . '</h1>');
        $email->send();
    }

    /**
     * Get the instance of the Post class.
     *
     * @return Post
     * @since 1.0.0
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
