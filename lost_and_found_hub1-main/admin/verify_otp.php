<?php
// Admin OTP အတည်ပြုစာမျက်နှာ - Login OTP စစ်ဆေးခြင်း
require_once __DIR__ . '/../includes/bootstrap.php';

// login.php မှ သတ်မှတ်ပေးလိုက်သော session နှစ်မျိုးစလုံးကို စစ်ဆေးပေးခြင်း
$pending = $_SESSION['pending_admin_otp'] ?? $_SESSION['pending_admin_login'] ?? null;

if (!$pending) {
    flash_set('error', 'Please sign in first.');
    redirect('login.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif (($_POST['action'] ?? '') === 'resend') {
        // OTP အသစ် ထုတ်ယူ၍ ပြန်ပို့ခြင်း
        $newOtp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $newHash = password_hash($newOtp, PASSWORD_DEFAULT);
        $newExp = time() + (5 * 60);

        if (isset($_SESSION['pending_admin_otp'])) {
            $_SESSION['pending_admin_otp']['otp_hash'] = $newHash;
            $_SESSION['pending_admin_otp']['expires_at'] = $newExp;
        }
        if (isset($_SESSION['pending_admin_login'])) {
            $_SESSION['pending_admin_login']['otp_hash'] = $newHash;
            $_SESSION['pending_admin_login']['expires_at'] = $newExp;
        }

        $subject = 'New Admin Login Verification Code - Lost & Found Hub';
        $body = "Hello {$pending['username']},\n\nYour new verification code is: {$newOtp}\n\nExpires in 5 minutes.";

        if (function_exists('send_mail')) {
            send_mail($pending['email'], $subject, $body);
        }

        flash_set('success', 'A new verification code has been sent to your email.');
        redirect('verify_otp.php');
    } else {
        $inputCode = trim($_POST['code'] ?? '');

        if (time() > (int) $pending['expires_at']) {
            $errors[] = 'Verification code has expired. Please request a new one.';
        } elseif (!password_verify($inputCode, $pending['otp_hash'])) {
            $errors[] = 'Invalid verification code. Please check and try again.';
        } else {
            // OTP မှန်ကန်ပါက Admin Session အပြည့်အဝ ပေးအပ်ခြင်း
            $_SESSION['admin_id'] = $pending['admin_id'];
            $_SESSION['admin_username'] = $pending['username'];
            $_SESSION['admin_email'] = $pending['email'];

            // admins table ရှိ last_login_at ကို update လုပ်ခြင်း
            try {
                $pdo->prepare("UPDATE admins SET last_login_at = NOW() WHERE id = ?")->execute([$pending['admin_id']]);
            } catch (Exception $e) {}

            // ယာယီ pending session များကို ဖျက်ပစ်ခြင်း
            unset($_SESSION['pending_admin_otp'], $_SESSION['pending_admin_login']);

            flash_set('success', 'Welcome to Admin Dashboard, ' . $pending['username']);
            redirect('dashboard.php');
        }
    }
}

ui_header('Admin Verification');
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .bg-theme-green { background-color: #1b4332 !important; }
    .bg-theme-green:hover { background-color: #143527 !important; }
    .text-theme-green { color: #1b4332 !important; }
    .focus-theme-green:focus {
        border-color: #1b4332 !important;
        box-shadow: 0 0 0 2px rgba(27, 67, 50, 0.15) !important;
    }
</style>

<div class="max-w-md mx-auto py-16 px-4">
    <div class="bg-white p-8 sm:p-9 rounded-2xl shadow-xl border border-gray-100 text-center">
        
        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-theme-green flex items-center justify-center mx-auto mb-5 border border-emerald-100">
            <i class="fa-solid fa-shield-check text-2xl"></i>
        </div>

        <h1 class="font-serif text-2xl font-bold mb-2 text-gray-900">Admin Verification</h1>
        <p class="text-sm text-gray-600 mb-6 leading-relaxed">
            Enter the 6-digit code sent to <br>
            <span class="font-semibold text-gray-900"><?= h(mask_email($pending['email'])) ?></span>.
            <br><span class="text-xs text-gray-400">Expires in 5 minutes.</span>
        </p>

        <?php if ($flash = flash_get()): ?>
            <?php 
                $fMsg = is_array($flash) ? ($flash['message'] ?? '') : $flash;
                $fType = is_array($flash) ? ($flash['type'] ?? 'success') : 'success';
            ?>
            <div class="mb-5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs text-left">
                <?= htmlspecialchars($fMsg) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs sm:text-sm space-y-1.5 text-left">
                <?php foreach ($errors as $error): ?>
                    <p class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4" autocomplete="off">
            <?= csrf_field() ?>
            <div>
                <input 
                    type="text" 
                    name="code" 
                    required 
                    inputmode="numeric" 
                    pattern="\d{6}"
                    maxlength="6" 
                    placeholder="······"
                    class="w-full text-center text-2xl font-mono tracking-[0.4em] py-3 rounded-xl border border-gray-200 bg-gray-50/50 text-gray-900 focus:bg-white focus:outline-none transition-all duration-200 focus-theme-green" 
                    autofocus
                >
            </div>

            <button 
                type="submit" 
                class="w-full py-3 px-4 rounded-xl text-white font-medium text-sm transition shadow bg-theme-green flex items-center justify-center gap-2"
            >
                <span>Verify &amp; Enter Dashboard</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </form>

        <div class="flex items-center justify-between mt-6 pt-5 border-t border-gray-100 text-sm">
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="resend">
                <button type="submit" class="text-theme-green font-semibold hover:underline">
                    Resend code
                </button>
            </form>
            <a href="login.php" class="text-gray-500 hover:text-gray-800">
                Back to sign in
            </a>
        </div>

    </div>
</div>

<?php ui_footer(); ?>