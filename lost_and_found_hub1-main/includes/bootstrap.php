<?php
// စာမျက်နှာတိုင်း၏ အစတွင် ထည့်သွင်းရမည့် အခြေခံဖိုင်
// Session စတင်ခြင်း၊ Database ချိတ်ဆက်ခြင်းနှင့် helper function များ ပါဝင်သည်

if (session_status() === PHP_SESSION_NONE) {
    // Cookie ကို JavaScript မှ မဖတ်နိုင်အောင်နှင့် HTTPS ရှိလျှင် secure ဖြစ်အောင် သတ်မှတ်သည်
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

require_once __DIR__ . '/../config/database.php'; // $pdo ကို ဤနေရာမှ ရရှိသည်
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/otp.php';
require_once __DIR__ . '/auth_flow.php';
require_once __DIR__ . '/profile_lib.php';
require_once __DIR__ . '/ui.php';

// HTML ထဲသို့ ထုတ်ပြသည့်အခါ XSS မဖြစ်စေရန် escape လုပ်သည် (Array to string conversion safe ဖြစ်အောင် စစ်ထားသည်)
function h($value): string
{
    if (is_array($value)) {
        return htmlspecialchars(implode(', ', $value), ENT_QUOTES, 'UTF-8');
    }
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// အခြားစာမျက်နှာသို့ ပို့ဆောင်ပြီး script ကို ရပ်သည်
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

// ----- CSRF ကာကွယ်မှု -----
// Form တိုင်းအတွက် token ထုတ်ပေးသည်
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

// POST လာသော token မှန်မမှန် စစ်သည်
function csrf_valid(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string) $_POST['csrf']);
}

// ----- Flash message (တစ်ကြိမ်သာ ပြသောစာ) -----
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Flash message ကို ပြန်ထုတ်ပေးသည်
 * flash_get('success') လို့ ခေါ်လျှင် string သီးသန့် ပြန်ပေးမည်
 * flash_get() လို့ parameter မပါဘဲ ခေါ်လျှင် array သို့မဟုတ် message string ပြန်ပေးမည်
 */
function flash_get(?string $specificType = null)
{
    $flash = $_SESSION['flash'] ?? null;
    
    if (!$flash) {
        return null;
    }

    // တိကျသော type (ဥပမာ 'success') တောင်းထားလျှင်
    if ($specificType !== null) {
        if (is_array($flash) && ($flash['type'] ?? '') === $specificType) {
            unset($_SESSION['flash']);
            return $flash['message'] ?? '';
        }
        return null;
    }

    // Default အနေဖြင့် session ဖျက်ပြီး flash data ကို ပြန်ပေးသည်
    unset($_SESSION['flash']);
    return $flash;
}

// ----- Login အခြေအနေ စစ်ခြင်း -----
// လက်ရှိ login ဝင်ထားသူ၏ role ('user' / 'admin') ကို ပြန်ပေးသည်၊ မဝင်ထားလျှင် null
function current_role(): ?string
{
    return isset($_SESSION['user_id'], $_SESSION['role']) ? $_SESSION['role'] : null;
}

// သတ်မှတ်ထားသော role မဟုတ်လျှင် login စာမျက်နှာသို့ ပြန်ပို့သည်
function require_role(string $role, string $loginUrl): void
{
    if (current_role() !== $role) {
        redirect($loginUrl);
    }
}

// Session အားလုံးကို ရှင်းလင်း၍ logout လုပ်သည်
function destroy_session(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// Logout စာမျက်နှာများအတွက် (POST + CSRF သာ လက်ခံသည်)
function handle_logout(string $redirectTo): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid()) {
        destroy_session();
    }
    redirect($redirectTo);
}