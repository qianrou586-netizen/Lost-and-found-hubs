<?php
// user/verify_register_otp.php
// User Registration OTP အတည်ပြုစာမျက်နှာ (All-Dark Theme)
require_once __DIR__ . '/../includes/bootstrap.php';

$pending = $_SESSION['pending_registration'] ?? null;
if (!$pending) {
    flash_set('error', 'Please fill out the registration form first.');
    redirect('register.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif (($_POST['action'] ?? '') === 'resend') {
        $newOtp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['pending_registration']['otp_hash'] = password_hash($newOtp, PASSWORD_DEFAULT);
        $_SESSION['pending_registration']['expires_at'] = time() + (5 * 60);

        $subject = 'Your New Verification Code - Lost & Found Hub';
        $body = "Hello {$pending['username']},\n\nYour new verification code is: {$newOtp}\n\nExpires in 5 minutes.";
        
        if (function_exists('send_mail')) {
            send_mail($pending['email'], $subject, $body);
        }

        flash_set('success', 'A new verification code has been sent to your email.');
        redirect('verify_register_otp.php');
    } else {
        $inputCode = trim($_POST['code'] ?? '');

        if (time() > (int) $pending['expires_at']) {
            $errors[] = 'Verification code has expired. Please request a new one.';
        } elseif (!password_verify($inputCode, $pending['otp_hash'])) {
            $errors[] = 'Invalid verification code. Please check and try again.';
        } else {
            // OTP မှန်ကန်ပါက Database ထဲသို့ အကောင့်သိမ်းဆည်းခြင်း
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                $stmt->execute([$pending['username'], $pending['email'], $pending['password_hash']]);
                $newUserId = (int) $pdo->lastInsertId();

                // user_profiles table ထဲသို့ username အား full_name အဖြစ်နှင့် ဖုန်းနံပါတ်အား ထည့်သွင်းခြင်း
                $profStmt = $pdo->prepare("
                    INSERT INTO user_profiles (user_id, full_name, phone, updated_at) 
                    VALUES (?, ?, ?, NOW())
                ");
                $profStmt->execute([$newUserId, $pending['username'], $pending['phone'] ?? null]);

                unset($_SESSION['pending_registration']);

                // အကောင့်ကို login တန်းဝင်စေပြီး items.php သို့ ချက်ချင်းပို့ဆောင်ပေးခြင်း
                $_SESSION['user_id'] = $newUserId;
                $_SESSION['role'] = 'user';
                $_SESSION['username'] = $pending['username'];

                flash_set('success', 'Account verified and created successfully! Welcome to Lost & Found Hub.');
                redirect('items.php');
            } catch (Exception $e) {
                $errors[] = 'An error occurred while creating your account. Please try again.';
            }
        }
    }
}

ui_header('Verify Account');
?>

<div class="max-w-md mx-auto py-16 px-4">
    <div class="bg-[#101d42] p-8 sm:p-9 rounded-2xl shadow-2xl border border-blue-900/40 text-center">
        
        <!-- Header Envelope Icon -->
        <div class="w-14 h-14 rounded-2xl bg-blue-950/80 border border-blue-800/40 text-blue-400 flex items-center justify-center mx-auto mb-5 shadow-inner">
            <i class="fa-solid fa-envelope-open-text text-2xl"></i>
        </div>

        <h1 class="font-serif text-2xl font-bold mb-2 text-white">Verify Your Email</h1>
        <p class="text-sm text-slate-400 mb-6 leading-relaxed">
            We sent a 6-digit code to <br>
            <span class="font-semibold text-white"><?= h(mask_email($pending['email'])) ?></span>.
            <br><span class="text-xs text-slate-500">Expires in 5 minutes.</span>
        </p>

        <?php if ($flash = flash_get('success')): ?>
            <div class="mb-5 p-3 rounded-xl bg-emerald-950/60 border border-emerald-800/50 text-emerald-300 text-xs text-left">
                <?= htmlspecialchars($flash) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-rose-950/60 border border-rose-800/50 text-rose-300 text-xs sm:text-sm space-y-1.5 text-left">
                <?php foreach ($errors as $error): ?>
                    <p class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation shrink-0 text-rose-400"></i>
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
                    class="form-input-pill w-full text-center text-2xl font-mono tracking-[0.4em] py-3 text-white" 
                    autofocus
                >
            </div>

            <button 
                type="submit" 
                class="w-full py-3 px-4 rounded-xl text-white font-semibold text-sm transition-all duration-200 shadow-lg shadow-blue-950/50 bg-blue-600 hover:bg-blue-700 flex items-center justify-center gap-2"
            >
                <span>Verify &amp; Browse Items</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </form>

        <div class="flex items-center justify-between mt-6 pt-5 border-t border-blue-900/40 text-sm">
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="resend">
                <button type="submit" class="text-blue-400 font-semibold hover:underline text-xs sm:text-sm">
                    Resend code
                </button>
            </form>
            <a href="register.php" class="text-slate-400 hover:text-white text-xs sm:text-sm">
                Change details
            </a>
        </div>

    </div>
</div>

<?php ui_footer(); ?>