<?php
// user/claim.php
// တွေ့ရှိထားသော ပစ္စည်းအား ပိုင်ရှင်အဖြစ် တောင်းဆိုပြီး Finder နှင့် Claimant ဆက်သွယ်နိုင်သည့် စာမျက်နှာ (Dark Midnight Theme)
require_once __DIR__ . '/../includes/bootstrap.php';

// Guest စစ်ဆေးခြင်း
if (current_role() !== 'user') {
    ui_header('Claim Item', 'browse');
    ?>
    <div class="max-w-md mx-auto text-center py-24 px-4">
        <div class="w-12 h-12 rounded-full bg-blue-950/60 border border-blue-800/40 flex items-center justify-center mx-auto mb-4 text-blue-400">
            <i class="fa-solid fa-lock text-lg"></i>
        </div>
        <h1 class="font-serif text-2xl font-normal text-white mb-2">Sign In Required</h1>
        <p class="text-xs text-slate-400 mb-6">You need an account to claim items and view contact details.</p>
        <a href="login.php" class="inline-block px-6 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition">Sign In</a>
    </div>
    <?php
    ui_footer();
    exit;
}

$userId = (int) ($_SESSION['user_id'] ?? $_SESSION['user']['id']);
$itemId = (int) ($_GET['item_id'] ?? 0);

if ($itemId <= 0) {
    redirect('items.php');
}

// ပစ္စည်းအချက်အလက်နှင့်အတူ Finder (တွေ့ရှိသူ) ၏ Profile နှင့် City ကို ချိတ်ဆက်ဆွဲယူခြင်း
$stmt = $pdo->prepare('
    SELECT i.*, 
           c.name AS category_name, 
           l.name AS location_name,
           ci.name AS city_name,
           u.username AS finder_username,
           u.email AS finder_email,
           up.phone AS finder_phone,
           up.avatar AS finder_avatar
    FROM items i
    JOIN categories c ON c.id = i.category_id
    JOIN locations l ON l.id = i.location_id
    LEFT JOIN cities ci ON ci.id = l.city_id
    LEFT JOIN users u ON u.id = i.user_id
    LEFT JOIN user_profiles up ON up.user_id = i.user_id
    WHERE i.id = ?
');
$stmt->execute([$itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    ui_header('Item Not Found', 'browse');
    echo '<div class="max-w-md mx-auto text-center py-20"><p class="text-slate-400">Item not found or already deleted.</p><a href="items.php" class="text-blue-400 mt-4 inline-block font-semibold">Back to Browse</a></div>';
    ui_footer();
    exit;
}

$isOwnItem = ((int)$item['user_id'] === $userId);

$claimStmt = $pdo->prepare('SELECT * FROM claims WHERE found_item_id = ? AND claimant_user_id = ?');
$claimStmt->execute([$itemId, $userId]);
$existingClaim = $claimStmt->fetch(PDO::FETCH_ASSOC);

$myLostStmt = $pdo->prepare('SELECT id, title FROM items WHERE user_id = ? AND type = "lost" AND status = "open"');
$myLostStmt->execute([$userId]);
$myLostItems = $myLostStmt->fetchAll(PDO::FETCH_ASSOC);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isOwnItem) { if (!csrf_valid()) { $errors[] = 'Your session expired. Please try again.'; } else { $message    = trim($_POST['message'] ?? ''); $lostItemId = !empty($_POST['lost_item_id']) ? (int)$_POST['lost_item_id'] : null;
    if ($message === '') {
        $errors[] = 'Please provide details or proof of ownership in your message.';
    }

    // Proof Image Upload Logic
    $proofImageName = $existingClaim['proof_image'] ?? null;

    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['proof_image']['tmp_name'];
        $fileName      = $_FILES['proof_image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $proofImageName = time() . '_' . uniqid() . '.' . $fileExtension;
            $uploadFileDir  = __DIR__ . '/../uploads/';

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            move_uploaded_file($fileTmpPath, $uploadFileDir . $proofImageName);
        } else {
            $errors[] = 'Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP.';
        }
    }

    if (!$errors) {
        if ($existingClaim) {
            $updStmt = $pdo->prepare('
                UPDATE claims 
                SET message = ?, lost_item_id = ?, proof_image = ?, status = "pending" 
                WHERE id = ?
            ');
            $updStmt->execute([$message, $lostItemId, $proofImageName, $existingClaim['id']]);
        } else {
            $insStmt = $pdo->prepare('
                INSERT INTO claims (found_item_id, claimant_user_id, lost_item_id, message, proof_image, status, created_at)
                VALUES (?, ?, ?, ?, ?, "pending", NOW())
            ');
            $insStmt->execute([$itemId, $userId, $lostItemId, $message, $proofImageName]);
        }

        flash_set('success', 'Your claim request has been sent! You can now contact the finder directly.');
        redirect("claim.php?item_id={$itemId}");
    }
}
}
?>
<?php ui_header('Claim Item','browse', 'max-w-4xl'); ?>

    <!-- Alert / Messages -->
     <?php
    $flashMsg = flash_get('success');
    if ($flashMsg): ?>
        <div class="mb-6 p-4 rounded-xl bg-emerald-950/60 text-emerald-300 text-xs border border-emerald-800/50 flex items-center space-x-2">
            <i class="fa-solid fa-circle-check text-emerald-400"></i>
            <span><?= h($flashMsg) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="mb-6 p-4 rounded-xl bg-rose-950/60 text-rose-300 text-xs border border-rose-800/50 space-y-1">
            <?php foreach ($errors as $err): ?>
                <p><i class="fa-solid fa-triangle-exclamation mr-1.5 text-rose-400"></i><?= h($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="grid md:grid-cols-5 gap-8">
        
        <!-- ဘယ်ဘက်ခြမ်း - ပစ္စည်းအကျဉ်းချုပ် နှင့် Finder အဆက်အသွယ် -->
        <div class="md:col-span-2 space-y-6">
            <div class="p-5 bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-xl overflow-hidden">
                <?php if (!empty($item['photo_path']) && file_exists(__DIR__ . '/../' . ltrim($item['photo_path'], '/'))): ?>
                    <img src="../<?= h(ltrim($item['photo_path'], '/')) ?>?v=<?= time() ?>" alt="<?= h($item['title']) ?>" class="w-full h-44 object-cover rounded-xl border border-blue-900/40 mb-4">
                <?php endif; ?>

                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $item['type'] === 'lost' ? 'bg-rose-950/80 text-rose-400 border border-rose-800/50' : 'bg-emerald-950/80 text-emerald-400 border border-emerald-800/50' ?>">
                        <?= h($item['type']) ?>
                    </span>
                    <h2 class="font-serif text-xl font-normal text-white mt-2"><?= h($item['title']) ?></h2>
                    <p class="text-xs text-slate-400 mt-1">
                        <?= h($item['category_name']) ?> • <?= h($item['location_name']) ?><?= !empty($item['city_name']) ? ', ' . h($item['city_name']) : '' ?>
                    </p>
                    
                    <?php if (!empty($item['description'])): ?>
                        <p class="text-xs text-slate-300 mt-3 pt-3 border-t border-blue-900/30 leading-relaxed"><?= h($item['description']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Finder Contact Info -->
            <div class="p-5 bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-xl">
                <h3 class="font-serif text-base font-normal text-white mb-3 pb-2 border-b border-blue-900/40">
                    Finder Contact Details
                </h3>

                <div class="flex items-center space-x-3 mb-4">
                    <?php 
                    $finderAvatar = !empty($item['finder_avatar']) && file_exists(__DIR__ . '/../' . ltrim($item['finder_avatar'], '/')) 
                        ? '../' . h(ltrim($item['finder_avatar'], '/')) 
                        : null;
                    ?>
                    <?php if ($finderAvatar): ?>
                        <img src="<?= $finderAvatar ?>?v=<?= time() ?>" class="w-10 h-10 rounded-full object-cover border border-blue-800/40 shadow-xs">
                    <?php else: ?>
                        <span class="w-10 h-10 rounded-full bg-blue-600 text-white text-sm flex items-center justify-center font-bold">
                            <?= strtoupper(substr($item['finder_username'] ?? 'F', 0, 1)) ?>
                        </span>
                    <?php endif; ?>

                    <div>
                        <p class="text-sm font-semibold text-white"><?= h($item['finder_username'] ?? 'Anonymous') ?></p>
                        <span class="text-[10px] text-slate-400">Reporter</span>
                    </div>
                </div>

                <div class="space-y-2.5 text-xs text-slate-300">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-phone text-blue-400 w-4"></i>
                        <?php if (!empty($item['finder_phone'])): ?>
                            <a href="tel:<?= h($item['finder_phone']) ?>" class="text-blue-400 hover:text-blue-300 font-semibold hover:underline">
                                <?= h($item['finder_phone']) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-slate-500 italic">No phone provided</span>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-envelope text-blue-400 w-4"></i>
                        <?php if (!empty($item['finder_email'])): ?>
                            <a href="mailto:<?= h($item['finder_email']) ?>" class="text-blue-400 hover:text-blue-300 font-semibold hover:underline truncate">
                                <?= h($item['finder_email']) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-slate-500 italic">No email available</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ညာဘက်ခြမ်း - Claim Form -->
        <div class="md:col-span-3">
            <div class="p-7 bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-xl">
                <?php if ($isOwnItem): ?>
                    <div class="text-center py-10">
                        <i class="fa-solid fa-circle-info text-2xl text-slate-500 mb-2"></i>
                        <h3 class="font-serif text-lg text-white font-normal">This is your own report</h3>
                        <p class="text-xs text-slate-400 mt-1">You reported this item, so you cannot claim it yourself.</p>
                    </div>
                <?php else: ?>
                    <h2 class="font-serif text-xl font-normal text-white mb-1">Prove Ownership</h2>
                    <p class="text-xs text-slate-400 mb-5">Describe distinctive features so the finder can confirm your ownership.</p>

                    <?php if ($existingClaim): ?>
                        <div class="mb-5 p-4 rounded-xl border text-xs <?= $existingClaim['status'] === 'approved' ? 'bg-emerald-950/60 border-emerald-800/50 text-emerald-300' : ($existingClaim['status'] === 'rejected' ? 'bg-rose-950/60 border-rose-800/50 text-rose-300' : 'bg-blue-950/60 border-blue-800/50 text-blue-300') ?>">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold uppercase tracking-wider text-[10px]">Claim Status: <?= h($existingClaim['status']) ?></span>
                                <span class="text-slate-400"><?= date('M d, Y', strtotime($existingClaim['created_at'])) ?></span>
                            </div>
                            <?php if (!empty($existingClaim['response_message'])): ?>
                                <p class="mt-2 pt-2 border-t border-blue-900/30"><strong>Response:</strong> <?= h($existingClaim['response_message']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" class="space-y-5">
                        <?= csrf_field() ?>

                        <?php if (!empty($myLostItems)): ?>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Link to Your Lost Report (Optional)</label>
                                <select name="lost_item_id" class="form-input-pill w-full text-sm">
                                    <option value="" class="bg-[#101d42] text-slate-400">-- None / Report Not Listed --</option>
                                    <?php foreach ($myLostItems as $lost): ?>
                                        <option value="<?= (int)$lost['id'] ?>" class="bg-[#101d42] text-white" <?= ($existingClaim['lost_item_id'] ?? null) == $lost['id'] ? 'selected' : '' ?>>
                                            <?= h($lost['title']) ?> (#<?= (int)$lost['id'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                 Message &amp; Proof of Ownership <span class="text-rose-400">*</span>
                            </label>
                            <textarea name="message" required rows="5" class="w-full rounded-2xl border border-blue-900/30 bg-[#111e47] text-white p-4 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/25 placeholder-slate-500 transition" placeholder="Describe unique stickers, wallpaper, marks, internal items, or provide contact info..."><?= h($existingClaim['message'] ?? '') ?></textarea>
                        </div>
                    <!-- Proof Image File Input -->
                <div class="mt-4">
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Upload Proof Image (အထောက်အထား ဓာတ်ပုံ)
                        </label>
                        <input type="file" name="proof_image" accept="image/*" class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
            </div>

                        <div class="pt-3 flex items-center justify-between border-t border-blue-900/40">
                            <a href="items.php" class="text-xs text-slate-400 hover:text-white transition">Cancel</a>
                            <button type="submit" class="px-6 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-lg shadow-blue-950/50 transition">
                                <?= $existingClaim ? 'Update Claim' : 'Send Claim Request' ?>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php ui_footer(); ?>