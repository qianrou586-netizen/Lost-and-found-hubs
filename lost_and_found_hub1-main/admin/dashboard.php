<?php
// Admin Dedicated Portal & Analytics Dashboard
require_once __DIR__ . '/../includes/bootstrap.php';

// အကောင့်မဝင်ထားပါက Login စာမျက်နှာသို့ ပြန်ညွှန်းခြင်း
if (empty($_SESSION['admin_id'])) {
    flash_set('error', 'Please sign in to access the dashboard.');
    redirect('login.php');
}

$adminId = (int)$_SESSION['admin_id'];

// လက်ရှိ Admin ၏ Avatar နှင့် အချက်အလက်များကို ဆွဲထုတ်ခြင်း
$adminData = $pdo->query("SELECT username, email, avatar FROM admins WHERE id = {$adminId} LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// ၁။ အဓိက Stats များ ရေတွက်ခြင်း
$totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalItems = (int) $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
$openItems  = (int) $pdo->query("SELECT COUNT(*) FROM items WHERE status IN ('open','claimed')")->fetchColumn();
$pendingClaims = (int) $pdo->query("SELECT COUNT(*) FROM claims WHERE status = 'pending'")->fetchColumn();

// ၂။ ဘယ်နေရာမှာ ပစ္စည်းအပျောက်အများဆုံးလဲ (Top Lost Hotspots Diagram Data) - မြို့အမည်ပါ တွဲဖက်ဆွဲယူခြင်း
$locationStats = $pdo->query("
    SELECT CONCAT(COALESCE(l.name, 'Other'), IF(c.name IS NOT NULL, CONCAT(' (', c.name, ')'), '')) AS location_name, 
           COUNT(i.id) AS lost_count
    FROM items i
    LEFT JOIN locations l ON i.location_id = l.id
    LEFT JOIN cities c ON l.city_id = c.id
    WHERE i.type = 'lost'
    GROUP BY l.id, c.name
    ORDER BY lost_count DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

$locLabels = [];
$locCounts = [];
foreach ($locationStats as $row) {
    $locLabels[] = $row['location_name'];
    $locCounts[] = (int) $row['lost_count'];
}

// ၃။ ပစ္စည်းအမျိုးအစားအလိုက် ခွဲခြားမှု (Categories breakdown)
$catStats = $pdo->query("
    SELECT COALESCE(c.name, 'General') AS cat_name, COUNT(i.id) AS cat_count
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
    GROUP BY cat_name
    ORDER BY cat_count DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$catLabels = [];
$catCounts = [];
foreach ($catStats as $row) {
    $catLabels[] = $row['cat_name'];
    $catCounts[] = (int) $row['cat_count'];
}

// ၄။ နောက်ဆုံး စာရင်းသွင်းထားသော Users
$recentUsers = $pdo->query(
    "SELECT username, email, is_suspended, created_at FROM users ORDER BY created_at DESC LIMIT 6"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Lost &amp; Found Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a;
        }
        .admin-sidebar {
            background-color: #111827;
            border-right: 1px solid #1f2937;
        }
        .admin-content {
            background-color: #0b1120;
        }
        .admin-card {
            background-color: #1e293b;
            border: 1px solid #334155;
        }
    </style>
</head>
<body class="text-slate-100 flex min-h-screen">

    <!-- Admin Dedicated Sidebar -->
    <aside class="w-64 admin-sidebar flex flex-col justify-between shrink-0 hidden md:flex">
        <div>
            <!-- Admin Brand Logo -->
            <div class="p-6 flex items-center gap-3 border-b border-gray-800">
                <div class="w-10 h-10 rounded-xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center text-lg font-bold border border-emerald-500/30">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h1 class="font-bold text-base tracking-wide text-white leading-tight">Admin Console</h1>
                    <span class="text-[10px] text-emerald-400 font-semibold uppercase tracking-wider">Control Center</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5 text-xs font-semibold">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-900/30">
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

                <!-- Settings & Preferences Section -->
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

        <!-- Admin Profile Bottom Card -->
        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900/80 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-800/60 transition group cursor-pointer" onclick="window.location.href='profile.php'">
                <a href="profile.php" class="flex items-center gap-3 flex-1 overflow-hidden" title="View Profile">
                    <?php if (!empty($adminData['avatar']) && file_exists(__DIR__ . '/../' . $adminData['avatar'])): ?>
                        <img src="../<?= htmlspecialchars($adminData['avatar']) ?>" class="w-9 h-9 rounded-lg object-cover border border-emerald-500/40 shrink-0">
                    <?php else: ?>
                        <div class="w-9 h-9 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0 group-hover:bg-emerald-500 group-hover:text-white transition">
                            <?= strtoupper(substr($adminData['username'] ?? 'A', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="overflow-hidden">
                        <div class="text-xs font-bold text-white truncate group-hover:text-emerald-400 transition flex items-center gap-1.5">
                            <span><?= htmlspecialchars($adminData['username'] ?? 'Admin') ?></span>
                            <i class="fa-solid fa-gear text-[10px] text-slate-500 group-hover:text-emerald-400"></i>
                        </div>
                        <div class="text-[10px] text-slate-400 truncate"><?= htmlspecialchars($adminData['email'] ?? '') ?></div>
                    </div>
                </a>
                <a href="logout.php" title="Sign Out" onclick="event.stopPropagation();" class="text-slate-500 hover:text-red-400 p-2 rounded-lg hover:bg-red-500/10 transition ml-1 shrink-0">
                    <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
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
                <h1 class="text-2xl font-extrabold text-white tracking-tight">System Overview &amp; Analytics</h1>
                <p class="text-xs text-slate-400 mt-1">Real-time reports, item distribution, and loss hotspot analysis.</p>
            </div>

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="users.php" class="admin-card p-5 rounded-2xl hover:border-emerald-500/50 transition block">
                    <div class="flex items-center justify-between text-slate-400 text-xs mb-3">
                        <span class="font-medium">Total Users</span>
                        <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center">
                            <i class="fa-solid fa-users text-xs"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-white"><?= $totalUsers ?></div>
                    <div class="text-[11px] text-blue-400 mt-2 font-medium">Registered accounts &rarr;</div>
                </a>

                <a href="items.php" class="admin-card p-5 rounded-2xl hover:border-emerald-500/50 transition block">
                    <div class="flex items-center justify-between text-slate-400 text-xs mb-3">
                        <span class="font-medium">Total Reports</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                            <i class="fa-solid fa-box-open text-xs"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-white"><?= $totalItems ?></div>
                    <div class="text-[11px] text-emerald-400 mt-2 font-medium">Lost &amp; found posts &rarr;</div>
                </a>

                <a href="items.php?status=open" class="admin-card p-5 rounded-2xl hover:border-emerald-500/50 transition block">
                    <div class="flex items-center justify-between text-slate-400 text-xs mb-3">
                        <span class="font-medium">Active / Open</span>
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                            <i class="fa-solid fa-clock text-xs"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-white"><?= $openItems ?></div>
                    <div class="text-[11px] text-amber-400 mt-2 font-medium">Unresolved cases &rarr;</div>
                </a>

                <a href="claims.php" class="admin-card p-5 rounded-2xl hover:border-emerald-500/50 transition block">
                    <div class="flex items-center justify-between text-slate-400 text-xs mb-3">
                        <span class="font-medium">Pending Claims</span>
                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center">
                            <i class="fa-solid fa-shield-heart text-xs"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-white"><?= $pendingClaims ?></div>
                    <div class="text-[11px] text-purple-400 mt-2 font-medium">Awaiting verification &rarr;</div>
                </a>
            </div>

            <!-- Analytics Diagram Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 admin-card p-6 rounded-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-fire text-amber-500"></i>
                                <span>Lost Items Hotspots</span>
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Campus locations and areas with the highest reported losses</p>
                        </div>
                        <a href="locations.php" class="text-xs text-emerald-400 hover:underline">Manage zones</a>
                    </div>
                    
                    <div class="h-64">
                        <canvas id="locationHotspotChart"></canvas>
                    </div>
                </div>

                <div class="admin-card p-6 rounded-2xl flex flex-col justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2 mb-1">
                            <i class="fa-solid fa-chart-pie text-emerald-400"></i>
                            <span>Category Breakdown</span>
                        </h2>
                        <p class="text-xs text-slate-400">Distribution by item type</p>
                    </div>

                    <div class="h-52 my-auto">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>

            </div>

            <!-- Recent Members Table -->
            <div class="admin-card rounded-2xl overflow-hidden">
                <div class="p-5 border-b border-gray-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider">Recently Registered Users</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Latest community members joining the platform</p>
                    </div>
                    <a href="users.php" class="px-3 py-1.5 rounded-lg text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                        View All Users &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead class="bg-slate-900/60 text-slate-400 uppercase text-[10px] tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-5">Member</th>
                                <th class="py-3 px-5">Email Address</th>
                                <th class="py-3 px-5">Account Status</th>
                                <th class="py-3 px-5">Registered Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 text-slate-300">
                            <?php foreach ($recentUsers as $u): ?>
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-3 px-5 font-semibold text-white"><?= htmlspecialchars($u['username']) ?></td>
                                    <td class="py-3 px-5 text-slate-400"><?= htmlspecialchars($u['email']) ?></td>
                                    <td class="py-3 px-5">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?= (int)$u['is_suspended'] === 1 ? 'bg-red-500/10 text-red-400 border border-red-500/30' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' ?>">
                                            <?= (int) $u['is_suspended'] === 1 ? 'Suspended' : 'Active' ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-5 text-slate-400"><?= htmlspecialchars(substr($u['created_at'], 0, 10)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentUsers)): ?>
                                <tr><td colspan="4" class="py-6 text-center text-slate-500">No member accounts registered yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- Chart.js Scripts -->
    <script>
        const locCtx = document.getElementById('locationHotspotChart').getContext('2d');
        const locLabels = <?= json_encode($locLabels ?: ['No Data']) ?>;
        const locData = <?= json_encode($locCounts ?: [0]) ?>;

        new Chart(locCtx, {
            type: 'bar',
            data: {
                labels: locLabels,
                datasets: [{
                    label: 'Lost Reports',
                    data: locData,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: '#10b981',
                    borderWidth: 1.5,
                    borderRadius: 8,
                    maxBarThickness: 38
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#334155' },
                        ticks: { color: '#94a3b8', stepSize: 1, font: { size: 11 } }
                    }
                }
            }
        });

        const catCtx = document.getElementById('categoryChart').getContext('2d');
        const catLabels = <?= json_encode($catLabels ?: ['No Data']) ?>;
        const catData = <?= json_encode($catCounts ?: [1]) ?>;

        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catData,
                    backgroundColor: [
                        '#10b981', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6', '#64748b'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#94a3b8', boxWidth: 10, font: { size: 10 } }
                    }
                },
                cutout: '70%'
            }
        });
    </script>
</body>
</html>