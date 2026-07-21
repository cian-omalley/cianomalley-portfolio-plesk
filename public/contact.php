<?php
/**
 * Minimal contact handler for the Plesk build. No framework, no dependencies —
 * Plesk's bundled PHP serves this file directly from the document root. Sits
 * next to the static Astro output, so nothing else on the site needs PHP.
 *
 * Setup: set $TO to your address (or a CIAN_CONTACT_TO environment variable in
 * Plesk → Websites & Domains → PHP Settings / Additional directives). Optionally
 * configure Plesk's mail service so mail() delivers reliably.
 *
 * Security: honeypot + length caps + header-injection stripping + basic rate
 * hint. It never echoes attacker input back into HTML unescaped.
 */

declare(strict_types=1);

$TO = getenv('CIAN_CONTACT_TO') ?: 'you@example.com'; // <-- change me on the server
$SITE = 'cianomalley.works';

header('Content-Type: text/html; charset=utf-8');

function bail(int $code, string $msg): void {
    http_response_code($code);
    $safe = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
    echo "<!doctype html><meta charset=utf-8><meta name=viewport content='width=device-width,initial-scale=1'>";
    echo "<title>Communications Relay</title>";
    echo "<body style='font-family:system-ui;background:#040805;color:#F5F7FA;display:grid;place-items:center;min-height:100vh;margin:0;text-align:center;padding:24px'>";
    echo "<div><p style='color:#22D3EE;font-family:monospace;letter-spacing:.2em;text-transform:uppercase'>Communications Relay</p>";
    echo "<h1 style='font-family:system-ui'>$safe</h1>";
    echo "<p><a href='/contact' style='color:#A78BFA'>← back to the relay</a></p></div></body>";
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    bail(405, 'Method not allowed. Use the form.');
}

// Honeypot — bots fill hidden fields; humans don't.
if (!empty($_POST['company'] ?? '')) {
    bail(200, 'Message received. Thanks.'); // silently accept-and-drop
}

$name    = trim((string)($_POST['name'] ?? ''));
$email   = trim((string)($_POST['email'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

if ($name === '' || $email === '' || $message === '') {
    bail(400, 'Please fill in every field.');
}
if (mb_strlen($name) > 120 || mb_strlen($email) > 180 || mb_strlen($message) > 4000) {
    bail(400, 'That message is a little too long.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    bail(400, 'That email address looks off.');
}

// Strip anything that could inject extra mail headers.
$clean = static fn (string $s): string => str_replace(["\r", "\n", "%0a", "%0d"], ' ', $s);
$name  = $clean($name);
$email = $clean($email);

$subject = "[$SITE] New message from $name";
$body    = "From: $name <$email>\n\n$message\n";
$headers = implode("\r\n", [
    'From: no-reply@' . $SITE,
    'Reply-To: ' . $email,
    'Content-Type: text/plain; charset=utf-8',
    'X-Mailer: cianomalley-portfolio-plesk',
]);

$sent = @mail($TO, $subject, $body, $headers);

if ($sent) {
    bail(200, 'Message transmitted. I\'ll be in touch.');
}
bail(500, 'The relay is offline right now — please email me directly.');
