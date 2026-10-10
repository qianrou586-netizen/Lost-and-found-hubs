<?php
// OTP (One-Time Password) ထုတ်ပေးခြင်းနှင့် စစ်ဆေးခြင်း

// OTP ကို database ထဲတွင် မူရင်းအတိုင်း မသိမ်းဘဲ hash လုပ်၍ သိမ်းသည်
function otp_hash(string $code): string
{
    return hash_hmac('sha256', $code, OTP_PEPPER);
}

// OTP အသစ်ထုတ်၍ Email ဖြင့် ပို့သည်
// ပြန်ပေးတန်ဖိုး: [အောင်မြင်မှု(bool), မက်ဆေ့ချ်(string)]
function otp_issue(PDO $pdo, int $userId, string $purpose, string $email, string $username): array
{
    // ၁။ ပြန်တောင်းခွင့် စောင့်ချိန် မကျော်သေးလျှင် ပယ်ချသည်
    $stmt = $pdo->prepare(
        'SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) AS age
         FROM otp_codes WHERE user_id = ? AND purpose = ? ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute([$userId, $purpose]);
    $last = $stmt->fetch();
    if ($last && (int) $last['age'] < OTP_RESEND_SECONDS) {
        $wait = OTP_RESEND_SECONDS - (int) $last['age'];
        return [false, "Please wait {$wait} seconds before requesting a new code."];
    }

    // ၂။ အရင် OTP အဟောင်းများကို အသုံးမပြုနိုင်အောင် ပိတ်သည်
    $pdo->prepare('UPDATE otp_codes SET used_at = NOW() WHERE user_id = ? AND purpose = ? AND used_at IS NULL')
        ->execute([$userId, $purpose]);

    // ၃။ ဂဏန်း ၆ လုံး ကျပန်းထုတ်သည် (random_int သည် လုံခြုံသော random ဖြစ်သည်)
    $code = str_pad((string) random_int(0, (10 ** OTP_LENGTH) - 1), OTP_LENGTH, '0', STR_PAD_LEFT);

    // ၄။ Hash လုပ်ပြီး သက်တမ်းနှင့်တကွ သိမ်းသည်
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $pdo->prepare(
        'INSERT INTO otp_codes (user_id, purpose, code_hash, expires_at, ip_address)
         VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), ?)'
    )->execute([$userId, $purpose, otp_hash($code), OTP_TTL_SECONDS, $ip]);
    $otpId = (int) $pdo->lastInsertId();

    // ၅။ Email ပို့သည်
    $minutes = (int) (OTP_TTL_SECONDS / 60);
    $body = "Hello {$username},\n\n"
          . "Your verification code is: {$code}\n\n"
          . "This code expires in {$minutes} minutes. If you did not try to sign in, please ignore this email.\n\n"
          . "- Lost & Found Hub";

    if (!send_mail($email, 'Your Lost & Found Hub verification code', $body)) {
        // ပို့မရလျှင် သိမ်းထားသော OTP ကို ဖျက်သည်
        $pdo->prepare('DELETE FROM otp_codes WHERE id = ?')->execute([$otpId]);
        return [false, 'We could not send the email. Please try again later.'];
    }
    return [true, 'A verification code has been sent to your email.'];
}

// ထည့်သွင်းလာသော OTP မှန်မမှန် စစ်သည်
// ပြန်ပေးတန်ဖိုး: [မှန်မှု(bool), မက်ဆေ့ချ်(string)]
function otp_verify(PDO $pdo, int $userId, string $purpose, string $code): array
{
    // ဂဏန်းသာ ဖြစ်ရမည်
    if (!preg_match('/^\d{' . OTP_LENGTH . '}$/', $code)) {
        return [false, 'Please enter the ' . OTP_LENGTH . '-digit code.'];
    }

    // အသုံးမပြုရသေးသော နောက်ဆုံး OTP ကို ယူသည် (သက်တမ်းကုန်မကုန်ကိုပါ database အချိန်ဖြင့် စစ်သည်)
    $stmt = $pdo->prepare(
        'SELECT id, code_hash, attempts, (expires_at < NOW()) AS expired
         FROM otp_codes WHERE user_id = ? AND purpose = ? AND used_at IS NULL
         ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute([$userId, $purpose]);
    $row = $stmt->fetch();

    if (!$row) {
        return [false, 'No active code. Please request a new one.'];
    }
    if ((int) $row['expired'] === 1) {
        return [false, 'This code has expired. Please request a new one.'];
    }
    if ((int) $row['attempts'] >= OTP_MAX_ATTEMPTS) {
        return [false, 'Too many wrong attempts. Please request a new code.'];
    }

    // hash_equals ဖြင့် timing attack ကို ကာကွယ်၍ နှိုင်းယှဉ်သည်
    if (!hash_equals($row['code_hash'], otp_hash($code))) {
        $pdo->prepare('UPDATE otp_codes SET attempts = attempts + 1 WHERE id = ?')->execute([$row['id']]);
        return [false, 'Incorrect code. Please try again.'];
    }

    // မှန်ကန်လျှင် ထပ်မသုံးနိုင်အောင် အသုံးပြုပြီးဟု မှတ်သည်
    $pdo->prepare('UPDATE otp_codes SET used_at = NOW() WHERE id = ?')->execute([$row['id']]);
    return [true, 'Verified.'];
}
