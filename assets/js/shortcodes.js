jQuery(function ($) {
	// Initialize date picker
	flatpickr("#dslb_booking_date", {
		minDate: "today",
		dateFormat: "Y-m-d",
		enableTime: false,
	});

	function loadTimeSlots(doctorId, bookingDate) {
		if (doctorId && bookingDate) {
			$.post(
				dslb_ajax_object.ajax_url,
				{
					action: "get_available_slots",
					doctor: doctorId,
					booking_date: bookingDate,
				},
				function (slots) {
					let options = slots.length
						? [
								'<option value="" disabled selected>-- Select Time --</option>',
						  ]
						: [
								'<option value="" disabled selected>No slots available</option>',
						  ];
					if (slots.length) {
						slots.forEach(function (slot) {
							const [hour, minute] = slot.split(":").map(Number);
							const ampm = hour >= 12 ? "PM" : "AM";
							const hour12 = hour % 12 || 12;
							const displayTime = `${hour12}:${minute
								.toString()
								.padStart(2, "0")} ${ampm}`;

							options.push(
								`<option value="${slot}">${displayTime}</option>`
							);
						});
					}

					$("#dslb_booking_time").html(options.join(""));
					$("#dslb_booking_time").prop("disabled", !slots.length);
				}
			);
		}
	}

	// Activate the date picker
	$("#dslb_doctor").on("change", function () {
		const doctorId = $(this).val();
		if (doctorId) {
			// get doctor's available dates
			$.post(
				dslb_ajax_object.ajax_url,
				{
					action: "get_available_days",
					doctor: doctorId,
				},
				function (response) {
					if (response.success) {
						const availableDays = response.days;

						$("#dslb_booking_date").flatpickr({
							disable: [
								function (date) {
									return !availableDays.includes(
										date
											.toLocaleString("default", {
												weekday: "long",
											})
											.toLowerCase()
									);
								},
							],
						});
						$("#dslb_booking_date").prop("disabled", false).val("");
						$("#dslb_booking_time").prop("disabled", true).val("");
					} else if (response.data === "no_doctor_available") {
						alert("No doctors available. Please check back later.");
						$("#dslb_booking_date").prop("disabled", true).val("");
						$("#dslb_booking_time").prop("disabled", true).val("");
					} else {
						alert(response.data);
					}
				}
			);
		}
	});

	// Get available slots when date changes
	$("#dslb_booking_date").on("change", function () {
		const doctorId = $(this)
			.parents(".dslb-booking-form")
			.find("#dslb_doctor")
			.val();
		const bookingDate = $(this)
			.parents(".dslb-booking-form")
			.find("#dslb_booking_date")
			.val();
		if (doctorId && bookingDate) {
			loadTimeSlots(doctorId, bookingDate);
		}
	});

	// Enable submit button when all fields are filled
	$(".dslb-booking-form input, .dslb-booking-form select").on(
		"change keyup",
		function () {
			const form = $(this).closest(".dslb-booking-form");
			validateForm(form, false);
		}
	);

	// Validate form fields
	function validateField(field, show_error = true) {
		let isValid = true;
		if (!field.length) {
			return isValid; // If field doesn't exist, return true
		}
		let hasExistingError = field.next(".dslb-error-message").length > 0;
		if (hasExistingError) {
			field.removeClass("dslb-error");
			field.next(".dslb-error-message").remove();
		}

		const value = field.val();
		if (field.prop("required") && !value) {
			isValid = false;
			if (show_error || hasExistingError) {
				field.addClass("dslb-error");
				field.next(".dslb-error-message").remove();
				field.after(
					'<div class="dslb-error-message">This field is required.</div>'
				);
			}
		} else if (field.attr("type") === "email") {
			const emailRegEx =
				/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
			if (!emailRegEx.test(value.trim())) {
				isValid = false;
				if (show_error || hasExistingError) {
					field.addClass("dslb-error");
					field.next(".dslb-error-message").remove();
					field.after(
						'<div class="dslb-error-message">Please enter a valid email address.</div>'
					);
				}
			}
		} else if (field.attr("name") === "patient_name") {
			const nameRegEx = /^[a-zA-Z0-9 ]+$/;
			if (!nameRegEx.test(value.trim())) {
				isValid = false;
				if (show_error || hasExistingError) {
					field.addClass("dslb-error");
					field.next(".dslb-error-message").remove();
					field.after(
						'<div class="dslb-error-message">Name should not have any special characters.</div>'
					);
				}
			}
		} else {
			field.removeClass("dslb-error");
			field.next(".dslb-error-message").remove();
		}
		return isValid;
	}

	// Enable the book appointment button when all fields are filled
	function validateForm(form, show_error = true) {
		let isValid = true;
		form.find("input, select").each(function () {
			const field = $(this);
			if (!validateField(field, show_error)) {
				isValid = false;
			}
		});
		return isValid;
	}

	$("#dslb-booking-form").on("submit", function (e) {
		e.preventDefault(); // Prevent default form submission
		if (validateForm($(this), true)) {
			e.preventDefault();
			const formData = $(this).serialize();
			$.post(
				dslb_ajax_object.ajax_url,
				formData + "&action=dslb_book_appointment",
				function (response) {
					const responseDiv = $(".dslb-booking-response");
					if (response.success) {
						responseDiv.html(
							`<div class="dslb-success">${response.data}</div>`
						);
						setTimeout(function () {
							responseDiv.html("");
						}, 5000); // Clear response after 5 seconds
						$("#dslb-booking-form")[0].reset();
						$("#dslb_booking_time").prop("disabled", true);
						$("#dslb_booking_date").flatpickr().clear();
					} else {
						responseDiv.html(
							`<div class="dslb-error">${response.data}</div>`
						);
						setTimeout(function () {
							responseDiv.html("");
						}, 5000); // Clear response after 5 seconds
					}
				}
			);
		}
	});
});
