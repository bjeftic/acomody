<?php

return [

    // Auth
    'verify_email' => [
        'subject' => 'Verify Your Email Address',
        'title' => 'Verify Your Email Address',
        'body' => 'Thank you for registering with :app. Please verify your email address by clicking the button below.',
        'btn' => 'Verify Email Address',
        'expire_note' => 'This link will expire in :minutes minutes.',
        'no_account_note' => 'If you did not create an account, no further action is required.',
        'fallback_note' => 'If the button above does not work, copy and paste the following link into your browser:',
    ],

    'reset_password' => [
        'subject' => 'Reset Your Password',
        'title' => 'Reset Your Password',
        'body' => 'You are receiving this email because we received a password reset request for your account. Click the button below to reset your password.',
        'btn' => 'Reset Password',
        'expire_note' => 'This password reset link will expire in :minutes minutes.',
        'no_request_note' => 'If you did not request a password reset, no further action is required.',
        'fallback_note' => 'If the button above does not work, copy and paste the following link into your browser:',
    ],

    // Shared booking detail labels
    'label_property' => 'Property',
    'label_guest' => 'Guest',
    'label_check_in' => 'Check-in',
    'label_check_out' => 'Check-out',
    'label_nights' => 'Nights',
    'label_guests' => 'Guests',
    'label_booking_id' => 'Booking ID',
    'label_total' => 'Total',
    'label_estimated_total' => 'Estimated Total',
    'label_reason' => 'Reason',
    'label_refund' => 'Refund',
    'no_refund' => 'No refund (non-refundable policy)',
    'guest_notes_label' => 'Guest notes:',
    'your_notes_label' => 'Your notes:',

    // Booking — requested (guest)
    'booking_requested_guest' => [
        'subject' => 'Booking Request Submitted — :title',
        'title' => 'Booking Request Submitted',
        'body' => 'Your booking request has been sent to the host. You will receive a confirmation once they respond.',
        'note' => 'Dates will not be blocked until the host confirms your request.',
    ],

    // Booking — requested (host)
    'booking_requested_host' => [
        'subject' => 'New Booking Request — :title',
        'title' => 'New Booking Request',
        'body' => 'You have a new booking request for :property. Please log in to confirm or decline.',
        'btn' => 'Review Request',
        'dashboard_note' => 'Or log in to your :link to confirm or decline.',
        'dashboard_link' => 'host dashboard',
    ],

    // Booking — confirmed (guest)
    'booking_confirmed_guest' => [
        'subject' => 'Booking Confirmed — :title',
        'title' => '✓ Booking Confirmed',
        'body' => 'Your booking has been confirmed. Here are the details:',
        'note' => 'If you have any questions, please contact the host directly through Acomody.',
    ],

    // Booking — confirmed (host)
    'booking_confirmed_host' => [
        'subject' => 'New Booking Confirmed — :title',
        'title' => '✓ New Booking Confirmed',
        'body' => 'A new booking for :property has been confirmed. Here are the details:',
        'btn' => 'View in Dashboard',
    ],

    // Booking — cancelled (guest)
    'booking_cancelled_guest' => [
        'subject' => 'Booking Cancelled — :title',
        'title' => 'Booking Cancelled',
        'body' => 'Your booking for :property has been cancelled.',
        'note' => 'If you believe this is an error, please contact us at Acomody.',
    ],

    // Booking — cancelled (host)
    'booking_cancelled_host' => [
        'subject' => 'Booking Cancelled — :title',
        'title' => 'Booking Cancelled by Guest',
        'body' => 'A guest has cancelled their booking for :property. The dates are now available again.',
    ],

    // Booking — declined (guest)
    'booking_declined' => [
        'subject' => 'Booking Request Declined — :title',
        'title' => 'Booking Request Declined',
        'body' => 'Unfortunately, the host has declined your booking request for :property.',
        'footer' => 'No payment has been taken. You can search for other available properties on Acomody.',
    ],

    // Accommodation — approved
    'accommodation_approved' => [
        'subject' => 'Your Accommodation Has Been Approved',
        'title' => '✓ Accommodation Approved',
        'body' => 'Great news! Your accommodation :property has been reviewed and approved by our team.',
        'now_live' => 'Your listing is now live on Acomody and guests can start searching and booking.',
        'warning_title' => 'One more step to go live',
        'warning_body' => "Your listing has been approved but it won't appear in search results until you complete your host profile (display name, contact email, and phone number). It only takes a minute.",
        'warning_btn' => 'Complete host profile →',
    ],

    // Accommodation — rejected
    'accommodation_rejected' => [
        'subject' => 'Accommodation Not Approved',
        'title' => 'Accommodation Not Approved',
        'body1' => 'Thank you for submitting your accommodation :property for review.',
        'body2' => 'After careful review, our team was unable to approve your submission at this time.',
        'reason_label' => 'Reason',
        'resubmit' => 'If you believe this was a mistake or have made the necessary changes, you are welcome to re-submit your accommodation for review.',
    ],

    // Accommodation — draft submitted
    'draft_submitted' => [
        'subject' => 'Accommodation Under Review',
        'title' => 'Accommodation Under Review',
        'body' => 'Your accommodation :property has been successfully submitted and is now under review by our team.',
        'next' => "Here's what happens next:",
        'step1' => 'Our team reviews your listing — this usually takes 1–2 business days.',
        'step2' => 'Once approved, your listing will automatically become searchable and guests can start booking.',
        'step3' => "You'll receive an email notification as soon as it goes live.",
        'btn' => 'Go to hosting dashboard →',
    ],

    // Accommodation — draft submitted (profile incomplete)
    'draft_submitted_incomplete' => [
        'subject' => 'Accommodation Under Review',
        'title' => 'Accommodation Under Review',
        'body' => "Your accommodation :property has been successfully submitted and is now under review by our team. We'll notify you once it's approved.",
        'warning_title' => 'Complete your host profile to go live',
        'warning_body' => "Once your accommodation is approved, it won't appear in search results until your host profile is complete. Please add your display name, contact email, and phone number — it only takes a minute.",
        'warning_btn' => 'Complete host profile →',
    ],

    // Accommodation — review comment
    'review_comment' => [
        'subject' => 'Reviewer Comment on Your Submission',
        'title' => 'Reviewer Comment',
        'body1' => 'Our review team has left a comment on your accommodation submission :property.',
        'body2' => 'Please review the comment and make any necessary updates to your submission.',
    ],

    // Host — listings now live
    'listings_live' => [
        'subject' => 'Your Listings Are Now Live on Acomody',
        'title' => '✓ Your Listings Are Now Live!',
        'body_singular' => 'Your host profile is now complete — your listing is now searchable on Acomody and guests can start booking.',
        'body_plural' => 'Your host profile is now complete — your :count listings are now searchable on Acomody and guests can start booking.',
        'btn' => 'Go to hosting dashboard →',
    ],

    // Common
    'hi' => 'Hi :name,',
    'support_note' => 'If you have any questions, please contact our support team.',
    'copyright' => 'All rights reserved.',

];
