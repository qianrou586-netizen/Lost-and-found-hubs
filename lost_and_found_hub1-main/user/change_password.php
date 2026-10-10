<?php
// user/change_password.php
// User စကားဝှက်ပြောင်းစာမျက်နှာ - လက်ရှိ password မှန်မှသာ အသစ်ပြောင်းခွင့်ပေးသည် (All-Dark Theme)
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('user', 'login.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        [$ok, $message] = auth_change_password(
            $pdo,
            (int) $_SESSION['user_id'],
            (string) ($_POST['current_password'] ?? ''),
            (string) ($_POST['new_password'] ?? ''),
            (string) ($_POST['confirm_password'] ?? '')
        );
        if ($ok) {
            flash_set('success', $message);
            redirect('change_password.php');
        }
        $errors[] = $message;
    }
}

ui_header('Change Password', 'user');
?>

<div class="max-w-md mx-auto py-12 px-4">
    <!-- Dark Surface Card Container -->
    <div class="p-8 sm:p-9 bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-2xl">
        <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-blue-900/30">
            <div class="w-10 h-10 rounded-xl bg-blue-950/80 border border-blue-800/40 text-blue-400 flex items-center justify-center">
                <i class="fa-solid fa-key text-base"></i>
            </div>
            <div>
                <h1 class="font-serif text-2xl font-bold text-white">Change Password</h1>
                <p class="text-xs text-slate-400">Update your account credentials safely.</p>
            </div>
        </div>

        <?php if ($flash = flash_get('success')): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-950/60 border border-emerald-800/50 text-emerald-300 text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-400"></i>
                <span><?= htmlspecialchars($flash) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-rose-950/60 border border-rose-800/50 text-rose-300 text-xs space-y-1">
                <?php foreach ($errors as $error): ?>
                    <p class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-400"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4" autocomplete="off">
            <?= csrf_field() ?>
            
            <!-- လက်ရှိ စကားဝှက် -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Current Password</label>
                <input 
                    type="password" 
                    name="current_password" 
                    required 
                    placeholder="Enter current password" 
                    class="form-input-pill w-full text-sm"
                >
            </div>

            <!-- စကားဝှက်အသစ် -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">New Password (min 8 characters)</label>
                <input 
                    type="password" 
                    name="new_password" 
                    required 
                    minlength="8" 
                    placeholder="Enter new password" 
                    class="form-input-pill w-full text-sm"
                >
            </div>

            <!-- စကားဝှက်အသစ် ပြန်လည်အတည်ပြုခြင်း -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Confirm New Password</label>
                <input 
                    type="password" 
                    name="confirm_password" 
                    required 
                    minlength="8" 
                    placeholder="Re-enter new password" 
                    class="form-input-pill w-full text-sm"
                >
            </div>

            <div class="pt-3">
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 rounded-xl text-white font-semibold text-sm transition-all duration-200 shadow-lg shadow-blue-950/50 bg-blue-600 hover:bg-blue-700 flex items-center justify-center gap-2"
                >
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                    <span>Update Password</span>
                </button>
            </div>
        </form>

        <div class="mt-6 pt-4 border-t border-blue-900/30 text-center">
            <a href="profile.php" class="text-xs text-slate-400 hover:text-blue-400 transition inline-flex items-center gap-1">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span>Back to Profile</span>
            </a>
        </div>
    </div>
</div>

<?php ui_footer(); ?>