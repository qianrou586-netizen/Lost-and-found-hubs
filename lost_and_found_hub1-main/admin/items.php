<?php
// Admin Items Management - Dark Theme Console Version
require_once __DIR__ . '/../includes/bootstrap.php';

if (empty($_SESSION['admin_id'])) {
    flash_set('error', 'Please sign in first.');
    redirect('login.php');
}

$adminId = (int) $_SESSION['admin_id'];
$adminAvatar = $pdo->query("SELECT avatar FROM admins WHERE id = {$adminId}")->fetchColumn();

$errors = [];

// Filter ရွေးချယ်နိုင်ရန် မြို့များ စာရင်းဆွဲယူခြင်း
$cities = $pdo->query("SELECT id, name FROM cities ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('error', 'Session expired. Please try again.');
        redirect('items.php');
    }

    $action = $_POST['action'] ?? '';
    $itemId = (int) ($_POST['item_id'] ?? 0);

    if ($action === 'change_status') {
        $newStatus = $_POST['status'] ?? 'open';
        if (in_array($newStatus, ['open', 'claimed', 'resolved'])) {
            $stmt = $pdo->prepare('UPDATE items SET status = ? WHERE id = ?');
            $stmt->execute([$newStatus, $itemId]);
            flash_set('success', 'Item status updated to ' . ucfirst($newStatus) . '.');
        }
    } elseif ($action === 'delete') {
        try {
            $pdo->prepare('DELETE FROM matches WHERE lost_item_id = ? OR found_item_id = ?')->execute([$itemId, $itemId]);
        } catch (Exception $e) {}

        try {
            $pdo->prepare('DELETE FROM claims WHERE found_item_id = ?')->execute([$itemId]);
        } catch (Exception $e) {}

        $pdo->prepare('DELETE FROM items WHERE id = ?')->execute([$itemId]);
        flash_set('success', 'Item removed permanently by administrator.');
    }
    redirect('items.php');
}

$filterType   = $_GET['type'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterCity   = (int) ($_GET['city_id'] ?? 0);
$searchQuery  = trim($_GET['q'] ?? '');

// cities table ကို join တွဲယူခြင်း
$sql = "
    SELECT i.*, u.username, u.email AS reporter_email, 
           c.name AS category_name, 
           l.name AS location_name,
           ci.name AS city_name
    FROM items i
    JOIN users u ON i.user_id = u.id
    LEFT JOIN categories c ON i.category_id = c.id
    LEFT JOIN locations l ON i.location_id = l.id
    LEFT JOIN cities ci ON l.city_id = ci.id
    WHERE 1=1
";
$params = [];

if ($filterType && in_array($filterType, ['lost', 'found'])) {
    $sql .= " AND i.type = ?";
    $params[] = $filterType;
}
if ($filterStatus && in_array($filterStatus, ['open', 'claimed', 'resolved'])) {
    $sql .= " AND i.status = ?";
    $params[] = $filterStatus;
}
if ($filterCity > 0) {
    $sql .= " AND ci.id = ?";
    $params[] = $filterCity;
}
if ($searchQuery !== '') {
    $sql .= " AND (i.title LIKE ? OR i.description LIKE ? OR u.username LIKE ?)";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
}

$sql .= " ORDER BY i.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items - Admin Console</title>
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
                <a href="items.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-900/30">
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
                
                <a href="manage_admins.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition">
                    <i class="fa-solid fa-user-shield w-4"></i>
                    <span>Admin Staff</span>
                </a>
                <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition">
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
                    <?php if (!empty($adminAvatar) && file_exists(__DIR__ . '/../' . $adminAvatar)): ?>
                        <img src="../<?= htmlspecialchars($adminAvatar) ?>" class="w-9 h-9 rounded-lg object-cover border border-emerald-500/40 shrink-0">
                    <?php else: ?>
                        <div class="w-9 h-9 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0 group-hover:bg-emerald-500 group-hover:text-white transition">
                            <?= strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="overflow-hidden">
                        <div class="text-xs font-bold text-white truncate group-hover:text-emerald-400 transition flex items-center gap-1.5">
                            <span><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
                            <i class="fa-solid fa-gear text-[10px] text-slate-500 group-hover:text-emerald-400"></i>
                        </div>
                        <div class="text-[10px] text-slate-400 truncate"><?= htmlspecialchars($_SESSION['admin_email'] ?? '') ?></div>
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

        <div class="p-6 md:p-8 space-y-6 max-w-7xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Reported Items Management</h1>
                <p class="text-xs text-slate-400 mt-1">Review, filter, update statuses, or permanently moderate item posts.</p>
            </div>

            <div class="admin-card p-4 rounded-2xl">
                <form method="get" class="flex flex-wrap items-center gap-3">
                    <input type="text" name="q" placeholder="Search by title, description or username..." value="<?= htmlspecialchars($searchQuery) ?>" class="px-4 py-2 text-xs rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 flex-1 min-w-[200px]">
                    
                    <select name="type" class="px-4 py-2 text-xs rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-emerald-500">
                        <option value="">All Types</option>
                        <option value="lost" <?= $filterType === 'lost' ? 'selected' : '' ?>>Lost Only</option>
                        <option value="found" <?= $filterType === 'found' ? 'selected' : '' ?>>Found Only</option>
                    </select>

                    <select name="status" class="px-4 py-2 text-xs rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-emerald-500">
                        <option value="">All Statuses</option>
                        <option value="open" <?= $filterStatus === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="claimed" <?= $filterStatus === 'claimed' ? 'selected' : '' ?>>Claimed</option>
                        <option value="resolved" <?= $filterStatus === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    </select>

                    <!-- မြို့အလိုက် စစ်ထုတ်နိုင်ရန် City Filter ထည့်သွင်းခြင်း -->
                    <select name="city_id" class="px-4 py-2 text-xs rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-emerald-500">
                        <option value="0">All Cities</option>
                        <?php foreach ($cities as $ct): ?>
                            <option value="<?= (int)$ct['id'] ?>" <?= $filterCity === (int)$ct['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ct['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white transition">Filter</button>
                    <?php if ($filterType || $filterStatus || $filterCity || $searchQuery): ?>
                        <a href="items.php" class="text-xs text-slate-400 hover:text-white underline self-center ml-2">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="admin-card rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead class="bg-slate-900/60 text-slate-400 uppercase text-[10px] tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Item Details</th>
                                <th class="py-3 px-4">Type</th>
                                <th class="py-3 px-4">Reported By</th>
                                <th class="py-3 px-4">Location &amp; Date</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Moderation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 text-slate-300">
                            <?php foreach ($items as $item): ?>
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-white"><?= htmlspecialchars($item['title']) ?></div>
                                        <div class="text-[10px] text-slate-400"><?= htmlspecialchars($item['category_name'] ?? 'General') ?></div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $item['type'] === 'lost' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' ?>">
                                            <?= htmlspecialchars($item['type']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-white"><?= htmlspecialchars($item['username']) ?></div>
                                        <div class="text-[10px] text-slate-400"><?= htmlspecialchars($item['reporter_email']) ?></div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-white">
                                            <?= htmlspecialchars($item['location_name'] ?? '-') ?>
                                            <?php if (!empty($item['city_name'])): ?>
                                                <span class="text-slate-400 text-[11px] font-normal">(<?= htmlspecialchars($item['city_name']) ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-[10px] text-slate-400"><?= date('M d, Y', strtotime($item['created_at'])) ?></div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <form method="post" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="change_status">
                                            <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                                            <select name="status" onchange="this.form.submit()" class="text-[11px] font-semibold py-1 px-2 rounded-lg bg-slate-900 border border-slate-700 text-white">
                                                <option value="open" <?= $item['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                                                <option value="claimed" <?= $item['status'] === 'claimed' ? 'selected' : '' ?>>Claimed</option>
                                                <option value="resolved" <?= $item['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <form method="post" onsubmit="return confirm('Permanently remove this report?');" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-medium text-red-400 hover:bg-red-500/20 border border-red-500/30 transition">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                                <tr><td colspan="6" class="py-8 text-center text-slate-500">No reported items match your criteria.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>