<?php
// user/login.php
// User Login စာမျက်နှာ (All-Dark Midnight Theme)
require_once __DIR__ . '/../includes/bootstrap.php';

if (current_role() === 'user') {
    redirect('dashboard.php');
}

$errors = [];
$loginValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginValue = trim($_POST['login'] ?? '');

    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        [$ok, $message] = auth_start_login($pdo, 'user', $loginValue, $_POST['password'] ?? '');
        if ($ok) {
            flash_set('success', $message);
            redirect('verify_otp.php');
        }
        $errors[] = $message;
    }
}

ui_header('User Sign In');
?>

<div class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8 min-h-[75vh] flex items-center">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-16 items-center w-full">
        
        <!-- Left Column: About Platform -->
        <div class="md:col-span-7 space-y-7">
            <div>
                <span class="inline-block px-3.5 py-1.5 bg-blue-950/80 text-blue-400 text-xs font-bold rounded-full uppercase tracking-wider mb-4 border border-blue-800/50">
                    Community Platform
                </span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-bold text-white tracking-tight leading-tight">
                    Lost something?<br>
                    <span class="text-blue-400">Let’s help you find it.</span>
                </h1>
            </div>

            <p class="text-base text-slate-300 leading-relaxed max-w-lg">
                Lost &amp; Found Hub connects people who have misplaced their belongings with honest finders. Report lost items, search the inventory, and reclaim what matters to you.
            </p>

            <div class="space-y-4 pt-2">
                <div class="flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-blue-950/80 text-blue-400 flex items-center justify-center shrink-0 border border-blue-800/40">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white text-sm">Smart Matching</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Automated matching algorithms connect reported lost items with found reports.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-blue-950/80 text-blue-400 flex items-center justify-center shrink-0 border border-blue-800/40">
                        <i class="fa-solid fa-shield-halved text-sm"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white text-sm">Secure Verification</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Email OTP authentication ensures genuine and safe claim ownership.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-blue-950/80 text-blue-400 flex items-center justify-center shrink-0 border border-blue-800/40">
                        <i class="fa-solid fa-bullhorn text-sm"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white text-sm">Instant Notifications</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Get notified immediately when someone submits a match for your item.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Login Card -->
        <div class="md:col-span-5 w-full max-w-md mx-auto">
            <div class="bg-[#101d42] p-8 sm:p-9 rounded-2xl shadow-2xl border border-blue-900/40">
                <h2 class="font-serif text-2xl font-bold mb-1.5 text-white">Sign in</h2>
                <p class="text-sm text-slate-400 mb-6 leading-relaxed">
                    Enter your details. We will email you a verification code.
                </p>

                <?php if (!empty($errors)): ?>
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-950/60 border border-rose-800/50 text-rose-300 text-xs sm:text-sm space-y-1.5">
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
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Username or email</label>
                        <input 
                            type="text" 
                            name="login" 
                            required 
                            placeholder="Enter your username or email" 
                            class="form-input-pill w-full text-sm" 
                            value="<?= h($loginValue) ?>"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Password</label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="login_password" 
                                name="password" 
                                required 
                                placeholder="Enter your password" 
                                class="form-input-pill w-full text-sm pr-11"
                            >
                            <button 
                                type="button" 
                                onclick="togglePassword('login_password', 'loginEyeIcon')" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white focus:outline-none"
                            >
                                <i id="loginEyeIcon" class="fa-solid fa-eye-slash text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="w-full py-3 px-4 rounded-xl text-white font-semibold text-sm transition-all duration-200 shadow-lg shadow-blue-950/50 bg-blue-600 hover:bg-blue-700 flex items-center justify-center gap-2"
                        >
                            <span>Continue</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </form>

                <div class="mt-7 pt-5 border-t border-blue-900/40 text-center">
                    <p class="text-sm text-slate-400">
                        New here? <a href="register.php" class="text-blue-400 font-semibold hover:underline">Create an account</a>
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
}
</script>

<?php ui_footer(); ?>