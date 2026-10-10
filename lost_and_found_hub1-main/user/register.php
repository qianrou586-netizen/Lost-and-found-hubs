<?php
// user/register.php
// User စာရင်းသွင်းစာမျက်နှာ - Username, Email, Phone နှင့် Password စစ်ပြီး OTP ပို့ပေးသည် (All-Dark Theme)
require_once __DIR__ . '/../includes/bootstrap.php';

if (current_role() === 'user') {
    redirect('dashboard.php');
}

$errors = [];
$old = ['username' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['username'] = trim($_POST['username'] ?? '');
    $old['email']    = trim($_POST['email'] ?? '');
    $old['phone']    = trim($_POST['phone'] ?? '');
    $password        = (string) ($_POST['password'] ?? '');
    $confirm         = (string) ($_POST['confirm_password'] ?? '');

    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        // Validation စစ်ဆေးခြင်း
        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $old['username'])) {
            $errors[] = 'Username must be 3-50 characters (letters, numbers, underscores).';
        }
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['email']) > 100) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (!preg_match('/^[0-9+]{7,15}$/', $old['phone'])) {
            $errors[] = 'Please enter a valid phone number (e.g., 09425867189).';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'The passwords do not match.';
        }

        // Username သို့မဟုတ် Email ထပ်နေခြင်း ရှိမရှိ စစ်ဆေးခြင်း
        if (!$errors) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
            $stmt->execute([$old['username'], $old['email']]);
            if ($stmt->fetch()) {
                $errors[] = 'Username or email already exists.';
            }
        }

        // အမှားမရှိပါက OTP code ထုတ်ယူပြီး Email သို့ ပို့ခြင်း
        if (!$errors) {
            $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otpHash = password_hash($otpCode, PASSWORD_DEFAULT);
            $expiresAt = time() + (5 * 60);

            $_SESSION['pending_registration'] = [
                'username'      => $old['username'],
                'email'         => $old['email'],
                'phone'         => $old['phone'],
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'otp_hash'      => $otpHash,
                'expires_at'    => $expiresAt
            ];

            $subject = 'Your Verification Code - Lost & Found Hub';
            $body = "Hello {$old['username']},\n\nYour registration verification code is: {$otpCode}\n\nThis code will expire in 5 minutes.";
            
            if (function_exists('send_mail')) {
                send_mail($old['email'], $subject, $body);
            }

            flash_set('info', 'We sent a verification code to ' . $old['email']);
            redirect('verify_register_otp.php');
        }
    }
}

ui_header('Create Account');
?>

<div class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8 min-h-[75vh] flex items-center">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-16 items-center w-full">
        
        <!-- Left Column: Community Details -->
        <div class="md:col-span-7 space-y-7">
            <div>
                <span class="inline-block px-3.5 py-1.5 bg-blue-950/80 text-blue-400 text-xs font-bold rounded-full uppercase tracking-wider mb-4 border border-blue-800/50">
                    Join Our Community
                </span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-bold text-white tracking-tight leading-tight">
                    Never lose track of<br>
                    <span class="text-blue-400">what’s yours again.</span>
                </h1>
            </div>

            <p class="text-base text-slate-300 leading-relaxed max-w-lg">
                Create an account to report missing valuables, connect with honest campus finders, and get real-time email alerts whenever items match your claims.
            </p>

            <div class="space-y-4 pt-2">
                <div class="flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-blue-950/80 text-blue-400 flex items-center justify-center shrink-0 border border-blue-800/40">
                        <i class="fa-solid fa-user-shield text-sm"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white text-sm">Verified Ownership</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Protect claim rights with single-user verification codes sent to your inbox.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-blue-950/80 text-blue-400 flex items-center justify-center shrink-0 border border-blue-800/40">
                        <i class="fa-solid fa-bell text-sm"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white text-sm">Direct Alerts</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Receive prompt notifications whenever items matching your description are turned in.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Registration Card -->
        <div class="md:col-span-5 w-full max-w-md mx-auto">
            <div class="bg-[#101d42] p-8 sm:p-9 rounded-2xl shadow-2xl border border-blue-900/40">
                <h2 class="font-serif text-2xl font-bold mb-1.5 text-white">Create Account</h2>
                <p class="text-sm text-slate-400 mb-6 leading-relaxed">
                    Use a real email address. A verification code will be sent there.
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

                <form method="post" class="space-y-3.5" autocomplete="off">
                    <?= csrf_field() ?>
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Username</label>
                        <input 
                            type="text" 
                            name="username" 
                            required 
                            minlength="3" 
                            maxlength="50" 
                            placeholder="Choose a username" 
                            class="form-input-pill w-full text-sm" 
                            value="<?= h($old['username']) ?>"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Email address</label>
                        <input 
                            type="email" 
                            name="email" 
                            required 
                            maxlength="100" 
                            placeholder="name@example.com" 
                            class="form-input-pill w-full text-sm" 
                            value="<?= h($old['email']) ?>"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Phone Number (Myanmar)</label>
                        <input 
                            type="text" 
                            name="phone" 
                            required 
                            maxlength="15" 
                            placeholder="09..." 
                            class="form-input-pill w-full text-sm" 
                            value="<?= h($old['phone']) ?>"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Password</label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                minlength="8" 
                                placeholder="At least 8 characters" 
                                class="form-input-pill w-full text-sm pr-11"
                            >
                            <button 
                                type="button" 
                                onclick="togglePassword('password', 'eyeIcon1')" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white focus:outline-none"
                            >
                                <i id="eyeIcon1" class="fa-solid fa-eye-slash text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Confirm password</label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                required 
                                minlength="8" 
                                placeholder="Re-enter password" 
                                class="form-input-pill w-full text-sm pr-11"
                            >
                            <button 
                                type="button" 
                                onclick="togglePassword('confirm_password', 'eyeIcon2')" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white focus:outline-none"
                            >
                                <i id="eyeIcon2" class="fa-solid fa-eye-slash text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="w-full py-3 px-4 rounded-xl text-white font-semibold text-sm transition-all duration-200 shadow-lg shadow-blue-950/50 bg-blue-600 hover:bg-blue-700 flex items-center justify-center gap-2"
                        >
                            <span>Send Verification Code</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </form>

                <div class="mt-6 pt-4 border-t border-blue-900/40 text-center">
                    <p class="text-sm text-slate-400">
                        Already registered? <a href="login.php" class="text-blue-400 font-semibold hover:underline">Sign in</a>
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