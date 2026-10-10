<?php
// user/profile.php
// User Profile စီမံခန့်ခွဲသည့် စာမျက်နှာ (All-Dark Midnight Theme)
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('user', 'login.php');

$userId = (int) $_SESSION['user_id'];
$errors = [];

$stmt = $pdo->prepare("
    SELECT u.username, u.email, up.* 
    FROM users u
    LEFT JOIN user_profiles up ON u.id = up.user_id
    WHERE u.id = ?
");
$stmt->execute([$userId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    destroy_session();
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $region   = trim($_POST['state_region'] ?? '');
        $city     = trim($_POST['city_township'] ?? '');
        $address  = trim($_POST['address'] ?? '');

        if ($fullName === '') {
            $fullName = $userData['username'];
        }

        $avatarPath = $userData['avatar'] ?? null;
        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed)) {
                $uploadDir = __DIR__ . '/../uploads/avatars/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $newFileName)) {
                    $avatarPath = 'uploads/avatars/' . $newFileName;
                }
            } else {
                $errors[] = 'Only JPG, PNG and WEBP image files are allowed.';
            }
        }

        if (empty($errors)) {
            $checkProf = $pdo->prepare("SELECT user_id FROM user_profiles WHERE user_id = ?");
            $checkProf->execute([$userId]);
            
            if ($checkProf->fetch()) {
                $upd = $pdo->prepare("
                    UPDATE user_profiles 
                    SET full_name = ?, phone = ?, state_region = ?, city_township = ?, address = ?, avatar = COALESCE(?, avatar), updated_at = NOW()
                    WHERE user_id = ?
                ");
                $upd->execute([$fullName, $phone, $region, $city, $address, $avatarPath, $userId]);
            } else {
                $ins = $pdo->prepare("
                    INSERT INTO user_profiles (user_id, full_name, phone, state_region, city_township, address, avatar, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $ins->execute([$userId, $fullName, $phone, $region, $city, $address, $avatarPath]);
            }

            flash_set('success', 'Profile updated successfully.');
            redirect('profile.php');
        }
    }
}

$displayFullName = !empty($userData['full_name']) ? $userData['full_name'] : $userData['username'];

ui_header('My Profile');
?>

<div class="max-w-2xl mx-auto py-10 px-4">
    <div class="bg-[#101d42] p-8 sm:p-10 rounded-2xl shadow-2xl border border-blue-900/40">
        
        <div class="text-center mb-8">
            <div class="relative w-24 h-24 mx-auto mb-3">
                <?php if (!empty($userData['avatar']) && file_exists(__DIR__ . '/../' . $userData['avatar'])): ?>
                    <img id="avatarPreview" src="../<?= h($userData['avatar']) ?>?v=<?= time() ?>" class="w-24 h-24 rounded-full object-cover border-2 border-blue-400">
                <?php else: ?>
                    <div id="avatarPlaceholder" class="w-24 h-24 rounded-full bg-blue-950/80 text-blue-400 flex items-center justify-center font-bold text-3xl border-2 border-blue-800/40">
                        <?= strtoupper(substr($userData['username'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                
                <label for="avatarInput" class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center cursor-pointer shadow hover:bg-blue-700 transition">
                    <i class="fa-solid fa-camera text-xs"></i>
                </label>
            </div>

            <h2 class="text-2xl font-serif font-bold text-white"><?= h($userData['username']) ?></h2>
            <p class="text-xs text-slate-400 mt-0.5"><?= h($userData['email']) ?></p>
            <p class="text-[11px] text-slate-500 mt-1">Click the camera icon to upload a photo and click Save Profile</p>
        </div>

        <?php if ($flash = flash_get()): ?>
            <?php 
                $fMsg = is_array($flash) ? ($flash['message'] ?? '') : $flash;
                $fType = is_array($flash) ? ($flash['type'] ?? 'success') : 'success';
                $isSuccess = ($fType === 'success');
            ?>
            <div class="mb-6 p-4 rounded-xl border flex items-center gap-3 text-xs sm:text-sm <?= $isSuccess ? 'bg-emerald-950/60 text-emerald-300 border-emerald-800/50' : 'bg-rose-950/60 text-rose-300 border-rose-800/50' ?>">
                <i class="fa-solid <?= $isSuccess ? 'fa-circle-check text-emerald-400' : 'fa-circle-exclamation text-rose-400' ?>"></i>
                <span><?= htmlspecialchars($fMsg) ?></span>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field() ?>
            <input type="file" id="avatarInput" name="avatar" class="hidden" accept="image/*" onchange="previewImage(this)">

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Full Name</label>
                <input 
                    type="text" 
                    name="full_name" 
                    required 
                    value="<?= h($displayFullName) ?>" 
                    class="form-input-pill w-full text-sm"
                >
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Phone Number (Myanmar)</label>
                <input 
                    type="text" 
                    name="phone" 
                    placeholder="09..." 
                    value="<?= h($userData['phone'] ?? '') ?>" 
                    class="form-input-pill w-full text-sm"
                >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">State / Region</label>
                    <input 
                        type="text" 
                        name="state_region" 
                        placeholder="e.g. Yangon Region" 
                        value="<?= h($userData['state_region'] ?? '') ?>" 
                        class="form-input-pill w-full text-sm"
                    >
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">City / Township</label>
                    <input 
                        type="text" 
                        name="city_township" 
                        placeholder="e.g. Mayangone" 
                        value="<?= h($userData['city_township'] ?? '') ?>" 
                        class="form-input-pill w-full text-sm"
                    >
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Detailed Address</label>
                <textarea 
                    name="address" 
                    rows="3" 
                    placeholder="Street, Ward, House No." 
                    class="w-full rounded-2xl border border-blue-900/30 bg-[#111e47] text-white p-4 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/25 placeholder-slate-500 transition"
                ><?= h($userData['address'] ?? '') ?></textarea>
            </div>

            <div class="pt-3">
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 rounded-xl text-white font-semibold text-sm transition shadow-lg shadow-blue-950/50 bg-blue-600 hover:bg-blue-700 flex items-center justify-center gap-2"
                >
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    <span>Save Profile</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let img = document.getElementById('avatarPreview');
            const placeholder = document.getElementById('avatarPlaceholder');
            if (!img && placeholder) {
                img = document.createElement('img');
                img.id = 'avatarPreview';
                img.className = 'w-24 h-24 rounded-full object-cover border-2 border-blue-400';
                placeholder.parentNode.replaceChild(img, placeholder);
            }
            img.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php ui_footer(); ?>