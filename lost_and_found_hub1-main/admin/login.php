<?php
// Admin Login - Auto-Create & Auto-Sync Version
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/lang.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

$errors = [];
$loginValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginValue = trim($_POST['login'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    } else {
        // admins table ထဲတွင် username သို့မဟုတ် email စစ်ဆေးခြင်း
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE (username = ? OR email = ?) LIMIT 1");
        $stmt->execute([$loginValue, $loginValue]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        $newHash = password_hash($password, PASSWORD_DEFAULT);

        // အကယ်၍ admins table ထဲတွင် အကောင့်မရှိသေးပါက အလိုအလျောက် အကောင့်သစ် ဆောက်ပေးခြင်း (Auto-Create)
        if (!$admin) {
            $isEmail = filter_var($loginValue, FILTER_VALIDATE_EMAIL);
            $username = $isEmail ? explode('@', $loginValue)[0] : $loginValue;
            $email = $isEmail ? $loginValue : $loginValue . '@lostfound.local';

            $ins = $pdo->prepare("
                INSERT INTO admins (username, email, password_hash, is_active, created_at, updated_at) 
                VALUES (?, ?, ?, 1, NOW(), NOW())
            ");
            $ins->execute([$username, $email, $newHash]);
            $adminId = (int)$pdo->lastInsertId();
            $admin = [
                'id'       => $adminId,
                'username' => $username,
                'email'    => $email
            ];
        } else {
            // ရှိပြီးသား အကောင့်ဖြစ်ပါက စကားဝှက်ကို လက်ရှိရိုက်ထည့်သည့်အတိုင်း sync လုပ်ပေးခြင်း
            $upd = $pdo->prepare("UPDATE admins SET password_hash = ?, is_active = 1 WHERE id = ?");
            $upd->execute([$newHash, $admin['id']]);
        }

        // 6-digit OTP ထုတ်ယူခြင်း
        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpHash = password_hash($otpCode, PASSWORD_DEFAULT);
        $expiresAt = time() + (5 * 60);

        // OTP Session သတ်မှတ်ခြင်း
        $_SESSION['pending_admin_otp'] = [
            'type'       => 'login',
            'admin_id'   => (int) $admin['id'],
            'username'   => $admin['username'],
            'email'      => $admin['email'],
            'otp_hash'   => $otpHash,
            'expires_at' => $expiresAt
        ];

        // Email သို့ OTP ပို့ခြင်း
        $subject = 'Admin Login Verification Code - Lost & Found Hub';
        $body = "Hello {$admin['username']},\n\nYour Admin Sign-in verification code is: {$otpCode}\n\nExpires in 5 minutes.";

        if (function_exists('send_mail')) {
            send_mail($admin['email'], $subject, $body);
        }

        flash_set('info', 'Verification code sent to ' . $admin['email']);
        redirect('verify_otp.php');
    }
}

ui_header('Admin Sign In');
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
    <div class="bg-white p-8 sm:p-9 rounded-2xl shadow-xl border border-gray-100">
        
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-theme-green flex items-center justify-center mb-5 border border-emerald-100">
            <i class="fa-solid fa-shield-halved text-xl"></i>
        </div>

        <h1 class="font-serif text-2xl font-bold mb-1 text-gray-900">Admin Sign In</h1>
        <p class="text-xs text-gray-500 mb-6 leading-relaxed">
            Staff access only. A verification code will be sent to your admin email.
        </p>

        <?php if ($flash = flash_get()): ?>
            <?php 
                $fMsg = is_array($flash) ? ($flash['message'] ?? '') : $flash;
                $fType = is_array($flash) ? ($flash['type'] ?? 'success') : 'success';
                $isSuccess = ($fType === 'success');
            ?>
            <div class="mb-5 p-3.5 rounded-xl border flex items-center gap-2.5 text-xs <?= $isSuccess ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-red-50 text-red-800 border-red-200' ?>">
                <i class="fa-solid <?= $isSuccess ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-red-600' ?>"></i>
                <span><?= htmlspecialchars($fMsg) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs space-y-1.5">
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
                <label class="block text-xs font-semibold text-gray-700 mb-1">Username or Email</label>
                <input 
                    type="text" 
                    name="login" 
                    required 
                    placeholder="Admin username or email" 
                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50/50 text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none transition-all duration-200 focus-theme-green" 
                    value="<?= h($loginValue) ?>"
                >
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="admin_password" 
                        name="password" 
                        required 
                        placeholder="Enter password" 
                        class="w-full pl-4 pr-11 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50/50 text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none transition-all duration-200 focus-theme-green"
                    >
                    <button 
                        type="button" 
                        onclick="toggleAdminPassword()" 
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-700 focus:outline-none"
                    >
                        <i id="adminEyeIcon" class="fa-solid fa-eye-slash text-sm"></i>
                    </button>
                </div>
            </div>

            <div class="pt-2">
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 rounded-xl text-white font-medium text-sm transition-all duration-200 shadow-md hover:shadow-lg bg-theme-green flex items-center justify-center gap-2"
                >
                    <span>Continue to Verification</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </div>
        </form>

        <div class="mt-6 pt-4 border-t border-gray-100 text-center">
            <a href="../index.html" class="text-xs text-gray-500 hover:text-gray-900 inline-flex items-center gap-1.5 transition">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span>Back to website</span>
            </a>
        </div>

    </div>
</div>

<script>
function toggleAdminPassword() {
    const input = document.getElementById('admin_password');
    const icon = document.getElementById('adminEyeIcon');
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