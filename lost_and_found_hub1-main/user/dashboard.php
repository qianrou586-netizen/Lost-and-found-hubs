<?php
// user/dashboard.php
// စနစ်တွင်း စာရင်းအင်းနှင့် စွမ်းဆောင်ရည် အကျဉ်းချုပ် စာမျက်နှာ (Dark Midnight Theme)
require_once __DIR__ . '/../includes/bootstrap.php';

$totalItems  = (int) $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
$activeItems = (int) $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'open'")->fetchColumn();
$reunited    = (int) $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'resolved'")->fetchColumn();
$rate        = $totalItems > 0 ? round(($reunited / $totalItems) * 100) : 0;

$byCategory = $pdo->query("SELECT c.name, COUNT(i.id) as count FROM categories c LEFT JOIN items i ON i.category_id = c.id GROUP BY c.id ORDER BY c.name ASC")->fetchAll();
$byLocation = $pdo->query("SELECT l.name, COUNT(i.id) as count FROM locations l LEFT JOIN items i ON i.location_id = l.id GROUP BY l.id ORDER BY l.name ASC")->fetchAll();

ui_header('Dashboard', 'stats');
?>

<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="mb-8">
        <h1 class="font-serif text-3xl font-normal text-white">Dashboard</h1>
        <p class="text-xs text-slate-400 mt-1">Overview of all reported items and recovery performance.</p>
    </div>

    <!-- Top Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="p-6 bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-xl">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Items</span>
            <div class="font-serif text-4xl text-white font-normal mt-2"><?= $totalItems ?></div>
        </div>
        <div class="p-6 bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-xl">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Active</span>
            <div class="font-serif text-4xl text-blue-400 font-normal mt-2"><?= $activeItems ?></div>
        </div>
        <div class="p-6 bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-xl">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Reunited</span>
            <div class="font-serif text-4xl text-emerald-400 font-normal mt-2"><?= $reunited ?></div>
        </div>
        <div class="p-6 bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-xl">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Recovery Rate</span>
            <div class="font-serif text-4xl text-amber-400 font-normal mt-2"><?= $rate ?>%</div>
        </div>
    </div>

    <!-- Detailed Stats Grid -->
    <div class="grid md:grid-cols-2 gap-6">
        <!-- By Category -->
        <div class="p-6 bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-xl">
            <h2 class="font-serif text-lg font-normal text-white mb-4">By Category</h2>
            <div class="space-y-3">
                <?php foreach ($byCategory as $cat): ?>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-[#0b1329] border border-blue-900/30 text-xs">
                        <span class="text-slate-300 font-medium"><?= h($cat['name']) ?></span>
                        <span class="font-bold text-white bg-blue-950 px-2.5 py-0.5 rounded-lg border border-blue-800/40"><?= $cat['count'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- By Location -->
        <div class="p-6 bg-[#101d42] border border-blue-900/40 rounded-2xl shadow-xl">
            <h2 class="font-serif text-lg font-normal text-white mb-4">By Location</h2>
            <div class="space-y-3">
                <?php foreach ($byLocation as $loc): ?>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-[#0b1329] border border-blue-900/30 text-xs">
                        <span class="text-slate-300 font-medium"><?= h($loc['name']) ?></span>
                        <span class="font-bold text-white bg-blue-950 px-2.5 py-0.5 rounded-lg border border-blue-800/40"><?= $loc['count'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php ui_footer(); ?>