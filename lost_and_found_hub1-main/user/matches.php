<?php
// user/matches.php
// Weighted Matching Algorithm ဖြင့် တွက်ချက်ထားသော ကိုက်ညီမှုများကို ပြသသည့် စာမျက်နှာ (All-Dark Theme)
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/matcher.php';

if (current_role() !== 'user') {
    ui_header('Matches', 'matches');
    ?>
    <div class="max-w-md mx-auto text-center py-24 px-4">
        <div class="w-12 h-12 rounded-full bg-blue-950/60 border border-blue-800/40 flex items-center justify-center mx-auto mb-4 text-blue-400">
            <i class="fa-solid fa-lock text-lg"></i>
        </div>
        <h1 class="font-serif text-2xl font-normal text-white mb-2">Sign In Required</h1>
        <p class="text-xs text-slate-400 mb-6">You need an account to view matches.</p>
        <a href="login.php" class="inline-block px-6 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition">Sign In</a>
    </div>
    <?php
    ui_footer();
    exit;
}

$userId = (int) ($_SESSION['user_id'] ?? $_SESSION['user']['id']);

$stmt = $pdo->prepare('
    SELECT m.id AS match_id, m.score, m.status AS match_status,
           target.id AS item_id, target.title AS item_title, target.type AS item_type, 
           target.photo_path, target.description AS item_description,
           c.name AS category_name, l.name AS location_name, ci.name AS city_name,
           u.username AS owner_username,
           up.avatar AS owner_avatar,
           my.title AS my_title
    FROM matches m
    JOIN items my ON (my.id = m.lost_item_id AND my.user_id = ?) 
                  OR (my.id = m.found_item_id AND my.user_id = ?)
    JOIN items target ON (target.id = CASE WHEN my.id = m.lost_item_id THEN m.found_item_id ELSE m.lost_item_id END)
    JOIN categories c ON c.id = target.category_id
    JOIN locations l ON l.id = target.location_id
    LEFT JOIN cities ci ON ci.id = l.city_id
    LEFT JOIN users u ON u.id = target.user_id
    LEFT JOIN user_profiles up ON up.user_id = target.user_id
    WHERE my.status = "open" AND target.status = "open"
    ORDER BY m.score DESC
');
$stmt->execute([$userId, $userId]);
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

ui_header('Smart Matches', 'matches', 'max-w-4xl');
?>

<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="mb-6">
        <h1 class="font-serif text-3xl font-normal text-white">Smart Matches</h1>
        <p class="text-xs text-slate-400 mt-1">Items paired using our string similarity &amp; weighted matching algorithm.</p>
    </div>

    <?php if (empty($matches)): ?>
        <div class="p-12 text-center bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-xl">
            <div class="w-12 h-12 rounded-full bg-blue-950/60 border border-blue-800/40 flex items-center justify-center mx-auto mb-3 text-blue-400">
                <i class="fa-solid fa-wand-magic-sparkles text-lg"></i>
            </div>
            <h2 class="font-serif text-lg font-normal text-white mb-1">No matches found yet</h2>
            <p class="text-xs text-slate-400 max-w-sm mx-auto">When someone posts an item that closely matches yours by name, category, or location, it will show up here.</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($matches as $match): 
                $score = round((float)$match['score']);
                $stars = match (true) {
                    $score >= 80 => 5,
                    $score >= 65 => 4,
                    $score >= 50 => 3,
                    $score >= 35 => 2,
                    default      => 1,
                };
            ?>
                <div class="p-5 bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-blue-500/50 transition">
                    <div class="flex items-start space-x-4">
                        <?php if (!empty($match['photo_path']) && file_exists(__DIR__ . '/../' . ltrim($match['photo_path'], '/'))): ?>
                            <img src="../<?= h(ltrim($match['photo_path'], '/')) ?>?v=<?= time() ?>" alt="Item Photo" class="w-20 h-20 rounded-xl object-cover border border-blue-900/40 flex-shrink-0">
                        <?php else: ?>
                            <div class="w-20 h-20 rounded-xl bg-[#0b1329] text-slate-500 flex items-center justify-center flex-shrink-0 text-lg border border-blue-900/30">
                                <i class="fa-solid fa-image"></i>
                            </div>
                        <?php endif; ?>

                        <div>
                            <div class="flex items-center space-x-2 mb-1.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $match['item_type'] === 'lost' ? 'bg-rose-950/80 text-rose-400 border border-rose-800/50' : 'bg-emerald-950/80 text-emerald-400 border border-emerald-800/50' ?>">
                                    <?= h($match['item_type']) ?>
                                </span>
                                <span class="text-xs text-slate-400"><?= h($match['category_name']) ?> • <?= h($match['location_name']) ?><?= !empty($match['city_name']) ? ', ' . h($match['city_name']) : '' ?></span>
                            </div>

                            <h3 class="font-medium text-white text-base"><?= h($match['item_title']) ?></h3>

                            <div class="flex items-center space-x-3 mt-1.5">
                                <div class="text-amber-400 text-xs">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa-solid fa-star <?= $i <= $stars ? '' : 'text-slate-600' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-[11px] font-bold text-blue-400 bg-blue-950/80 px-2.5 py-0.5 rounded-full border border-blue-800/50">
                                    <?= $score ?>% Match Score
                                </span>
                            </div>

                            <div class="flex items-center space-x-2 mt-2 pt-2 border-t border-blue-900/30 text-xs text-slate-400">
                                <?php 
                                $ownerAvatar = !empty($match['owner_avatar']) && file_exists(__DIR__ . '/../' . ltrim($match['owner_avatar'], '/')) 
                                    ? '../' . h(ltrim($match['owner_avatar'], '/')) 
                                    : null;
                                ?>
                                <?php if ($ownerAvatar): ?>
                                    <img src="<?= $ownerAvatar ?>" class="w-5 h-5 rounded-full object-cover border border-blue-800/40">
                                <?php else: ?>
                                    <span class="w-5 h-5 rounded-full bg-blue-600 text-white text-[10px] flex items-center justify-center font-bold">
                                        <?= strtoupper(substr($match['owner_username'] ?? 'U', 0, 1)) ?>
                                    </span>
                                <?php endif; ?>
                                <span>Reported by <strong class="text-white"><?= h($match['owner_username'] ?? 'User') ?></strong></span>
                                <span class="text-slate-600">•</span>
                                <span>Matched: <strong class="text-slate-300">"<?= h($match['my_title']) ?>"</strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 justify-end">
                        <?php if ($match['item_type'] === 'found'): ?>
                            <a href="claim.php?item_id=<?= (int)$match['item_id'] ?>" class="px-5 py-2 rounded-full bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition shadow-lg shadow-blue-950/50 inline-block">Claim Item</a>
                        <?php else: ?>
                            <a href="items.php?q=<?= urlencode($match['item_title']) ?>" class="px-4 py-2 rounded-full bg-[#0b1329] text-slate-300 text-xs font-semibold hover:text-white hover:bg-blue-950 transition border border-blue-900/40 inline-block">View Details</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php ui_footer(); ?>