<?php
namespace RahulK\DSLB;

defined('ABSPATH') || exit;

class MetaBoxes {
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

        add_action( 'add_meta_boxes', [$this, 'meta_boxes' ] );
        add_action( 'save_post', [$this, 'booking_save_meta_boxes'] );
        add_action( 'save_post', [$this, 'doctor_save_meta_boxes'] );
        add_filter( 'manage_'.$this->token.'_bookings_posts_columns', [ $this, 'bookings_columns' ]);
        add_action( 'manage_posts_custom_column', [ $this, 'bookings_columns_content' ], 10, 2 );
        add_action( 'restrict_manage_posts', [$this, 'add_bookings_filter_dropdown'] );
        add_action( 'pre_get_posts', [$this, 'filter_bookings'] );
        add_filter( 'posts_search', [$this, 'filter_bookings_search'], 10, 2);
    }

    /**
     * Add Meta Boxes
     */
    public function meta_boxes() {
        add_meta_box(
            'dslb_meta_box_group',
            __( 'Doctor Settings', 'doctors-slot-booking' ),
            [$this, 'meta_box_doctor'],
            $this->token . '_doctor',
            'normal',
            'high'
        );
        add_meta_box(
            'dslb_meta_box_group',
            __( 'Booking Settings', 'doctors-slot-booking' ),
            [$this, 'meta_box_booking'],
            $this->token . '_bookings',
            'normal',
            'high'
        );
    }


    /**
     * Metabox for Doctor CPT
     */
    public function meta_box_doctor( $post ) {
        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'dslb_doctor_meta_data_update', '__dslb_doctor_meta_nonce' );

        $values     = get_post_meta( $post->ID, Schema::getConstant('DOCTOR_META_KEY'), true );
        $dslb_meta  = is_array($values) ? $values : array();  
        
        do_action('dslb_doctor_meta_fields_before_all', $dslb_meta); ?>
        <table class="form-table">
            <tbody>
                <?php do_action('dslb_doctor_meta_fields_before', $dslb_meta); ?>
                <tr>
                    <th scope="row">
                        <label for="dslb_meta[specialization]">
                            <?php _e('Specialization', 'doctors-slot-booking'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="dslb_meta[specialization]" id="dslb_meta[specialization]"
                               value="<?php echo esc_attr( isset( $dslb_meta['specialization'] ) ? $dslb_meta['specialization'] : '' ); ?>"
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="dslb_meta[available_days]">
                            <?php _e('Available Days', 'doctors-slot-booking'); ?>
                        </label>
                    </th>
                    <td>
                        <select name="dslb_meta[available_days][]" id="dslb_meta[available_days]"
                                class="regular-text" multiple>
                            <?php
                            $days = array(
                                'sunday'    => __('Sunday', 'doctors-slot-booking'),
                                'monday'    => __('Monday', 'doctors-slot-booking'),
                                'tuesday'   => __('Tuesday', 'doctors-slot-booking'),
                                'wednesday' => __('Wednesday', 'doctors-slot-booking'),
                                'thursday'  => __('Thursday', 'doctors-slot-booking'),
                                'friday'    => __('Friday', 'doctors-slot-booking'),
                                'saturday'  => __('Saturday', 'doctors-slot-booking'),
                            );
                            foreach ($days as $key => $day) {
                                $selected = isset($dslb_meta['available_days']) && in_array($key, $dslb_meta['available_days']) ? 'selected' : '';
                                echo '<option value="'.$key.'" '.$selected.'>'.$day.'</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr> 
                <tr>
                    <th scope="row">
                        <label for="dslb_meta[start_time]">
                            <?php _e('Duty Start Time', 'doctors-slot-booking'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="time" name="dslb_meta[start_time]" id="dslb_meta[start_time]"
                               value="<?php echo esc_attr( isset( $dslb_meta['start_time'] ) ? $dslb_meta['start_time'] : '' ); ?>"
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="dslb_meta[end_time]">
                            <?php _e('Duty End Time', 'doctors-slot-booking'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="time" name="dslb_meta[end_time]" id="dslb_meta[end_time]"
                               value="<?php echo esc_attr( isset( $dslb_meta['end_time'] ) ? $dslb_meta['end_time'] : '' ); ?>"
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="dslb_meta[slot_duration]">
                            <?php _e('Slot Duration (in minutes)', 'doctors-slot-booking'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number" name="dslb_meta[slot_duration]" id="dslb_meta[slot_duration]"
                               value="<?php echo esc_attr( isset( $dslb_meta['slot_duration'] ) ? $dslb_meta['slot_duration'] : '' ); ?>"
                               class="regular-text" min="1" max="120">
                        <p class="description">
                            <?php _e('Duration of each appointment slot in minutes.', 'doctors-slot-booking'); ?>
                        </p>
                    </td>
                </tr>
                <?php do_action('dslb_doctor_meta_fields_after', $dslb_meta); ?>
            </tbody>
        </table>
        <?php do_action('dslb_doctor_meta_fields_after_all', $dslb_meta);
    }

    /**
     * Metabox for Booking CPT
     */
    public function meta_box_booking( $post ) {
        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'dslb_booking_meta_data_update', '__dslb_booking_meta_nonce' );

        // Get existing meta values
        $fields = [
            'name',
            'phone',
            'email',
            'doctor_id',
            'appointment_date',
            'appointment_time',
            'symptoms',
            'status',
        ];

        $fields = apply_filters('dslb_booking_meta_fields', $fields, $post->ID);

        $dslb_meta     = [];
        foreach ($fields as $field) {
            $dslb_meta[$field] = get_post_meta($post->ID, Schema::getConstant('BOOKING_META_KEY') . '_' . $field, true);
        }

        do_action('dslb_booking_meta_fields_before_all', $dslb_meta); ?>
        <p>
            <strong><?php _e('Booking Information', 'doctors-slot-booking'); ?></strong><br>
        </p>
        <table class="form-table">
            <tbody>
                <?php do_action('dslb_booking_meta_fields_before', $dslb_meta); ?>
                <tr>
                    <th scope="row">
                        <label for="dslb_meta[status]">
                            <?php _e('Status', 'doctors-slot-booking'); ?>
                        </label>
                    </th>
                    <td>
                        <select name="dslb_meta[status]" id="dslb_meta[status]"
                                class="regular-text">
                            <option value=""><?php _e('Select Status', 'doctors-slot-booking'); ?></option>
                            <option value="confirmed" <?php selected( isset( $dslb_meta['status'] ) ? $dslb_meta['status'] : '', 'confirmed' ); ?>>
                                <?php _e('Confirmed', 'doctors-slot-booking'); ?>
                            </option>
                            <option value="cancelled" <?php selected( isset( $dslb_meta['status'] ) ? $dslb_meta['status'] : '', 'cancelled' ); ?>>
                                <?php _e('Cancelled', 'doctors-slot-booking'); ?>
                            </option>
                            <option value="completed" <?php selected( isset( $dslb_meta['status'] ) ? $dslb_meta['status'] : '', 'completed' ); ?>>
                                <?php _e('Completed', 'doctors-slot-booking'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php _e('Select the status of the booking.', 'doctors-slot-booking'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><i style="color:rgb(186, 28, 28);"><?= __('Note: Editing below information is not recommended. Modifications may result in multiple bookings for the same slot.', 'doctors-slot-booking'); ?></i></td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="dslb_meta[name]">
                            <?php _e('Name', 'doctors-slot-booking'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="dslb_meta[name]" id="dslb_meta[name]"
                               value="<?php echo esc_attr( isset( $dslb_meta['name'] ) ? $dslb_meta['name'] : '' ); ?>"
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="dslb_meta[phone]">
                            <?php _e('Phone', 'doctors-slot-booking'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="dslb_meta[phone]" id="dslb_meta[phone]"
                               value="<?php echo esc_attr( isset( $dslb_meta['phone'] ) ? $dslb_meta['phone'] : '' ); ?>"
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="dslb_meta[email]">
                            <?php _e('Email', 'doctors-slot-booking'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="email" name="dslb_meta[email]" id="dslb_meta[email]"
                               value="<?php echo esc_attr( isset( $dslb_meta['email'] ) ? $dslb_meta['email'] : '' ); ?>"
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="dslb_meta[doctor_id]">
                            <?php _e('Doctor', 'doctors-slot-booking'); ?>
                        </label>
                    </th>
                    <td>
                        <select name="dslb_meta[doctor_id]" id="dslb_meta[doctor_id]"
                                class="regular-text">
                            <option value=""><?php _e('Select Doctor', 'doctors-slot-booking'); ?></option>
                            <?php
                            $doctors = get_posts(array(
                                'post_type'      => 'dslb_doctor',
                                'posts_per_page' => -1,
                                'post_status'    => 'publish',
                            ));
                            foreach ($doctors as $doctor) {
                                $selected = (isset($dslb_meta['doctor_id']) && $dslb_meta['doctor_id'] == $doctor->ID) ? 'selected' : '';
                                echo '<option value="'.$doctor->ID.'" '.$selected.'>'.esc_html($doctor->post_title).'</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="dslb_meta[appointment_date]">
                            <?php _e('Appointment Date', 'doctors-slot-booking'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="date" name="dslb_meta[appointment_date]" id="dslb_meta[appointment_date]"
                               value="<?php echo esc_attr( isset( $dslb_meta['appointment_date'] ) ? $dslb_meta['appointment_date'] : '' ); ?>"
                               class="regular-text">
                        <p class="description">
                            <?php _e('Select the date for the appointment.', 'doctors-slot-booking'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="dslb_meta[appointment_time]">
                            <?php _e('Appointment Time', 'doctors-slot-booking'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="time" name="dslb_meta[appointment_time]" id="dslb_meta[appointment_time]"
                               value="<?php echo esc_attr( isset( $dslb_meta['appointment_time'] ) ? $dslb_meta['appointment_time'] : '' ); ?>"
                               class="regular-text">
                        <p class="description">
                            <?php _e('Select the time for the appointment.', 'doctors-slot-booking'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="dslb_meta[symptoms]">
                            <?php _e('Symptoms', 'doctors-slot-booking'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea name="dslb_meta[symptoms]" id="dslb_meta[symptoms]"
                                  class="regular-text" rows="4"><?php echo esc_textarea( isset( $dslb_meta['symptoms'] ) ? $dslb_meta['symptoms'] : '' ); ?></textarea>
                        <p class="description">
                            <?php _e('Describe the symptoms or reason for the appointment.', 'doctors-slot-booking'); ?>
                        </p>
                    </td>
                </tr>
                <?php do_action('dslb_booking_meta_fields_after', $dslb_meta); ?>
            </tbody>
        </table>
        <?php
        do_action('dslb_booking_meta_fields_after_all', $dslb_meta);
    }


    /**
     * Metaboxes Save for Doctor CPT
     */
    function doctor_save_meta_boxes( $post_id ) {
        // Check if our nonce is set.
        if ( ! isset( $_POST['__dslb_doctor_meta_nonce'] ) ) {
            return;
        }
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $_POST['__dslb_doctor_meta_nonce'], 'dslb_doctor_meta_data_update' ) ) {
            return;
        }
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        /* OK, it's safe for us to save the data now. */
        // Make sure that it is set.
        if ( ! isset( $_POST['dslb_meta'] ) ) {
            return;
        }

        // Add a blank value to the array to prevent the field from being completely removed
        if ( empty( $_POST['dslb_meta'] ) ) {
            $_POST['dslb_meta'] = array();
        }

        // Update the meta field in the database.
        update_post_meta( $post_id, Schema::getConstant('DOCTOR_META_KEY'), $_POST['dslb_meta'] );
    }


    /**
     * Metaboxes Save for Booking CPT
     */
    function booking_save_meta_boxes( $post_id ) {
        // Check if our nonce is set.
        if ( ! isset( $_POST['__dslb_booking_meta_nonce'] ) ) {
            return;
        }
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $_POST['__dslb_booking_meta_nonce'], 'dslb_booking_meta_data_update' ) ) {
            return;
        }
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        /* OK, it's safe for us to save the data now. */
        // Make sure that it is set.
        if ( ! isset( $_POST['dslb_meta'] ) ) {
            return;
        }

        // Add a blank value to the array to prevent the field from being completely removed
        if ( empty( $_POST['dslb_meta'] ) ) {
            $_POST['dslb_meta'] = array();
        }

        // Sanitize and prepare the data
        $dslb_meta = array(
            'name'              => sanitize_text_field( $_POST['dslb_meta']['name'] ?? '' ),
            'phone'             => sanitize_text_field( $_POST['dslb_meta']['phone'] ?? '' ),
            'email'             => sanitize_email( $_POST['dslb_meta']['email'] ?? '' ),
            'doctor_id'         => intval( $_POST['dslb_meta']['doctor_id'] ?? 0 ),
            'appointment_date'  => sanitize_text_field( $_POST['dslb_meta']['appointment_date'] ?? '' ),
            'appointment_time'  => sanitize_text_field( $_POST['dslb_meta']['appointment_time'] ?? '' ),
            'symptoms'          => sanitize_textarea_field( $_POST['dslb_meta']['symptoms'] ?? '' ),
            'status'            => sanitize_text_field( $_POST['dslb_meta']['status'] ?? '' ),
        );

        // Apply filters to allow modification of the meta data before saving
        $dslb_meta = apply_filters('dslb_booking_meta_fields_save', $dslb_meta, $post_id);

        // Update the meta field in the database.
        foreach ( $dslb_meta as $key => $value ) {
            update_post_meta( $post_id, Schema::getConstant('BOOKING_META_KEY') . '_' . $key, $value );
        }
    }

    public function bookings_columns( $columns ) {
        // this will add the column to the end of the array
        $columns['doctor'] = __('Doctor', 'doctors-slot-booking');
        $columns['status'] = 'Status';
        //add more columns as needed

        // as with all filters, we need to return the passed content/variable
        return $columns;
    }

    public function bookings_columns_content( $column, $post_id ) {
        switch ($column) {
            case 'doctor':
                $doctor_id = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY').'_doctor_id', true) ?? 0;
                if (!$doctor_id) {
                    echo __('No Doctor Assigned', 'doctors-slot-booking');
                    break;
                }
                echo get_the_title($doctor_id);
                break;
            case 'status':
                $status = get_post_meta($post_id, Schema::getConstant('BOOKING_META_KEY').'_status', true) ?? '';
                if (!$status) {
                    echo '<span class="dslb-status dslb-status-unknown">'.__('No Status', 'doctors-slot-booking').'</span>';
                    break;
                }
                $status = ucfirst($status); // Capitalize the first letter
                // Optionally, you can add classes for styling based on status
                switch ($status) {
                    case 'Pending':
                        $status = '<span class="dslb-status dslb-status-pending">'.$status.'</span>';
                        break;
                    case 'Confirmed':
                        $status = '<span class="dslb-status dslb-status-confirmed">'.$status.'</span>';
                        break;
                    case 'Cancelled':
                        $status = '<span class="dslb-status dslb-status-cancelled">'.$status.'</span>';
                        break;
                    case 'Completed':
                        $status = '<span class="dslb-status dslb-status-completed">'.$status.'</span>';
                        break;
                    default:
                        $status = '<span class="dslb-status dslb-status-unknown">'.$status.'</span>';
                        break;
                }
                echo $status;
                break;
        }
    }


    /**
     * Add filter dropdown for bookings
     */
    public function add_bookings_filter_dropdown() {
        global $typenow;
        if ($typenow == DSLB_TOKEN . '_bookings') {
            $statuses = array(
                ''          => __('All Statuses', 'doctors-slot-booking'),
                'confirmed' => __('Confirmed', 'doctors-slot-booking'),
                'cancelled' => __('Cancelled', 'doctors-slot-booking'),
                'completed' => __('Completed', 'doctors-slot-booking'),
                'pending'   => __('Pending', 'doctors-slot-booking'),
            );
            ?>
            <select name="dslb_booking_status" id="dslb_booking_status" class="postform">
                <?php
                foreach ($statuses as $value => $label) {
                    $selected = (isset($_GET['dslb_booking_status']) && $_GET['dslb_booking_status'] == $value) ? 'selected' : '';
                    echo '<option value="'. esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
                }
                ?>
            </select>
            <?php

            $doctors = get_posts(array(
                'post_type'      => DSLB_TOKEN . '_doctor',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
            ));
            if ($doctors) {
                echo '<select name="dslb_booking_doctor" id="dslb_booking_doctor" class="postform">';
                echo '<option value="">' . __('All Doctors', 'doctors-slot-booking') . '</option>';
                foreach ($doctors as $doctor) {
                    $selected = (isset($_GET['dslb_booking_doctor']) && $_GET['dslb_booking_doctor'] == $doctor->ID) ? 'selected' : '';
                    echo '<option value="' . esc_attr($doctor->ID) . '" ' . $selected . '>' . esc_html($doctor->post_title) . '</option>';
                }
                echo '</select>';
            }
            ?>
            <input type="date" name="dslb_booking_date" id="dslb_booking_date" class="postform"
                   value="<?php echo isset($_GET['dslb_booking_date']) ? esc_attr($_GET['dslb_booking_date']) : ''; ?>"
                   placeholder="<?php esc_attr_e('Select Date', 'doctors-slot-booking'); ?>" />
            <?php
        }
    }

    /**
     * Filter bookings based on selected status
     */
    public function filter_bookings($query) {
        global $pagenow;
        if (is_admin() && $pagenow == 'edit.php' && $query->is_main_query() && $query->get('post_type') == DSLB_TOKEN . '_bookings') {
            if (isset($_GET['dslb_booking_status']) && $_GET['dslb_booking_status'] != '') {
                $status = sanitize_text_field($_GET['dslb_booking_status']);
                $meta_query = array(
                    'relation' => 'AND',
                    array(
                        'key'     => Schema::getConstant('BOOKING_META_KEY').'_status',
                        'value'   => $status,
                        'compare' => '=',
                    ),
                );
                $query->set('meta_query', $meta_query);
            }

            if (isset($_GET['dslb_booking_doctor']) && $_GET['dslb_booking_doctor'] != '') {
                $doctor_id = intval($_GET['dslb_booking_doctor']);
                $meta_query = array(
                    'relation' => 'AND',
                    array(
                        'key'     => Schema::getConstant('BOOKING_META_KEY').'_doctor_id',
                        'value'   => $doctor_id,
                        'compare' => '=',
                    ),
                );
                $query->set('meta_query', $meta_query);
            }

            if (isset($_GET['dslb_booking_date']) && $_GET['dslb_booking_date'] != '') {
                $appointment_date = sanitize_text_field($_GET['dslb_booking_date']);
                $meta_query = array(
                    'relation' => 'AND',
                    array(
                        'key'     => Schema::getConstant('BOOKING_META_KEY').'_appointment_date',
                        'value'   => $appointment_date,
                        'compare' => '=',
                    ),
                );
                $query->set('meta_query', $meta_query);
            }
        }
    }


    /**
     * Filter bookings search query
     */
    public function filter_bookings_search($search, $wp_query) {
        global $wpdb;
        if (is_admin() && $wp_query->is_main_query() && $wp_query->get('post_type') == DSLB_TOKEN . '_bookings' && !empty($search)) {
            $search = '';
            $search_term = $wp_query->get('s');
            if (!empty($search_term)) { 
                $like = '%' . $wpdb->esc_like($search_term) . '%';
                // Search only in these meta keys
                $meta_keys = [
                    Schema::getConstant('BOOKING_META_KEY').'_name',
                    Schema::getConstant('BOOKING_META_KEY').'_phone',
                    Schema::getConstant('BOOKING_META_KEY').'_email',
                    Schema::getConstant('BOOKING_META_KEY').'_appointment_date',
                    Schema::getConstant('BOOKING_META_KEY').'_appointment_time',
                ];

                $meta_keys = apply_filters( 'dslb_search_meta_keys', $meta_keys, $search_term );
                
                $meta_conditions = [];
                foreach ($meta_keys as $key) {
                    $meta_conditions[] = $wpdb->prepare("
                        ({$wpdb->postmeta}.meta_key = %s AND {$wpdb->postmeta}.meta_value LIKE %s)
                    ", $key, $like);
                }

                if (empty($meta_conditions)) return $search;

                // Combine the conditions with OR
                $search = ' AND (' . implode(' OR ', $meta_conditions) . ')';
                
                // Join the postmeta table
                add_filter('posts_join', function($join) use ($wpdb) {
                    global $wpdb;
                    $join .= " LEFT JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id)";
                    return $join;
                });
                add_filter('posts_groupby', function($groupby) use ($wpdb) {
                    global $wpdb;
                    // Group by post ID to avoid duplicates
                    return "{$wpdb->posts}.ID";
                });

                return $search;
            }
        }
        return $search;
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