<?php
/**
 * Email sending — PHP port of mailer.js, using vendored PHPMailer
 * (no Composer). SMTP config resolves DB-first (site_settings, editable
 * via the admin panel) then falls back to config.php constants — same
 * resolution order as the original getSmtpConfig().
 */

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

function getSmtpConfig(): array
{
    return [
        'host' => getSetting('smtp_host') ?: SMTP_HOST_FALLBACK,
        'port' => (int) (getSetting('smtp_port') ?: SMTP_PORT_FALLBACK),
        'user' => getSetting('smtp_user') ?: SMTP_USER_FALLBACK,
        'pass' => getSetting('smtp_pass') ?: SMTP_PASS_FALLBACK,
        'secure' => (getSetting('smtp_secure') ?: SMTP_SECURE_FALLBACK) === 'true',
        'fromName' => getSetting('smtp_from_name') ?: SMTP_FROM_NAME_FALLBACK,
    ];
}

/**
 * Send an email. Mirrors sendMail() in mailer.js: swallows all failures
 * (returns a status array instead of throwing) so a bad/missing SMTP
 * config never blocks the caller's DB insert + HTTP response — matches
 * the original's fire-and-forget `.catch(() => {})` behavior, just
 * synchronous since PHP has no unawaited-promise equivalent.
 */
function sendMail(string $to, string $subject, string $html): array
{
    $cfg = getSmtpConfig();
    if (!$cfg['user'] || !$cfg['pass']) {
        error_log('[MAIL] SMTP not configured — skipping email. Data still saved to DB.');
        return ['skipped' => true];
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $cfg['host'];
        $mail->Port = $cfg['port'];
        $mail->SMTPAuth = true;
        $mail->Username = $cfg['user'];
        $mail->Password = $cfg['pass'];
        $mail->SMTPSecure = $cfg['secure'] ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($cfg['user'], $cfg['fromName']);
        foreach (array_filter(array_map('trim', explode(',', $to))) as $addr) {
            $mail->addAddress($addr);
        }
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;

        $mail->send();
        return ['sent' => true];
    } catch (PHPMailerException $e) {
        error_log('[MAIL] Send failed: ' . $mail->ErrorInfo);
        return ['error' => $mail->ErrorInfo];
    } catch (Throwable $e) {
        error_log('[MAIL] Send failed: ' . $e->getMessage());
        return ['error' => $e->getMessage()];
    }
}

/** Admin-facing notification template — verbatim port of wrap() in mailer.js. */
function mailWrap(string $title, array $rows): string
{
    $tableRows = '';
    foreach ($rows as $k => $v) {
        $val = ($v === null || $v === '') ? '-' : nl2br(h((string) $v));
        $tableRows .= '<tr><td style="padding:10px 14px;border-bottom:1px solid #e5e7eb;background:#f9fafb;font-weight:600;color:#374151;width:35%">' . h($k) . '</td><td style="padding:10px 14px;border-bottom:1px solid #e5e7eb;color:#111827">' . $val . '</td></tr>';
    }
    $year = date('Y');
    return <<<HTML
    <div style="font-family:'Segoe UI',Arial,sans-serif;max-width:640px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden">
      <div style="background:linear-gradient(135deg,#16a34a,#22c55e);padding:24px;color:#fff;text-align:center">
        <h1 style="margin:0;font-size:22px;letter-spacing:.5px">WePower Solar Solutions</h1>
        <p style="margin:6px 0 0;opacity:.9">{$title}</p>
      </div>
      <div style="padding:24px">
        <table style="width:100%;border-collapse:collapse;font-size:14px">{$tableRows}</table>
        <p style="margin-top:24px;padding:14px;background:#f0fdf4;border-left:4px solid #22c55e;color:#166534;border-radius:6px;font-size:13px">
          Automated notification from your WePower website. Please contact the lead promptly.
        </p>
      </div>
      <div style="background:#111827;color:#9ca3af;padding:14px;text-align:center;font-size:12px">
        &copy; {$year} WePower Solar Solutions
      </div>
    </div>
    HTML;
}

/** Customer-facing confirmation template — verbatim port of wrapCustomer() in mailer.js. */
function mailWrapCustomer(string $heading, string $bodyHtml): string
{
    $year = date('Y');
    return <<<HTML
    <div style="font-family:'Segoe UI',Arial,sans-serif;max-width:600px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden">
      <div style="background:linear-gradient(135deg,#16a34a,#22c55e);padding:28px 24px;color:#fff;text-align:center">
        <div style="width:60px;height:60px;background:rgba(255,255,255,.2);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;font-size:28px">&#9728;</div>
        <h1 style="margin:0;font-size:20px;letter-spacing:.5px">WePower Solar Solutions</h1>
        <p style="margin:6px 0 0;opacity:.9;font-size:14px">Empower Yourself with Solar Energy</p>
      </div>
      <div style="padding:28px 24px">
        <h2 style="margin:0 0 14px;font-size:18px;color:#111827">{$heading}</h2>
        {$bodyHtml}
        <div style="margin-top:24px;padding:16px;background:#f0fdf4;border-left:4px solid #22c55e;color:#166534;border-radius:6px;font-size:13px;line-height:1.6">
          <strong>Need help?</strong> Reply to this email or contact us at <a href="mailto:info@wepower.pk" style="color:#16a34a">info@wepower.pk</a>
        </div>
      </div>
      <div style="background:#f9fafb;padding:16px;text-align:center;font-size:12px;color:#6b7280;border-top:1px solid #e5e7eb">
        &copy; {$year} WePower Solar Solutions &nbsp;|&nbsp; Bahria Town Phase 7, Rawalpindi
      </div>
    </div>
    HTML;
}
