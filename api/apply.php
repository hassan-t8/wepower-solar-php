<?php
/**
 * Service/quote application — PHP port of POST /api/apply in server.js.
 * Backs the global "Get a Quote" modal.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);
checkFormRateLimit('apply');

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$name = clean($input['name'] ?? '');
$email = clean($input['email'] ?? '');
$phone = clean($input['phone'] ?? '');
$city = clean($input['city'] ?? '');
$propertyType = clean($input['property_type'] ?? '');
$serviceType = clean($input['service_type'] ?? '');
$notes = clean($input['notes'] ?? '');

if (!$name || !$email || !$phone) {
    jsonResponse(['error' => 'Name, email and phone are required.'], 400);
}

try {
    $id = insertGetId(
        'INSERT INTO applications (name, email, phone, city, property_type, service_type, notes) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$name, $email, $phone, $city, $propertyType, $serviceType, $notes]
    );

    sendMail(
        getAdminRecipients(),
        'New Application - ' . ($serviceType ?: 'Solar Quote'),
        mailWrap('New Service Application', [
            'Name' => $name, 'Email' => $email, 'Phone' => $phone, 'City' => $city,
            'Property Type' => $propertyType, 'Service' => $serviceType, 'Notes' => $notes,
        ])
    );

    if ($email) {
        sendMail(
            $email,
            'Quote Request Received — WePower Solar',
            mailWrapCustomer("We've received your request!", '
                <p style="color:#374151;font-size:15px;line-height:1.7">Hi <strong>' . h($name) . '</strong>,</p>
                <p style="color:#374151;font-size:15px;line-height:1.7">
                  Thank you for your interest in <strong>' . h($serviceType ?: 'our solar services') . '</strong>.
                  Our team will review your request and reach out within <strong>24 hours</strong> with a tailored proposal.
                </p>
                <p style="color:#374151;font-size:15px;line-height:1.7">
                  We\'ll call you at <strong>' . h($phone) . '</strong> to schedule a free on-site survey.
                </p>')
        );
    }

    jsonResponse(['success' => true, 'id' => $id, 'message' => 'Application received! Our team will reach out shortly.']);
} catch (Throwable $e) {
    error_log('[apply.php] ' . $e->getMessage());
    jsonResponse(['error' => 'Server error. Please try again.'], 500);
}
