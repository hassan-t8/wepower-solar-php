<?php
/**
 * Job application submission (multipart, includes resume upload) —
 * PHP port of POST /api/careers in server.js.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/notifications.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);
checkFormRateLimit('careers');

$fullName = clean($_POST['full_name'] ?? '');
$email = clean($_POST['email'] ?? '');
$phone = clean($_POST['phone'] ?? '');
$position = clean($_POST['position'] ?? '');
$experienceYears = (int) ($_POST['experience_years'] ?? 0);
$education = clean($_POST['education'] ?? '');
$coverLetter = clean($_POST['cover_letter'] ?? '');

if (!$fullName || !$email || !$phone || !$position) {
    jsonResponse(['error' => 'Name, email, phone, and position are required.'], 400);
}

if (!isValidEmail($email)) {
    jsonResponse(['error' => 'Please enter a valid email address.'], 400);
}
$phone = normalizePhone($phone);
if ($phone === null) {
    jsonResponse(['error' => 'Please enter a valid phone number (Pakistan: 10 digits starting with 3, e.g. +92 3001234567).'], 400);
}

$resumeFile = null;
try {
    if (!empty($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE) {
        $resumeFile = handleUpload(
            $_FILES['resume'],
            ['.pdf', '.doc', '.docx'],
            5 * 1024 * 1024,
            __DIR__ . '/../uploads/resumes'
        );
    }
} catch (Throwable $e) {
    jsonResponse(['error' => $e->getMessage()], 400);
}

try {
    $id = insertGetId(
        'INSERT INTO careers (full_name, email, phone, position, experience_years, education, cover_letter, resume_filename) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [$fullName, $email, $phone, $position, $experienceYears, $education, $coverLetter, $resumeFile]
    );

    notifyAdmins('careers', 'New job application: ' . $fullName, $position . ($resumeFile ? ' · resume attached' : '') . ' · ' . $phone, $id);

    sendMail(
        getAdminRecipients(),
        'New Job Application - ' . $position,
        mailWrap('New Career Application', [
            'Name' => $fullName, 'Email' => $email, 'Phone' => $phone, 'Position' => $position,
            'Experience' => $experienceYears . ' years', 'Education' => $education,
            'Cover Letter' => $coverLetter,
            'Resume' => $resumeFile ? 'Uploaded: ' . $resumeFile : 'Not uploaded',
        ]),
        $email ?: null // reply to the customer
    );

    sendMail(
        $email,
        'Application Received: ' . $position . ' — WePower Solar',
        mailWrapCustomer('Application Received!', '
            <p style="color:#374151;font-size:15px;line-height:1.7">Hi <strong>' . h($fullName) . '</strong>,</p>
            <p style="color:#374151;font-size:15px;line-height:1.7">
              Thank you for applying for the <strong>' . h($position) . '</strong> position at WePower Solar Solutions.
              We\'ve received your application and our HR team will review it carefully.
            </p>
            <p style="color:#374151;font-size:15px;line-height:1.7">
              If your profile matches our requirements, we\'ll be in touch within <strong>5–7 business days</strong>.
            </p>
            <p style="color:#374151;font-size:14px;line-height:1.7;color:#6b7280">
              In the meantime, follow us on LinkedIn and Instagram to stay up to date with WePower news.
            </p>')
    );

    jsonResponse(['success' => true, 'id' => $id, 'message' => 'Application submitted successfully!']);
} catch (Throwable $e) {
    error_log('[careers.php] ' . $e->getMessage());
    jsonResponse(['error' => $e->getMessage() ?: 'Server error.'], 500);
}
