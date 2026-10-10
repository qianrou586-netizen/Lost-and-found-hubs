<?php
// user/verify_otp.php
// User OTP အတည်ပြုစာမျက်နှာ - အဆင့် ၂: Email သို့ ရောက်လာသော ဂဏန်း ၆ လုံးကို စစ်ဆေးသည် (All-Dark Theme)
require_once __DIR__ . '/../includes/bootstrap.php';

// Login မစရသေးပါက login.php သို့ ပြန်လွှဲပေးခြင်း
$pending = auth_pending('user');
if (!$pending) {
    flash_set('error', 'Please sign in first.');
    redirect('login.php');
}

$errors = [];

// OTP Submit ပြုလုပ်သည့်အခါ စစ်ဆေးခြင်း
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif (($_POST['action'] ?? '') === 'resend') {
        // OTP အသစ် ထပ်မံပေးပို့ခြင်း
        [$ok, $message] = auth_resend($pdo, 'user');
        if ($ok) {
            flash_set('success', $message);
            redirect('verify_otp.php');
        }
        $errors[] = $message;
    } else {
        // ထည့်သွင်းထားသော OTP ဂဏန်း ၆ လုံးကို စစ်ဆေးအတည်ပြုခြင်း
        [$ok, $message] = auth_finish_login($pdo, 'user', (string) ($_POST['code'] ?? ''));
        if ($ok) {
            // Dashboard သို့ မသွားဘဲ Browse Items (items.php) သို့ တိုက်ရိုက်ရောက်ရှိစေခြင်း
            redirect('items.php');
        }
        $errors[] = $message;
        if (!auth_pending('user')) {
            flash_set('error', $message);
            redirect('login.php');
        }
    }
}

ui_header('Verify Code');
?>

<div class="max-w-md mx-auto py-16 px-4">
    <!-- Dark Surface Container Box -->
    <div class="bg-[#101d42] p-8 sm:p-9 rounded-2xl shadow-2xl border border-blue-900/40 text-center">
        
        <!-- Header Email Icon (သေချာပေါက် ပေါ်စေရန် Standard Envelope Icon ထည့်သွင်းထားသည်) -->
        <div class="w-14 h-14 rounded-2xl bg-blue-950/80 border border-blue-800/40 text-blue-400 flex items-center justify-center mx-auto mb-5 shadow-inner">
            <i class="fa-solid fa-envelope-open-text text-2xl"></i>
        </div>

        <h1 class="font-serif text-2xl font-bold mb-2 text-white">Enter verification code</h1>
        <p class="text-sm text-slate-400 mb-6 leading-relaxed">
            We sent a <?= (int) OTP_LENGTH ?>-digit code to <br>
            <span class="font-semibold text-white"><?= h(mask_email($pending['email'])) ?></span>. 
            <br><span class="text-xs text-slate-500">Expires in <?= (int) (OTP_TTL_SECONDS / 60) ?> minutes.</span>
        </p>

        <!-- Error သတိပေးချက်များ -->
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

        <!-- OTP Input Form -->
        <form method="post" class="space-y-4" autocomplete="off">
            <?= csrf_field() ?>
            <div>
                <input 
                    type="text" 
                    name="code" 
                    required 
                    inputmode="numeric" 
                    pattern="\d{<?= (int) OTP_LENGTH ?>}"
                    maxlength="<?= (int) OTP_LENGTH ?>" 
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
            <a href="login.php" class="text-slate-400 hover:text-white text-xs sm:text-sm">
                Back to sign in
            </a>
        </div>

    </div>
</div>

<?php ui_footer(); ?>