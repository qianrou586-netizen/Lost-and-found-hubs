<?php
// user/item_detail.php
// LOST ဖြစ်နေသော ပစ္စည်းအချက်အလက်နှင့် ပိုင်ရှင်ထံသို့ တိုက်ရိုက် ဆက်သွယ်နိုင်သည့် စာမျက်နှာ (Dark Midnight Theme)
require_once __DIR__ . '/../includes/bootstrap.php';

$itemId = (int)($_GET['id'] ?? 0);
if ($itemId <= 0) {
    redirect('items.php');
}

$stmt = $pdo->prepare('
    SELECT i.*, 
           c.name AS category_name, 
           l.name AS location_name,
           ci.name AS city_name,
           u.username AS owner_username,
           u.email AS owner_email,
           up.phone AS owner_phone,
           up.avatar AS owner_avatar
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
    redirect('items.php');
}

ui_header('Item Details & Contact', 'browse', 'max-w-4xl');
?>

<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="mb-6">
        <a href="items.php" class="text-xs text-slate-400 hover:text-blue-400 transition inline-flex items-center space-x-1 mb-3">
            <span>&larr; Back to Browse</span>
        </a>
        <h1 class="font-serif text-3xl font-normal text-white">Item Details &amp; Contact Owner</h1>
        <p class="text-xs text-slate-400 mt-1">Found this item? Reach out to the owner directly.</p>
    </div>

    <div class="grid md:grid-cols-5 gap-8">
        
        <!-- Left: Item Photo & Details -->
        <div class="md:col-span-3 space-y-6">
            <div class="p-6 bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-xl overflow-hidden">
                <?php if (!empty($item['photo_path']) && file_exists(__DIR__ . '/../' . ltrim($item['photo_path'], '/'))): ?>
                    <img src="../<?= h(ltrim($item['photo_path'], '/')) ?>?v=<?= time() ?>" alt="<?= h($item['title']) ?>" class="w-full h-64 object-cover rounded-xl border border-blue-900/40 mb-6">
                <?php endif; ?>

                <div>
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $item['type'] === 'lost' ? 'bg-rose-950/80 text-rose-400 border border-rose-800/50' : 'bg-emerald-950/80 text-emerald-400 border border-emerald-800/50' ?>">
                            <?= h($item['type']) ?>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-blue-950/80 text-blue-400 border border-blue-800/50">
                            <?= h($item['status'] ?? 'open') ?>
                        </span>
                        <span class="text-xs text-slate-400 font-medium"><?= h($item['category_name']) ?></span>
                    </div>

                    <h2 class="font-serif text-2xl font-normal text-white mb-2"><?= h($item['title']) ?></h2>
                    
                    <div class="space-y-2 py-3 border-y border-blue-900/30 text-xs text-slate-300">
                        <p class="flex items-center">
                            <i class="fa-solid fa-location-dot w-5 text-blue-400"></i>
                            <strong>Location:</strong>&nbsp;<?= h($item['location_name']) ?><?= !empty($item['city_name']) ? ', ' . h($item['city_name']) : '' ?>
                        </p>
                        <?php if (!empty($item['specific_location'])): ?>
                            <p class="flex items-center"><i class="fa-solid fa-map-pin w-5 text-slate-400"></i><strong>Specific Area:</strong>&nbsp;<?= h($item['specific_location']) ?></p>
                        <?php endif; ?>
                        <p class="flex items-center"><i class="fa-regular fa-clock w-5 text-slate-400"></i><strong>Reported On:</strong>&nbsp;<?= date('M d, Y', strtotime($item['created_at'])) ?></p>
                    </div>

                    <div class="mt-4">
                        <h4 class="text-xs font-semibold text-slate-300 mb-1">Description:</h4>
                        <p class="text-sm text-slate-300 leading-relaxed whitespace-pre-line"><?= h($item['description'] ?? 'No description provided.') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Owner Contact Details Card -->
        <div class="md:col-span-2">
            <div class="p-6 bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-xl sticky top-24">
                <h3 class="font-serif text-lg font-normal text-white mb-4 pb-2 border-b border-blue-900/40">
                    Owner Contact Details
                </h3>

                <div class="flex items-center space-x-3 mb-6">
                    <?php 
                    $ownerAvatar = !empty($item['owner_avatar']) && file_exists(__DIR__ . '/../' . ltrim($item['owner_avatar'], '/')) 
                        ? '../' . h(ltrim($item['owner_avatar'], '/')) 
                        : null;
                    ?>
                    <?php if ($ownerAvatar): ?>
                        <img src="<?= $ownerAvatar ?>?v=<?= time() ?>" class="w-12 h-12 rounded-full object-cover border border-blue-800/40 shadow-xs">
                    <?php else: ?>
                        <span class="w-12 h-12 rounded-full bg-blue-600 text-white text-base flex items-center justify-center font-bold">
                            <?= strtoupper(substr($item['owner_username'] ?? 'O', 0, 1)) ?>
                        </span>
                    <?php endif; ?>

                    <div>
                        <p class="text-sm font-semibold text-white"><?= h($item['owner_username'] ?? 'Anonymous') ?></p>
                        <span class="text-[10px] text-slate-400">Item Owner (Reported Lost)</span>
                    </div>
                </div>

                <div class="space-y-4 text-xs">
                    <div class="p-3 rounded-xl bg-[#0b1329] border border-blue-900/30 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-phone text-blue-400"></i>
                            <span class="text-slate-300 font-medium"><?= !empty($item['owner_phone']) ? h($item['owner_phone']) : 'No phone listed' ?></span>
                        </div>
                        <?php if (!empty($item['owner_phone'])): ?>
                            <a href="tel:<?= h($item['owner_phone']) ?>" class="px-3 py-1 rounded-full bg-blue-600 text-white text-[10px] font-semibold hover:bg-blue-700 transition">Call</a>
                        <?php endif; ?>
                    </div>

                    <div class="p-3 rounded-xl bg-[#0b1329] border border-blue-900/30 flex items-center justify-between">
                        <div class="flex items-center space-x-2 truncate mr-2">
                            <i class="fa-solid fa-envelope text-blue-400"></i>
                            <span class="text-slate-300 font-medium truncate"><?= !empty($item['owner_email']) ? h($item['owner_email']) : 'No email' ?></span>
                        </div>
                        <?php if (!empty($item['owner_email'])): ?>
                            <a href="mailto:<?= h($item['owner_email']) ?>" class="px-3 py-1 rounded-full bg-blue-600 text-white text-[10px] font-semibold hover:bg-blue-700 transition flex-shrink-0">Email</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-6 p-4 rounded-xl bg-amber-950/60 border border-amber-800/40 text-amber-300 text-[11px] leading-relaxed">
                    <i class="fa-solid fa-shield-halved mr-1 text-amber-400"></i>
                    <strong>Safety Notice:</strong> Always meet in a public and secure place when returning lost property.
                </div>
            </div>
        </div>

    </div>
</div>

<?php ui_footer(); ?>