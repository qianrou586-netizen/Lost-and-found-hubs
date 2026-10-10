<?php
// Admin စကားဝှက်ပြောင်းစာမျက်နှာ - လက်ရှိ password မှန်မှသာ အသစ်ပြောင်းခွင့်ပေးသည်
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin', 'login.php');

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

ui_header('Change Password', 'admin');
?>
        <div class="card p-8">
            <h1 class="font-serif text-xl font-semibold mb-6">Change password</h1>

            <?php ui_messages($errors); ?>

            <form method="post" class="space-y-3" autocomplete="off">
                <?= csrf_field() ?>
                <input type="password" name="current_password" required placeholder="Current password" class="form-input">
                <input type="password" name="new_password" required minlength="8" placeholder="New password (min 8 characters)" class="form-input">
                <input type="password" name="confirm_password" required minlength="8" placeholder="Confirm new password" class="form-input">
                <button type="submit" class="btn btn-primary">Update password</button>
            </form>
        </div>
<?php ui_footer(); ?>
