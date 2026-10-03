<?php
/**
 * Contact form submission — PHP port of POST /api/contact in server.js.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/notifications.php';
require_once __DIR__ . '/../config/site.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);
checkFormRateLimit('contact');

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$name = clean($input['name'] ?? '');
$email = clean($input['email'] ?? '');
$phone = clean($input['phone'] ?? '');
$subject = clean($input['subject'] ?? '');
$message = clean($input['message'] ?? '');

if (!$name || !$email || !$message) {
    jsonResponse(['error' => 'Name, email, and message are required.'], 400);
}

if (!isValidEmail($email)) {
    jsonResponse(['error' => 'Please enter a valid email address.'], 400);
}
if ($phone !== '') {
    $phone = normalizePhone($phone);
    if ($phone === null) {
        jsonResponse(['error' => 'Please enter a valid phone number (Pakistan: 10 digits starting with 3, e.g. +92 3001234567).'], 400);
    }
}

try {
    $id = insertGetId(
        'INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)',
        [$name, $email, $phone, $subject, $message]
    );

    notifyAdmins('contacts', 'New message from ' . $name, mb_substr($message, 0, 140), $id);

    sendMail(
        getAdminRecipients(),
        'New Contact Form: ' . ($subject ?: 'General Inquiry'),
        mailWrap('New Contact Submission', ['Name' => $name, 'Email' => $email, 'Phone' => $phone, 'Subject' => $subject, 'Message' => $message]),
        $email ?: null // reply to the customer
    );

    sendMail(
        $email,
        'We received your message — WePower Solar',
        mailWrapCustomer('Thank you for reaching out!', '
            <p style="color:#374151;font-size:15px;line-height:1.7">Hi <strong>' . h($name) . '</strong>,</p>
            <p style="color:#374151;font-size:15px;line-height:1.7">
              We\'ve received your message' . ($subject ? ' regarding "<em>' . h($subject) . '</em>"' : '') . ' and our team will review it shortly.
              You can expect a reply within <strong>24 hours</strong>.
            </p>
            <p style="color:#374151;font-size:15px;line-height:1.7">
              In the meantime, feel free to explore our services or call us at <strong>' . h(getSetting('company_phone1') ?: $brand['phones'][0]) . '</strong>.
            </p>')
    );

    jsonResponse(['success' => true, 'id' => $id, 'message' => 'Thank you! We will contact you shortly.']);
} catch (Throwable $e) {
    error_log('[contact.php] ' . $e->getMessage());
    jsonResponse(['error' => 'Server error. Please try again.'], 500);
}
