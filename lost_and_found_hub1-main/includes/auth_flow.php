<?php
// Login စီးဆင်းမှု (Password စစ်ခြင်း -> OTP ပို့ခြင်း -> OTP စစ်ခြင်း -> Login ဝင်ခြင်း)
// User နှင့် Admin နှစ်ဦးစလုံး ဤ function များကို မျှသုံးသည်၊ သို့သော် $role ဖြင့် ခွဲခြားထားသည်

// Role အလိုက် OTP ၏ ရည်ရွယ်ချက် (purpose) ကို ပြန်ပေးသည်
function auth_purpose(string $role): string
{
    return $role === 'admin' ? 'admin_login' : 'user_login';
}

// Email ကို တစ်စိတ်တစ်ပိုင်း ဖုံးကွယ်၍ ပြသသည် (ဥပမာ: a***@gmail.com)
function mask_email(string $email): string
{
    [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');
    return mb_substr($name, 0, 1) . '***@' . $domain;
}

// အဆင့် ၁: Username/Email နှင့် Password မှန်လျှင် OTP ပို့၍ "စောင့်ဆိုင်းနေသော login" အဖြစ် မှတ်သည်
// ပြန်ပေးတန်ဖိုး: [အောင်မြင်မှု(bool), မက်ဆေ့ချ်(string)]
function auth_start_login(PDO $pdo, string $role, string $login, string $password): array
{
    $login = trim($login);
    if ($login === '' || $password === '') {
        return [false, 'Please enter your username or email and your password.'];
    }

    // Role ပေါ်မူတည်၍ admins သို့မဟုတ် users table မှ သီးခြားစီ ရှာဖွေစစ်ဆေးသည်
    if ($role === 'admin') {
        $stmt = $pdo->prepare(
            'SELECT id, username, email, password_hash, is_active 
             FROM admins WHERE (username = ? OR email = ?) LIMIT 1'
        );
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return [false, 'Invalid credentials.'];
        }
        if (isset($user['is_active']) && (int) $user['is_active'] === 0) {
            return [false, 'This admin account is inactive.'];
        }
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, username, email, password_hash, is_suspended 
             FROM users WHERE (username = ? OR email = ?) LIMIT 1'
        );
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return [false, 'Invalid credentials.'];
        }
        if ((int) $user['is_suspended'] === 1) {
            return [false, 'This account has been suspended.'];
        }
    }

    // OTP ထုတ်၍ Email ပို့သည်
    [$sent, $message] = otp_issue($pdo, (int) $user['id'], auth_purpose($role), $user['email'], $user['username']);
    if (!$sent) {
        return [false, $message];
    }

    // OTP အတည်ပြုပြီးမှ တကယ် login ဝင်ရမည်ဖြစ်၍ ယခုအချိန်တွင် user_id ကို မသိမ်းသေးဘဲ pending အဖြစ်သာ မှတ်သည်
    session_regenerate_id(true);
    $_SESSION['pending_auth'] = [
        'user_id' => (int) $user['id'],
        'role'    => $role,
        'email'   => $user['email'],
        'name'    => $user['username'],
        'started' => time(),
    ];
    return [true, $message];
}

// OTP စာမျက်နှာအတွက် စောင့်ဆိုင်းနေသော login ရှိမရှိ စစ်သည် (role မကိုက်ညီလျှင်/အချိန်ကျော်လျှင် null)
function auth_pending(string $role): ?array
{
    $p = $_SESSION['pending_auth'] ?? null;
    if (!$p || $p['role'] !== $role || (time() - $p['started']) > OTP_PENDING_SECONDS) {
        unset($_SESSION['pending_auth']);
        return null;
    }
    return $p;
}

// OTP ကို ပြန်ပို့သည်
function auth_resend(PDO $pdo, string $role): array
{
    $p = auth_pending($role);
    if (!$p) {
        return [false, 'Your session has expired. Please sign in again.'];
    }
    return otp_issue($pdo, $p['user_id'], auth_purpose($role), $p['email'], $p['name']);
}

// အဆင့် ၂: OTP မှန်လျှင် login အပြည့်အဝ ဝင်ခွင့်ပေးသည်
function auth_finish_login(PDO $pdo, string $role, string $code): array
{
    $p = auth_pending($role);
    if (!$p) {
        return [false, 'Your session has expired. Please sign in again.'];
    }

    [$ok, $message] = otp_verify($pdo, $p['user_id'], auth_purpose($role), trim($code));
    if (!$ok) {
        return [false, $message];
    }

    // Session fixation ကာကွယ်ရန် session id အသစ်ပြောင်းပြီးမှ login အချက်အလက် သိမ်းသည်
    session_regenerate_id(true);
    unset($_SESSION['pending_auth'], $_SESSION['csrf']);
    $_SESSION['user_id']  = $p['user_id'];
    $_SESSION['username'] = $p['name'];
    $_SESSION['role']     = $role;

    // Table မတူသဖြင့် UPDATE လုပ်သည့် table အမည်ပါ $role ပေါ်မူတည်၍ ခွဲခြားသည်
    $table = ($role === 'admin') ? 'admins' : 'users';
    $pdo->prepare("UPDATE {$table} SET last_login_at = NOW() WHERE id = ?")->execute([$p['user_id']]);
    return [true, 'Signed in.'];
}

// စကားဝှက်ပြောင်းသည် (User နှင့် Admin နှစ်မျိုးလုံး သုံးသည်)
function auth_change_password(PDO $pdo, int $userId, string $role, string $current, string $new, string $confirm): array
{
    if ($new !== $confirm) {
        return [false, 'The new passwords do not match.'];
    }
    if (strlen($new) < 8) {
        return [false, 'The new password must be at least 8 characters.'];
    }

    $table = ($role === 'admin') ? 'admins' : 'users';
    $stmt = $pdo->prepare("SELECT password_hash FROM {$table} WHERE id = ?");
    $stmt->execute([$userId]);
    $hash = $stmt->fetchColumn();

    if (!$hash || !password_verify($current, $hash)) {
        return [false, 'Your current password is incorrect.'];
    }
    if (password_verify($new, $hash)) {
        return [false, 'The new password must be different from the current one.'];
    }

    $pdo->prepare("UPDATE {$table} SET password_hash = ? WHERE id = ?")
        ->execute([password_hash($new, PASSWORD_DEFAULT), $userId]);

    session_regenerate_id(true);
    return [true, 'Your password has been changed.'];
}