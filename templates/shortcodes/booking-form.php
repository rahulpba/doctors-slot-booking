<?php 
namespace Dudlewebs\DSLB;
defined('ABSPATH') || exit;

$token = defined('DSLB_TOKEN') ? DSLB_TOKEN : 'dslb'; ?>

<form method="post" class="<?= $token ?>-booking-form" id="<?= $token ?>-booking-form">
    <div class="<?= $token ?>-row">
        <label for="<?= $token ?>_patient_name">Patient Name<span>*</span></label>
        <input type="text" name="patient_name" id="<?= $token ?>_patient_name" required>
    </div>

    <div class="<?= $token ?>-row">
        <label for="<?= $token ?>_patient_phone">Phone Number</label>
        <input type="tel" name="patient_phone" id="<?= $token ?>_patient_phone">
    </div>

    <div class="<?= $token ?>-row">
        <label for="<?= $token ?>_patient_email">Email Address<span>*</span></label>
        <input type="email" name="patient_email" id="<?= $token ?>_patient_email" required>
    </div>

    <div class="<?= $token ?>-row">
        <label for="<?= $token ?>_doctor">Doctor<span>*</span></label>
        <select name="doctor" id="<?= $token ?>_doctor" required>
            <option value="" selected disabled>-- Select Doctor --</option>
            <?php
            $doctors = get_posts(['post_type' => 'dslb_doctor', 'numberposts' => -1]);
            foreach ($doctors as $doc) {
                echo '<option value="' . esc_attr($doc->ID) . '">' . esc_html($doc->post_title) . '</option>';
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

    <div class="<?= $token ?>-row">
        <button type="submit" class="<?= $token ?>-book-appointment">Book Appointment</button>
    </div>

    <div class="<?= $token ?>-booking-response"></div>
</form>