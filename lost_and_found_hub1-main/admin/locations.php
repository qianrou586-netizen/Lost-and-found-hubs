<?php
// Admin Locations Management - Dark Theme Console Version
require_once __DIR__ . '/../includes/bootstrap.php';

if (empty($_SESSION['admin_id'])) {
    flash_set('error', 'Please sign in first.');
    redirect('login.php');
}

$adminId = (int) $_SESSION['admin_id'];
$adminAvatar = $pdo->query("SELECT avatar FROM admins WHERE id = {$adminId}")->fetchColumn();

$errors = [];

// မြို့များအားလုံးကို Dropdown ရွေးချယ်နိုင်ရန် ဆွဲယူခြင်း
$cities = $pdo->query("SELECT id, name FROM cities ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('error', 'Session expired. Please try again.');
        redirect('locations.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name    = trim($_POST['name'] ?? '');
        $cityId  = (int) ($_POST['city_id'] ?? 0);
        $address = trim($_POST['address'] ?? '');

        if ($name === '') {
            $errors[] = 'Location name cannot be empty.';
        } elseif ($cityId <= 0) {
            $errors[] = 'Please select a valid city.';
        } else {
            $chk = $pdo->prepare('SELECT id FROM locations WHERE name = ? AND city_id = ?');
            $chk->execute([$name, $cityId]);
            if ($chk->fetch()) {
                $errors[] = 'This location already exists in the selected city.';
            } else {
                // Database schema နှင့်ကိုက်ညီအောင် city_id နှင့် address အား ထည့်သွင်းခြင်း
                $stmt = $pdo->prepare('INSERT INTO locations (city_id, name, address, created_at) VALUES (?, ?, ?, NOW())');
                $stmt->execute([$cityId, $name, $address]);
                flash_set('success', 'Location added successfully.');
                redirect('locations.php');
            }
        }
    }

    if ($action === 'delete') {
        $locId = (int) ($_POST['location_id'] ?? 0);
        $itemCount = $pdo->prepare('SELECT COUNT(*) FROM items WHERE location_id = ?');
        $itemCount->execute([$locId]);
        if ($itemCount->fetchColumn() > 0) {
            flash_set('error', 'Cannot delete this location because items are linked to it.');
        } else {
            $pdo->prepare('DELETE FROM locations WHERE id = ?')->execute([$locId]);
            flash_set('success', 'Location deleted successfully.');
        }
        redirect('locations.php');
    }
}

// မြို့အမည် (city_name) ပါ တစ်ခါတည်း တွဲဖက်ဆွဲယူခြင်း
$locations = $pdo->query('
    SELECT l.*, c.name AS city_name, COUNT(i.id) AS item_count
    FROM locations l
    LEFT JOIN cities c ON l.city_id = c.id
    LEFT JOIN items i ON l.id = i.location_id
    GROUP BY l.id
    ORDER BY c.name ASC, l.name ASC
')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Locations - Admin Console</title>
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
                <a href="locations.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-900/30">
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
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Campus &amp; City Locations Management</h1>
                <p class="text-xs text-slate-400 mt-1">Configure hotspots, bus stops, buildings, and areas monitored for loss incidents.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs space-y-1">
                    <?php foreach ($errors as $err): ?>
                        <p>&bull; <?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                <div class="admin-card p-5 rounded-2xl">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-3">Add Location</h2>
                    <form method="post" class="space-y-3">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="create">
                        
                        <!-- မြို့ရွေးချယ်ရန် Dropdown (မဖြစ်မနေလိုအပ်ချက်) -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">City</label>
                            <select name="city_id" required class="w-full px-4 py-2 text-xs rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-emerald-500">
                                <option value="">-- Select City --</option>
                                <?php foreach ($cities as $ct): ?>
                                    <option value="<?= (int)$ct['id'] ?>"><?= htmlspecialchars($ct['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Location Name</label>
                            <input type="text" name="name" required placeholder="e.g. Junction City, Main Gate" class="w-full px-4 py-2 text-xs rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Address / Detail</label>
                            <textarea name="address" rows="2" placeholder="Road, township or campus info..." class="w-full px-4 py-2 text-xs rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"></textarea>
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white transition">Add Location</button>
                    </form>
                </div>

                <div class="md:col-span-2 admin-card rounded-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead class="bg-slate-900/60 text-slate-400 uppercase text-[10px] tracking-wider border-b border-gray-800">
                                <tr>
                                    <th class="py-3 px-4">Location Name</th>
                                    <th class="py-3 px-4">City</th>
                                    <th class="py-3 px-4">Address</th>
                                    <th class="py-3 px-4">Reported Losses</th>
                                    <th class="py-3 px-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800 text-slate-300">
                                <?php foreach ($locations as $loc): ?>
                                    <tr class="hover:bg-slate-800/40 transition">
                                        <td class="py-3 px-4 font-bold text-white"><?= htmlspecialchars($loc['name']) ?></td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-emerald-400 border border-slate-700">
                                                <?= htmlspecialchars($loc['city_name'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-400 max-w-xs truncate" title="<?= htmlspecialchars($loc['address'] ?? '') ?>">
                                            <?= htmlspecialchars($loc['address'] ?? '-') ?>
                                        </td>
                                        <td class="py-3 px-4 font-semibold text-emerald-400"><?= (int) $loc['item_count'] ?> reports</td>
                                        <td class="py-3 px-4 text-right">
                                            <form method="post" onsubmit="return confirm('Delete this location?');" class="inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="location_id" value="<?= (int) $loc['id'] ?>">
                                                <button type="submit" class="px-2.5 py-1 rounded text-xs text-red-400 hover:bg-red-500/20 border border-red-500/30 transition">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($locations)): ?>
                                    <tr><td colspan="5" class="py-8 text-center text-slate-500">No locations found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>