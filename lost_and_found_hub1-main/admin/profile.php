<?php
// Admin Profile - Avatar Upload & Settings
require_once __DIR__ . '/../includes/bootstrap.php';

if (empty($_SESSION['admin_id'])) {
    flash_set('error', 'Please sign in first.');
    redirect('login.php');
}

$adminId = (int) $_SESSION['admin_id'];
$errors = [];

$stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ? LIMIT 1');
$stmt->execute([$adminId]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    redirect('logout.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors[] = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $phone = trim($_POST['phone'] ?? '');
            $avatarPath = $admin['avatar'];

            if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $fileTmp  = $_FILES['avatar']['tmp_name'];
                $fileName = $_FILES['avatar']['name'];
                $fileSize = $_FILES['avatar']['size'];
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($ext, $allowed)) {
                    $errors[] = 'Only JPG, JPEG, PNG, and WEBP image files are allowed.';
                } elseif ($fileSize > 2 * 1024 * 1024) {
                    $errors[] = 'Profile picture must not exceed 2MB.';
                } else {
                    $uploadDir = __DIR__ . '/../uploads/avatars/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $newFilename = 'admin_' . $adminId . '_' . time() . '.' . $ext;
                    $targetPath  = $uploadDir . $newFilename;

                    if (move_uploaded_file($fileTmp, $targetPath)) {
                        $avatarPath = 'uploads/avatars/' . $newFilename;
                        $_SESSION['admin_avatar'] = $avatarPath;
                    } else {
                        $errors[] = 'Failed to upload photo. Check folder permissions.';
                    }
                }
            }

            if (!$errors) {
                $stmt = $pdo->prepare('UPDATE admins SET phone = ?, avatar = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([$phone, $avatarPath, $adminId]);
                flash_set('success', 'Profile and avatar updated successfully.');
                redirect('profile.php');
            }
        }

        if ($action === 'change_password') {
            $currentPass = (string) ($_POST['current_password'] ?? '');
            $newPass     = (string) ($_POST['new_password'] ?? '');
            $confirmPass = (string) ($_POST['confirm_password'] ?? '');

            if (!password_verify($currentPass, $admin['password_hash'])) {
                $errors[] = 'Current password does not match.';
            } elseif (strlen($newPass) < 8) {
                $errors[] = 'New password must be at least 8 characters.';
            } elseif ($newPass !== $confirmPass) {
                $errors[] = 'New passwords do not match.';
            } else {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE admins SET password_hash = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([$newHash, $adminId]);
                flash_set('success', 'Password updated successfully.');
                redirect('profile.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Admin Console</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #0f172a; }
        .admin-sidebar { background-color: #111827; border-right: 1px solid #1f2937; }
        .admin-content { background-color: #0b1120; }
        .admin-card { background-color: #1e293b; border: 1px solid #334155; }
    </style>
</head>
<body class="text-slate-100 flex min-h-screen">

    <aside class="w-64 admin-sidebar flex flex-col justify-between shrink-0 hidden md:flex">
        <div>
            <div class="p-6 flex items-center gap-3 border-b border-gray-800">
                <div class="w-10 h-10 rounded-xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center text-lg font-bold border border-emerald-500/30">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h1 class="font-bold text-base tracking-wide text-white leading-tight">Admin Console</h1>
                    <span class="text-[10px] text-emerald-400 font-semibold uppercase tracking-wider">Control Center</span>
                </div>
            </div>

            <nav class="p-4 space-y-1.5 text-xs font-semibold">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition">
                    <i class="fa-solid fa-chart-pie w-4"></i>
                    <span>Dashboard &amp; Stats</span>
                </a>
                <a href="items.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition">
                    <i class="fa-solid fa-boxes-stacked w-4"></i>
                    <span>Manage Items</span>
                </a>
                <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition">
                    <i class="fa-solid fa-users w-4"></i>
                    <span>Manage Users</span>
                </a>
                <a href="claims.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition">
                    <i class="fa-solid fa-hand-holding-hand w-4"></i>
                    <span>Review Claims</span>
                </a>

                <div class="pt-4 pb-2 px-3 text-[10px] uppercase font-bold text-slate-500 tracking-wider">Settings &amp; Preferences</div>
                
                <!-- Admin Staff Tab ထည့်သွင်းထားသည် -->
                <a href="manage_admins.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition">
                    <i class="fa-solid fa-user-shield w-4"></i>
                    <span>Admin Staff</span>
                </a>
                <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-900/30">
                    <i class="fa-solid fa-user-gear w-4"></i>
                    <span>Admin Profile</span>
                </a>
                <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition">
                    <i class="fa-solid fa-tags w-4"></i>
                    <span>Categories</span>
                </a>
                <a href="locations.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition">
                    <i class="fa-solid fa-map-location-dot w-4"></i>
                    <span>Locations</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900/80 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-800/60 transition group cursor-pointer" onclick="window.location.href='profile.php'">
                <a href="profile.php" class="flex items-center gap-3 flex-1 overflow-hidden" title="View Profile">
                    <?php if (!empty($admin['avatar']) && file_exists(__DIR__ . '/../' . $admin['avatar'])): ?>
                        <img src="../<?= htmlspecialchars($admin['avatar']) ?>" class="w-9 h-9 rounded-lg object-cover border border-emerald-500/40 shrink-0">
                    <?php else: ?>
                        <div class="w-9 h-9 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0 group-hover:bg-emerald-500 group-hover:text-white transition">
                            <?= strtoupper(substr($admin['username'] ?? 'A', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="overflow-hidden">
                        <div class="text-xs font-bold text-white truncate group-hover:text-emerald-400 transition flex items-center gap-1.5">
                            <span><?= htmlspecialchars($admin['username'] ?? 'Admin') ?></span>
                            <i class="fa-solid fa-gear text-[10px] text-slate-500 group-hover:text-emerald-400"></i>
                        </div>
                        <div class="text-[10px] text-slate-400 truncate"><?= htmlspecialchars($admin['email'] ?? '') ?></div>
                    </div>
                </a>
                <a href="logout.php" title="Sign Out" onclick="event.stopPropagation();" class="text-slate-500 hover:text-red-400 p-2 rounded-lg hover:bg-red-500/10 transition ml-1 shrink-0">
                    <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                </a>
            </div>
        </div>
    </aside>

    <main class="flex-1 admin-content flex flex-col min-h-screen overflow-x-hidden">
        <header class="h-16 border-b border-gray-800 flex items-center justify-between px-6 bg-slate-900/40">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-slate-400">Environment:</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Production Admin</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="../user/index.php" target="_blank" class="px-3.5 py-1.5 rounded-xl text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-300 transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    <span>Visit Public Site</span>
                </a>
                <a href="logout.php" class="px-3.5 py-1.5 rounded-xl text-xs font-medium bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20 transition">
                    Logout
                </a>
            </div>
        </header>

        <div class="p-6 md:p-8 space-y-6 max-w-4xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Admin Profile &amp; Settings</h1>
                <p class="text-xs text-slate-400 mt-1">Update your account picture, contact details, and password.</p>
            </div>

            <?php if ($flash = flash_get()): ?>
                <?php 
                    $fMsg = is_array($flash) ? ($flash['message'] ?? '') : $flash;
                    $fType = is_array($flash) ? ($flash['type'] ?? 'success') : 'success';
                    $isSuccess = ($fType === 'success');
                ?>
                <div class="p-4 rounded-xl border text-xs <?= $isSuccess ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-red-500/10 text-red-400 border-red-500/30' ?>">
                    <?= htmlspecialchars($fMsg) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs space-y-1">
                    <?php foreach ($errors as $err): ?>
                        <p>&bull; <?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                
                <div class="admin-card p-6 rounded-2xl">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-5 flex items-center gap-2">
                        <i class="fa-solid fa-id-badge text-emerald-400"></i>
                        <span>Profile Picture &amp; Details</span>
                    </h2>

                    <form method="post" enctype="multipart/form-data" class="space-y-4">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update_profile">

                        <div class="flex items-center gap-4 pb-2">
                            <div class="relative w-16 h-16 rounded-2xl overflow-hidden border-2 border-slate-700 bg-slate-900 shrink-0">
                                <?php if (!empty($admin['avatar']) && file_exists(__DIR__ . '/../' . $admin['avatar'])): ?>
                                    <img id="avatarPreview" src="../<?= htmlspecialchars($admin['avatar']) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div id="avatarFallback" class="w-full h-full flex items-center justify-center text-xl font-bold text-emerald-400 bg-emerald-500/20">
                                        <?= strtoupper(substr($admin['username'] ?? 'A', 0, 1)) ?>
                                    </div>
                                    <img id="avatarPreview" src="" class="w-full h-full object-cover hidden">
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Upload New Picture</label>
                                <input type="file" name="avatar" accept="image/*" onchange="previewImage(event)" class="text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500 cursor-pointer">
                                <p class="text-[10px] text-slate-500 mt-1">PNG, JPG, WEBP (Max 2MB)</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Username</label>
                            <input type="text" value="<?= htmlspecialchars($admin['username']) ?>" disabled class="w-full px-4 py-2.5 text-xs rounded-xl bg-slate-900/60 border border-slate-700/60 text-slate-500 cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Email Address</label>
                            <input type="email" value="<?= htmlspecialchars($admin['email']) ?>" disabled class="w-full px-4 py-2.5 text-xs rounded-xl bg-slate-900/60 border border-slate-700/60 text-slate-500 cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Phone Number</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($admin['phone'] ?? '') ?>" placeholder="09xxxxxxxxx" class="w-full px-4 py-2.5 text-xs rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-emerald-500">
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white transition">
                            Save Changes
                        </button>
                    </form>
                </div>

                <div class="admin-card p-6 rounded-2xl">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-lock text-amber-400"></i>
                        <span>Change Password</span>
                    </h2>

                    <form method="post" class="space-y-4">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="change_password">

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Current Password</label>
                            <input type="password" name="current_password" required placeholder="Enter current password" class="w-full px-4 py-2.5 text-xs rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">New Password</label>
                            <input type="password" name="new_password" required minlength="8" placeholder="At least 8 characters" class="w-full px-4 py-2.5 text-xs rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Confirm New Password</label>
                            <input type="password" name="confirm_password" required minlength="8" placeholder="Repeat new password" class="w-full px-4 py-2.5 text-xs rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-emerald-500">
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition">
                            Update Password
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </main>

    <script>
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatarPreview');
                const fallback = document.getElementById('avatarFallback');
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (fallback) fallback.classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    }
    </script>
</body>
</html>