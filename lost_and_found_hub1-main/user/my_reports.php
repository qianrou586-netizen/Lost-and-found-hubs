<?php
// user/my_reports.php
// မိမိတင်ထားသော ပျောက်ဆုံး/တွေ့ရှိ ပစ္စည်းမှတ်တမ်းများကို စီမံခန့်ခွဲသည့် စာမျက်နှာ (All-Dark Theme)
require_once __DIR__ . '/../includes/bootstrap.php';

if (current_role() !== 'user') {
    ui_header('My Reports', 'myitems');
    ?>
    <div class="max-w-md mx-auto text-center py-24 px-4">
        <div class="w-12 h-12 rounded-full bg-blue-950/60 border border-blue-800/40 flex items-center justify-center mx-auto mb-4 text-blue-400">
            <i class="fa-solid fa-lock text-lg"></i>
        </div>
        <h1 class="font-serif text-2xl font-normal text-white mb-2">Sign In Required</h1>
        <p class="text-xs text-slate-400 mb-6">You need an account to view and manage your reported items.</p>
        <a href="login.php" class="inline-block px-6 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition">Sign In</a>
    </div>
    <?php
    ui_footer();
    exit;
}

$userId = (int) ($_SESSION['user_id'] ?? $_SESSION['user']['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('error', 'Your session expired. Please try again.');
        redirect('my_reports.php');
    }

    $action = $_POST['action'] ?? '';
    $itemId = (int) ($_POST['item_id'] ?? 0);

    $chkStmt = $pdo->prepare('SELECT id FROM items WHERE id = ? AND user_id = ?');
    $chkStmt->execute([$itemId, $userId]);
    $itemExists = $chkStmt->fetch();

    if ($itemExists) {
        if ($action === 'resolve') {
            $upd = $pdo->prepare("UPDATE items SET status = 'resolved' WHERE id = ? AND user_id = ?");
            $upd->execute([$itemId, $userId]);
            flash_set('success', 'Item marked as resolved.');
        } elseif ($action === 'delete') {
            try {
                $pdo->prepare('DELETE FROM matches WHERE lost_item_id = ? OR found_item_id = ?')->execute([$itemId, $itemId]);
            } catch (Exception $e) {}

            try {
                $pdo->prepare('DELETE FROM claims WHERE found_item_id = ?')->execute([$itemId]);
            } catch (Exception $e) {}

            $pdo->prepare('DELETE FROM items WHERE id = ? AND user_id = ?')->execute([$itemId, $userId]);
            flash_set('success', 'Item report deleted successfully.');
        }
    }
    redirect('my_reports.php');
}

$stmt = $pdo->prepare('
    SELECT i.*, c.name AS category_name, l.name AS location_name, ci.name AS city_name,
           (SELECT COUNT(*) FROM matches m WHERE (m.lost_item_id = i.id OR m.found_item_id = i.id) AND m.status = "pending") AS match_count,
           (SELECT COUNT(*) FROM claims cl WHERE cl.found_item_id = i.id) AS claim_count
    FROM items i
    JOIN categories c ON c.id = i.category_id
    JOIN locations l ON l.id = i.location_id
    LEFT JOIN cities ci ON ci.id = l.city_id
    WHERE i.user_id = ?
    ORDER BY i.created_at DESC
');
$stmt->execute([$userId]);
$myItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$itemIds = array_column($myItems, 'id');
$claimsByItem = [];

if (!empty($itemIds)) {
    $inQuery = implode(',', array_fill(0, count($itemIds), '?'));
    
    $claimStmt = $pdo->prepare("
        SELECT cl.*, u.username, u.email AS claimant_email
        FROM claims cl
        JOIN users u ON cl.claimant_user_id = u.id
        WHERE cl.found_item_id IN ($inQuery)
        ORDER BY cl.created_at DESC
    ");
    $claimStmt->execute($itemIds);
    $allClaims = $claimStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($allClaims as $claim) {
        $claimsByItem[$claim['found_item_id']][] = $claim;
    }
}

ui_header('My Reports', 'myitems', 'max-w-5xl');
?>

<div class="max-w-5xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="font-serif text-3xl font-normal text-white">My Reports</h1>
            <p class="text-xs text-slate-400 mt-1">Manage and track all the items you have reported.</p>
        </div>
        <a href="report.php" class="px-5 py-2.5 rounded-full bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition flex items-center space-x-1.5 shadow-lg shadow-blue-950/50">
            <span>+</span>
            <span>Report Item</span>
        </a>
    </div>

    <!-- Alert / Messages -->
    <?php 
    $flashMsg = flash_get('success');
    if ($flashMsg): ?>
        <div class="mb-6 p-4 rounded-xl bg-emerald-950/60 text-emerald-300 text-xs border border-emerald-800/50 flex items-center space-x-2">
            <i class="fa-solid fa-circle-check text-emerald-400"></i>
            <span><?= h($flashMsg) ?></span>
        </div>
    <?php endif; ?>

    <?php if (empty($myItems)): ?>
        <div class="p-12 text-center bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-xl">
            <div class="w-12 h-12 rounded-full bg-blue-950/60 border border-blue-800/40 flex items-center justify-center mx-auto mb-3 text-blue-400">
                <i class="fa-regular fa-folder-open text-lg"></i>
            </div>
            <h2 class="font-serif text-lg font-normal text-white mb-1">No reported items yet</h2>
            <p class="text-xs text-slate-400 max-w-sm mx-auto mb-6">You have not submitted any lost or found item reports yet.</p>
            <a href="report.php" class="inline-block px-5 py-2.5 rounded-full bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition shadow-xs">Report Your First Item</a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($myItems as $item): ?>
                <?php 
                    $itemId = (int)$item['id'];
                    $itemClaims = $claimsByItem[$itemId] ?? [];
                    $claimTotal = count($itemClaims);
                    $displayClaimCount = !empty($item['claim_count']) ? (int)$item['claim_count'] : $claimTotal;
                ?>
                <div class="p-5 bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-xl">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        
                        <div class="flex items-start space-x-4">
                            <?php if (!empty($item['photo_path']) && file_exists(__DIR__ . '/../' . $item['photo_path'])): ?>
                                <img src="../<?= h($item['photo_path']) ?>?v=<?= time() ?>" alt="Item Photo" class="w-16 h-16 rounded-xl object-cover border border-blue-900/40 flex-shrink-0">
                            <?php else: ?>
                                <div class="w-16 h-16 rounded-xl bg-[#0b1329] text-slate-500 flex items-center justify-center flex-shrink-0 text-sm border border-blue-900/30">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            <?php endif; ?>

                            <div>
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $item['type'] === 'lost' ? 'bg-rose-950/80 text-rose-400 border border-rose-800/50' : 'bg-emerald-950/80 text-emerald-400 border border-emerald-800/50' ?>">
                                        <?= h($item['type']) ?>
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider <?= $item['status'] === 'open' ? 'bg-blue-950/80 text-blue-400 border border-blue-800/50' : 'bg-slate-800 text-slate-300' ?>">
                                        <?= h($item['status']) ?>
                                    </span>
                                    <span class="text-xs text-slate-400"><?= h($item['category_name']) ?> • <?= h($item['location_name']) ?><?= !empty($item['city_name']) ? ', ' . h($item['city_name']) : '' ?></span>
                                </div>

                                <h2 class="font-medium text-white text-sm md:text-base"><?= h($item['title']) ?></h2>

                                <?php if (!empty($item['description'])): ?>
                                    <p class="text-xs text-slate-300 mt-1 line-clamp-1 max-w-lg leading-relaxed"><?= h($item['description']) ?></p>
                                <?php endif; ?>

                                <div class="flex items-center space-x-4 text-[11px] text-slate-400 mt-2">
                                    <span>Reported: <?= date('M d, Y', strtotime($item['created_at'])) ?></span>
                                    
                                    <?php if ((int)$item['match_count'] > 0): ?>
                                        <span class="text-blue-400 font-bold"><i class="fa-solid fa-wand-magic-sparkles mr-1"></i><?= (int)$item['match_count'] ?> Matches</span>
                                    <?php endif; ?>

                                    <?php if ($item['type'] === 'found' && $displayClaimCount > 0): ?>
                                        <button 
                                            type="button" 
                                            onclick="toggleClaims('claims-drawer-<?= $itemId ?>')" 
                                            class="inline-flex items-center space-x-1 font-bold text-rose-400 hover:text-rose-300 bg-rose-950/80 hover:bg-rose-900/80 px-2.5 py-1 rounded-full border border-rose-800/50 transition cursor-pointer"
                                        >
                                            <i class="fa-solid fa-hand text-xs"></i>
                                            <span><?= $displayClaimCount ?> Claims</span>
                                            <i class="fa-solid fa-chevron-down text-[9px] ml-1"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Buttons -->
                        <div class="flex items-center space-x-2 border-t md:border-t-0 pt-3 md:pt-0 border-blue-900/40 justify-end">
                            <?php if ($item['status'] === 'open'): ?>
                                <form method="post" onsubmit="return confirm('Mark this item as resolved?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="resolve">
                                    <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                                    <button type="submit" class="px-3.5 py-1.5 rounded-full bg-emerald-950/80 text-emerald-400 hover:bg-emerald-900/80 border border-emerald-800/50 text-xs font-semibold transition">
                                        Mark Resolved
                                    </button>
                                </form>
                            <?php endif; ?>

                            <form method="post" onsubmit="return confirm('Are you sure you want to permanently delete this report?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                                <button type="submit" class="px-3.5 py-1.5 rounded-full bg-rose-950/80 text-rose-400 hover:bg-rose-900/80 border border-rose-800/50 text-xs font-semibold transition">
                                    Delete
                                </button>
                            </form>
                        </div>

                    </div>

                    <!-- Claim Drawer -->
                    <div id="claims-drawer-<?= $itemId ?>" style="display: none;" class="mt-4 pt-4 border-t border-dashed border-blue-900/40">
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-3 flex items-center space-x-1.5">
                            <i class="fa-solid fa-users text-blue-400"></i>
                            <span>Claimant Details &amp; Contact</span>
                        </h3>

                        <?php if (!empty($itemClaims)): ?>
                            <div class="space-y-3">
                                <?php foreach ($itemClaims as $claim): ?>
                                    <div class="p-3.5 rounded-xl bg-[#0b1329] border border-blue-900/30 text-xs space-y-2">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-6 h-6 rounded-full bg-blue-950 text-blue-400 border border-blue-800/40 flex items-center justify-center font-bold text-[10px]">
                                                    <?= strtoupper(substr($claim['username'] ?? 'U', 0, 1)) ?>
                                                </div>
                                                <span class="font-semibold text-white"><?= h($claim['username']) ?></span>
                                                <span class="text-slate-400 text-[10px]">(Claimed on <?= date('M d, Y h:i A', strtotime($claim['created_at'])) ?>)</span>
                                            </div>

                                            <div class="flex items-center space-x-2">
                                                <a 
                                                    href="mailto:<?= h($claim['claimant_email']) ?>?subject=Regarding your claim for <?= urlencode($item['title']) ?>" 
                                                    class="inline-flex items-center space-x-1 px-3 py-1 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition font-semibold"
                                                >
                                                    <i class="fa-solid fa-envelope"></i>
                                                    <span>Send Email</span>
                                                </a>
                                            </div>
                                        </div>

                                        <div>
                                            <span class="font-semibold text-slate-300">Verification Proof / Message:</span>
                                            <p class="mt-1 text-slate-200 bg-[#101d42] p-2.5 rounded-lg border border-blue-900/40 leading-relaxed">
                                                <?= nl2br(h($claim['message'] ?? 'No details provided.')) ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-3 rounded-xl bg-[#0b1329] text-slate-400 text-xs border border-blue-900/30">
                                No active claim details found for this specific item ID.
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleClaims(drawerId) {
    const el = document.getElementById(drawerId);
    if (el) {
        el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
    }
}
</script>

<?php ui_footer(); ?>