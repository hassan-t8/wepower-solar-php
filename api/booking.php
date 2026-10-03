<?php
/**
 * Consultation booking submission — PHP port of POST /api/booking in server.js.
 * Note: not currently wired to a public form (matches the original — the
 * React app never rendered a booking form either, only the admin panel
 * lists bookings), but the endpoint is ported for parity/future use.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/notifications.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);
checkFormRateLimit('booking');

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$name = clean($input['name'] ?? '');
$email = clean($input['email'] ?? '');
$phone = clean($input['phone'] ?? '');
$city = clean($input['city'] ?? '');
$propertyType = clean($input['property_type'] ?? '');
$serviceType = clean($input['service_type'] ?? '');
$preferredDate = clean($input['preferred_date'] ?? '');
$notes = clean($input['notes'] ?? '');

if (!$name || !$email || !$phone) {
    jsonResponse(['error' => 'Name, email and phone are required.'], 400);
}

if (!isValidEmail($email)) {
    jsonResponse(['error' => 'Please enter a valid email address.'], 400);
}
$phone = normalizePhone($phone);
if ($phone === null) {
    jsonResponse(['error' => 'Please enter a valid phone number (Pakistan: 10 digits starting with 3, e.g. +92 3001234567).'], 400);
}

try {
    $id = insertGetId(
        'INSERT INTO bookings (name, email, phone, city, property_type, service_type, preferred_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [$name, $email, $phone, $city, $propertyType, $serviceType, $preferredDate, $notes]
    );

    notifyAdmins('bookings', 'New site survey booking: ' . $name, trim(($serviceType ?: 'Site survey') . ($preferredDate ? ' · ' . $preferredDate : '') . ' · ' . $phone), $id);

    sendMail(
        getAdminRecipients(),
        'New Booking Request - ' . ($serviceType ?: 'Consultation'),
        mailWrap('New Booking / Consultation Request', [
            'Name' => $name, 'Email' => $email, 'Phone' => $phone, 'City' => $city,
            'Property Type' => $propertyType, 'Service Type' => $serviceType,
            'Preferred Date' => $preferredDate, 'Notes' => $notes,
        ])
    );

    $extraRows = '';
    if ($serviceType) $extraRows .= '<tr><td style="padding:8px 12px;background:#f9fafb;font-weight:600;color:#374151;width:40%;border-bottom:1px solid #e5e7eb">Service</td><td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;color:#111827">' . h($serviceType) . '</td></tr>';
    if ($city) $extraRows .= '<tr><td style="padding:8px 12px;background:#f9fafb;font-weight:600;color:#374151;border-bottom:1px solid #e5e7eb">City</td><td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;color:#111827">' . h($city) . '</td></tr>';

    sendMail(
        $email,
        'Consultation Booking Received — WePower Solar',
        mailWrapCustomer('Your booking is confirmed!', '
            <p style="color:#374151;font-size:15px;line-height:1.7">Hi <strong>' . h($name) . '</strong>,</p>
            <p style="color:#374151;font-size:15px;line-height:1.7">
              We\'ve received your consultation booking request' . ($preferredDate ? ' for <strong>' . h($preferredDate) . '</strong>' : '') . '.
              Our team will contact you at <strong>' . h($phone) . '</strong> to confirm the appointment details.
            </p>
            <table style="width:100%;border-collapse:collapse;font-size:14px;margin:16px 0">' . $extraRows . '</table>')
    );

    jsonResponse(['success' => true, 'id' => $id, 'message' => 'Booking received! Our team will reach out to confirm.']);
} catch (Throwable $e) {
    error_log('[booking.php] ' . $e->getMessage());
    jsonResponse(['error' => 'Server error. Please try again.'], 500);
}
