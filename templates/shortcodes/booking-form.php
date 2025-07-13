<?php 
namespace RahulK\DSLB;
defined('ABSPATH') || exit;

$token = defined('DSLB_TOKEN') ? DSLB_TOKEN : 'dslb'; 

$personalFields = apply_filters('dslb_booking_form_personal_fields', [
    'patient_name' => __('Patient Name', 'dslb'),
    'patient_phone' => __('Phone Number', 'dslb'),
    'patient_email' => __('Email Address', 'dslb'),
]);

$requiredFields = apply_filters('dslb_booking_form_personal_required_fields', [
    'patient_name',
    'patient_email',
]);


do_action('dslb_booking_form_before', $token); ?>
<form method="post" class="<?= $token ?>-booking-form" id="<?= $token ?>-booking-form">
    <?php do_action('dslb_booking_form_fields_before', $token); 

    foreach ($personalFields as $field => $label) : ?>
        <div class="<?= $token ?>-row">
            <label for="<?= $token ?>_<?= $field ?>"><?= esc_html($label) ?><?php if (in_array($field, $requiredFields)) : ?><span>*</span><?php endif; ?></label>
            <input type="<?= $field === 'patient_email' ? 'email' : 'text' ?>" name="<?= $field ?>" id="<?= $token ?>_<?= $field ?>" <?php if (in_array($field, $requiredFields)) echo 'required'; ?> placeholder="<?= esc_attr($label) ?>" >
        </div>
    <?php endforeach; ?>

    <div class="<?= $token ?>-row">
        <label for="<?= $token ?>_doctor">Doctor<span>*</span></label>
        <select name="doctor" id="<?= $token ?>_doctor" required>
            <option value="" selected disabled>-- Select Doctor --</option>
            <?php
            $doctors = get_posts(['post_type' => 'dslb_doctor', 'numberposts' => -1]);
            foreach ($doctors as $doc) {
                $specialization = get_post_meta( $doc->ID, Schema::getConstant('DOCTOR_META_KEY'), true )['specialization'] ?? '';
                echo '<option value="' . esc_attr($doc->ID) . '">' . esc_html($doc->post_title) . ' (' . esc_html($specialization) . ')</option>';
            }
            ?>
        </select>
    </div>

    <div class="<?= $token ?>-row">
        <label for="<?= $token ?>_booking_date">Appointment Date<span>*</span></label>
        <input type="text" name="booking_date" id="<?= $token ?>_booking_date" required placeholder="Select a date" disabled>
    </div>

    <div class="<?= $token ?>-row">
        <label for="<?= $token ?>_booking_time">Time Slot<span>*</span></label>
        <select name="booking_time" id="<?= $token ?>_booking_time" required disabled>
            <option value="" selected disabled>-- Select Time --</option>
        </select>
    </div>

    <div class="<?= $token ?>-row">
        <label for="<?= $token ?>_symptoms">Symptoms</label>
        <textarea name="symptoms" id="<?= $token ?>_symptoms" rows="4" placeholder="Enter Symptoms"></textarea>
    </div>
    <?php do_action('dslb_booking_form_before_submit', $token); ?>
    <div class="<?= $token ?>-row">
        <button type="submit" class="<?= $token ?>-book-appointment">Book Appointment</button>
    </div>
    <?php do_action('dslb_booking_form_after_submit', $token); ?>
    <div class="<?= $token ?>-booking-response"></div>
    <?php do_action('dslb_booking_form_fields_after', $token); ?>
</form>
<?php do_action('dslb_booking_form_after', $token); ?>