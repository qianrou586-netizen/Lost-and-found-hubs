<?php
// Admin Claims Review - Dark Theme Console Version
require_once __DIR__ . '/../includes/bootstrap.php';

if (empty($_SESSION['admin_id'])) {
    flash_set('error', 'Please sign in first.');
    redirect('login.php');
}

$adminId = (int) $_SESSION['admin_id'];
$adminAvatar = $pdo->query("SELECT avatar FROM admins WHERE id = {$adminId}")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('error', 'Session expired. Please try again.');
        redirect('claims.php');
    }

    $claimId = (int) ($_POST['claim_id'] ?? 0);
    $actionStatus = $_POST['status'] ?? '';

    if (in_array($actionStatus, ['approved', 'rejected', 'pending'])) {
        $stmt = $pdo->prepare('UPDATE claims SET status = ? WHERE id = ?');
        $stmt->execute([$actionStatus, $claimId]);
        flash_set('success', 'Claim status changed to ' . ucfirst($actionStatus) . '.');
    }
    redirect('claims.php');
}

$stmt = $pdo->query("
    SELECT cl.*, 
           i.title AS item_title,
           finder.username AS finder_username, finder.email AS finder_email,
           claimant.username AS claimant_username, claimant.email AS claimant_email
    FROM claims cl
    JOIN items i ON cl.found_item_id = i.id
    JOIN users finder ON i.user_id = finder.id
    JOIN users claimant ON cl.claimant_user_id = claimant.id
    ORDER BY cl.created_at DESC
");
$claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Claims - Admin Console</title>
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
                <a href="claims.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-900/30">
                    <i class="fa-solid fa-hand-holding-hand w-4"></i>
                    <span>Review Claims</span>
                </a>
                
                <div class="pt-4 pb-2 px-3 text-[10px] uppercase font-bold text-slate-500 tracking-wider">Settings &amp; Preferences</div>
                
                <!-- Admin Staff Tab ထည့်သွင်းထားသည် -->
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
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Claims Verification &amp; Disputes</h1>
                <p class="text-xs text-slate-400 mt-1">Review proof messages and ownership disputes between finders and claimants.</p>
            </div>

            <div class="admin-card rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead class="bg-slate-900/60 text-slate-400 uppercase text-[10px] tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Item</th>
                                <th class="py-3 px-4">Finder</th>
                                <th class="py-3 px-4">Claimant</th>
                                <th class="py-3 px-4">Proof Message</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Moderation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 text-slate-300">
                            <?php foreach ($claims as $c): ?>
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-white"><?= htmlspecialchars($c['item_title']) ?></div>
                                        <span class="text-[10px] text-slate-400">Found Item #<?= (int)$c['found_item_id'] ?></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-white"><?= htmlspecialchars($c['finder_username']) ?></div>
                                        <div class="text-[10px] text-slate-400"><?= htmlspecialchars($c['finder_email']) ?></div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-white"><?= htmlspecialchars($c['claimant_username']) ?></div>
                                        <div class="text-[10px] text-slate-400"><?= htmlspecialchars($c['claimant_email']) ?></div>
                                    </td>
                                    <td class="py-3 px-4 max-w-xs">
                                        <div class="p-2 bg-slate-900/80 rounded border border-slate-700 text-slate-300 text-xs">
                                            <?= nl2br(htmlspecialchars($c['message'] ?? '-')) ?>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?php
                                            if ($c['status'] === 'approved') echo 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                                            elseif ($c['status'] === 'rejected') echo 'bg-red-500/10 text-red-400 border border-red-500/20';
                                            else echo 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
                                        ?>">
                                            <?= htmlspecialchars($c['status']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <form method="post" class="inline-flex gap-1.5 justify-end">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="claim_id" value="<?= (int)$c['id'] ?>">
                                            <?php if ($c['status'] !== 'approved'): ?>
                                                <button type="submit" name="status" value="approved" class="px-2.5 py-1 rounded text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/20 transition">Approve</button>
                                            <?php endif; ?>
                                            <?php if ($c['status'] !== 'rejected'): ?>
                                                <button type="submit" name="status" value="rejected" class="px-2.5 py-1 rounded text-xs bg-red-500/10 text-red-400 border border-red-500/30 hover:bg-red-500/20 transition">Reject</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($claims)): ?>
                                <tr><td colspan="6" class="py-8 text-center text-slate-500">No claim disputes found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>