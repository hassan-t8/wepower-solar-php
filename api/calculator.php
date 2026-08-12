<?php
/**
 * Load calculator submission — PHP port of POST /api/calculator in server.js.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);
checkFormRateLimit('calculator');

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$name = clean($input['name'] ?? '');
$email = clean($input['email'] ?? '');
$phone = clean($input['phone'] ?? '');
$city = clean($input['city'] ?? '');
$propertyType = clean($input['property_type'] ?? '');
$numPeople = (int) ($input['num_people'] ?? 0);
$appliances = is_array($input['appliances'] ?? null) ? $input['appliances'] : [];
$totalLoadW = (float) ($input['total_load_w'] ?? 0);
$recommendedKva = (float) ($input['recommended_kva'] ?? 0);
$estimatedBill = (float) ($input['estimated_bill'] ?? 0);

if (empty($appliances)) {
    jsonResponse(['error' => 'Please add at least one appliance.'], 400);
}

try {
    $id = insertGetId(
        'INSERT INTO load_calculations (name, email, phone, city, property_type, num_people, appliances_json, total_load_w, recommended_kva, estimated_bill) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [$name, $email, $phone, $city, $propertyType, $numPeople, json_encode($appliances), $totalLoadW, $recommendedKva, $estimatedBill]
    );

    $applianceRows = '';
    foreach ($appliances as $a) {
        $applianceRows .= '<tr><td style="padding:6px 10px;border-bottom:1px solid #eee">' . h($a['name'] ?? '') . '</td>'
            . '<td style="padding:6px 10px;border-bottom:1px solid #eee">' . h($a['quantity'] ?? '') . '</td>'
            . '<td style="padding:6px 10px;border-bottom:1px solid #eee">' . h($a['power'] ?? '') . 'W</td>'
            . '<td style="padding:6px 10px;border-bottom:1px solid #eee">' . h($a['total'] ?? '') . 'W</td></tr>';
    }

    sendMail(
        getAdminRecipients(),
        'New Load Calculation - ' . $recommendedKva . 'KVA Recommended',
        mailWrap('New Load Calculator Submission', [
            'Name' => $name ?: '—', 'Email' => $email ?: '—', 'Phone' => $phone ?: '—', 'City' => $city ?: '—',
            'Property Type' => $propertyType ?: '—', 'People' => $numPeople,
            'Total Load' => $totalLoadW . ' W', 'Recommended System' => $recommendedKva . ' KVA',
            'Appliances' => '<table style="width:100%;border-collapse:collapse;margin-top:6px;font-size:13px"><tr style="background:#f3f4f6"><th style="padding:6px 10px;text-align:left">Item</th><th style="padding:6px 10px;text-align:left">Qty</th><th style="padding:6px 10px;text-align:left">Power</th><th style="padding:6px 10px;text-align:left">Total</th></tr>' . $applianceRows . '</table>',
        ])
    );

    if ($email) {
        sendMail(
            $email,
            'Your Solar Assessment Results — ' . $recommendedKva . ' KVA System — WePower Solar',
            mailWrapCustomer('Your Solar Load Assessment', '
                <p style="color:#374151;font-size:15px;line-height:1.7">' . ($name ? 'Hi <strong>' . h($name) . '</strong>,' : 'Hi,') . '</p>
                <p style="color:#374151;font-size:15px;line-height:1.7">
                  Based on your load data, here is a summary of your solar assessment:
                </p>
                <table style="width:100%;border-collapse:collapse;font-size:14px;margin:16px 0;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb">
                  <tr><td style="padding:10px 14px;background:#f0fdf4;font-weight:700;color:#166534;width:50%">Recommended System</td><td style="padding:10px 14px;background:#f0fdf4;font-weight:700;color:#16a34a;font-size:18px">' . $recommendedKva . ' KVA</td></tr>
                  <tr><td style="padding:10px 14px;background:#f9fafb;font-weight:600;color:#374151;border-top:1px solid #e5e7eb">Total Connected Load</td><td style="padding:10px 14px;border-top:1px solid #e5e7eb;color:#111827">' . $totalLoadW . ' W</td></tr>
                </table>
                <p style="color:#374151;font-size:15px;line-height:1.7">
                  Our team will reach out to provide a detailed proposal and pricing for your ' . $recommendedKva . ' KVA system.
                  You can also call us directly at <strong>0335-5777898</strong>.
                </p>')
        );
    }

    jsonResponse(['success' => true, 'id' => $id]);
} catch (Throwable $e) {
    error_log('[calculator.php] ' . $e->getMessage());
    jsonResponse(['error' => 'Server error.'], 500);
}
