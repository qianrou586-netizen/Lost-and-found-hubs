<?php
// includes/ui.php
// All-Dark Midnight Navy & Cobalt Theme (index.php နှင့် တစ်သားတည်းဖြစ်သော ပုံစံ)

require_once __DIR__ . '/lang.php';
function ui_header(string $title = 'Lost & Found Hub', string $activeTab = 'home', string $maxWidth = 'max-w-6xl') {
    global $pdo;
    $role = current_role();
    $userId = (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0);
    
    // User Profile Avatar ပုံနှင့် Username ရယူခြင်း
    $navAvatar = null;
    $usernameDisplay = $_SESSION['username'] ?? 'User';

    if ($role === 'user' && $userId > 0 && isset($pdo)) {
        $uStmt = $pdo->prepare('SELECT username FROM users WHERE id = ?');
        $uStmt->execute([$userId]);
        $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);
        if ($uRow && !empty($uRow['username'])) {
            $usernameDisplay = $uRow['username'];
        }

        $pStmt = $pdo->prepare('SELECT avatar FROM user_profiles WHERE user_id = ?');
        $pStmt->execute([$userId]);
        $pRow = $pStmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($pRow['avatar'])) {
            $diskPath = __DIR__ . '/../' . ltrim($pRow['avatar'], '/');
            if (file_exists($diskPath)) {
                $navAvatar = '../' . ltrim($pRow['avatar'], '/');
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= h($title) ?> - Lost &amp; Found</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <script>
            // Tailwind CSS Custom Configuration (All-Dark Midnight Palette A)
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            cobalt: {
                                DEFAULT: '#2563eb',
                                hover: '#1d4ed8',
                                light: '#60a5fa',
                                bg: '#1e3a8a'
                            },
                            forest: { // နဂို bg-forest class များနှင့် ကိုက်ညီစေရန်
                                DEFAULT: '#2563eb',
                                hover: '#1d4ed8',
                                light: '#60a5fa',
                                dark: '#0b1329'
                            },
                            night: {
                                base: '#0b1329',   // Page Background
                                surface: '#101d42',// Card Surface
                                input: '#16285a',  // Input Box Background
                                border: '#1e3a8a'  // Subtle Dark Blue Border
                            },
                            accentCrimson: '#ef4444',
                            accentEmerald: '#10b981',
                            accentAmber: '#f59e0b',
                            ink: '#f8fafc'
                        },
                        fontFamily: {
                            serif: ['Newsreader', 'Georgia', 'serif'],
                            sans: ['Plus Jakarta Sans', 'sans-serif'],
                        }
                    }
                }
            }
        </script>
        <style>
            /* တစ်ပြင်လုံးကို index.php ၏ Dark Midnight Navy Background အဖြစ် သတ်မှတ်ခြင်း */
            body {
                background-color: #0b1329;
                color: #f1f5f9;
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
            /* Pill Input Box ပုံစံသတ်မှတ်ချက် (Dark Mode) */
            .form-input-pill {
                background-color: #111e47;
                border: 1px solid rgba(59, 130, 246, 0.3);
                border-radius: 9999px;
                padding: 0.625rem 1.25rem;
                outline: none;
                transition: all 0.2s;
                color: #ffffff;
            }
            .form-input-pill::placeholder {
                color: #94a3b8;
            }
            .form-input-pill:focus {
                background-color: #16285a;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
            }
            /* Dark Midnight Surface Card Design */
            .card-styled {
                background-color: #111e47;
                border: 1px solid rgba(59, 130, 246, 0.25);
                border-radius: 1.25rem;
                box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
                backdrop-filter: blur(8px);
            }
        </style>
    </head>
    <body class="min-h-screen flex flex-col bg-[#0b1329]">

        <!-- Header Navbar (Dark Midnight Navy Header) -->
        <header class="bg-[#0b1329]/95 backdrop-blur-md border-b border-blue-900/40 sticky top-0 z-50 transition-colors">
            <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
                
                <!-- Logo -->
                <a href="index.php" class="flex items-center space-x-2.5 font-serif text-xl font-semibold text-white">
                    <span class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center text-white text-sm shadow-md shadow-blue-900/50">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <span class="tracking-tight">Lost &amp; Found</span>
                </a>
            <!-- Navigation Tabs -->
            <nav class="hidden md:flex items-center space-x-6 text-sm font-semibold text-slate-300">
                <a href="items.php" class="<?= $activeTab === 'browse' ? 'text-blue-400 font-bold' : 'hover:text-white' ?> transition"><?= __('nav_browse') ?></a>
                <a href="report.php" class="<?= $activeTab === 'report' ? 'text-blue-400 font-bold' : 'hover:text-white' ?> transition"><?= __('nav_report') ?></a>
                <a href="matches.php" class="<?= $activeTab === 'matches' ? 'text-blue-400 font-bold' : 'hover:text-white' ?> transition"><?= __('nav_matches')?></a>
                <a href="my_reports.php" class="<?= $activeTab === 'myitems' ? 'text-blue-400 font-bold' : 'hover:text-white' ?> transition"><?= __('nav_my_items')?></a>
                <a href="dashboard.php" class="<?= $activeTab === 'stats' ? 'text-blue-400 font-bold' : 'hover:text-white' ?> transition"><?= __('nav_stats')?></a>
            </nav>
            <!-- Language Switcher -->
            <?php $currLang = $_SESSION['lang'] ?? 'en'; ?>
            <div class="flex items-center space-x-1 text-xs font-semibold">
                <a href="?lang=en" class="px-2 py-1 rounded <?= $currLang === 'en' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white' ?>">EN</a>
                <span class="text-gray-600">|</span>
                <a href="?lang=mm" class="px-2 py-1 rounded <?= $currLang === 'mm' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white' ?>">MM</a>
            </div>

                <!-- Profile နှင့် User Actions -->
                <div class="flex items-center space-x-3">
                    <?php if ($role === 'user'): ?>
                        <a href="profile.php" class="flex items-center space-x-2.5 text-sm font-semibold text-slate-200 hover:text-blue-400 transition">
                            <?php if ($navAvatar): ?>
                                <img src="<?= h($navAvatar) ?>?v=<?= time() ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover border border-blue-500/40 shadow-xs">
                            <?php else: ?>
                                <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                    <?= strtoupper(substr($usernameDisplay, 0, 1)) ?>
                                </span>
                            <?php endif; ?>
                            <span class="hidden sm:inline"><?= h($usernameDisplay) ?></span>
                        </a>
                        <form method="post" action="logout.php" class="inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="text-xs font-semibold text-slate-400 hover:text-rose-400 ml-2 transition">Sign Out</button>
                        </form>
                    <?php else: ?>
                        <a href="login.php" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-xs transition">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <main class="flex-grow flex flex-col">
    <?php
}

   function ui_footer() { 
    ?> 
        </main>
    <!-- Footer အပိုင်း (Dark Midnight Footer) -->
    <footer class="bg-[#080e1e] text-slate-400 border-t border-blue-900/40 py-12 transition-colors">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8 pb-8 border-b border-blue-900/30">
                
                <div class="md:col-span-2 space-y-3">
                    <a href="index.php" class="flex items-center space-x-2.5 font-serif text-xl font-semibold text-white">
                        <span class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center text-white text-sm shadow-md shadow-blue-900/40">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <span class="tracking-tight">Lost &amp; Found Hub</span>
                    </a>
                    <p class="text-xs text-slate-400 max-w-sm leading-relaxed">
                        A smart community-driven platform for reporting, locating, and recovering lost personal belongings across universities and city zones.
                    </p>
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-950/60 border border-emerald-500/30 text-[11px] text-emerald-400 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Matching Engine Active</span>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-200 mb-3">Explore</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="items.php" class="hover:text-blue-400 transition">Browse All Items</a></li>
                        <li><a href="report.php?type=lost" class="hover:text-rose-400 transition">Report Lost Item</a></li>
                        <li><a href="report.php?type=found" class="hover:text-emerald-400 transition">Report Found Item</a></li>
                        <li><a href="matches.php" class="hover:text-blue-400 transition">Smart Matches</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-200 mb-3">Management</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="my_reports.php" class="hover:text-blue-400 transition">My Item Reports</a></li>
                        <li><a href="dashboard.php" class="hover:text-blue-400 transition">Platform Statistics</a></li>
                        <li><a href="profile.php" class="hover:text-blue-400 transition">Account Profile</a></li>
                        <li><a href="../admin/login.php" class="text-slate-500 hover:text-slate-300 transition">Admin Portal</a></li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-3">
                <p>&copy; <?= date('Y') ?> Lost &amp; Found Hub. All rights reserved.</p>
                <p class="flex items-center gap-1.5">
                    <span>All-Dark Midnight Architecture</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    <span>Palette A</span>
                </p>
            </div>
        </div>
    </footer>

    <!-- ========================================== -->
    <!-- Floating AI Chatbot Widget (Bottom Right) -->
    <!-- ========================================== -->
    <div id="ai-chat-widget" class="fixed bottom-6 right-6 z-50 font-sans">
        <!-- Chat Box Window (Initially Hidden) -->
        <div id="ai-chat-box" class="hidden mb-4 w-80 sm:w-96 bg-[#101d42] border border-blue-500/30 rounded-2xl shadow-2xl overflow-hidden flex flex-col transition-all duration-300">
            <!-- Chat Header -->
              <div class="bg-gradient-to-r from-blue-700 to-blue-600 px-4 py-3 flex items-center justify-between text-white">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-full bg-white/10 border border-white/20 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold tracking-wide">Hub AI Assistant</h3>
                        <span class="text-[10px] text-blue-200 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Online
                        </span>
                    </div>
                </div>
                <button onclick="toggleChatBox()" class="text-white/70 hover:text-white transition text-sm p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Chat Body / Messages Area -->
            <div id="ai-chat-messages" class="p-4 h-72 overflow-y-auto space-y-3 text-xs text-slate-300 bg-[#0b1329]/60">
                <div class="flex items-start space-x-2">
                    <div class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-white text-[10px] shrink-0 mt-0.5">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <div class="bg-[#16285a] border border-blue-900/40 p-3 rounded-xl rounded-tl-none max-w-[85%] text-slate-200 leading-relaxed">
                        မင်္ဂလာပါရှင်။ Lost &amp; Found Hub AI Assistant မှ ကြိုဆိုပါတယ်။ ပစ္စည်းရှာဖွေရန် သို့မဟုတ် ပစ္စည်းတိုင်ကြားရန် မည်သို့ကူညီပေးရမလဲ?
                    </div>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="p-2 bg-[#101d42] border-t border-blue-900/30 flex flex-wrap gap-1.5">
                <a href="items.php" class="px-2.5 py-1 rounded-full bg-blue-600/30 border border-blue-500/30 text-blue-300 text-[10px] font-medium hover:bg-blue-600 hover:text-white transition">🔍 ပစ္စည်းများကြည့်မည်</a>
                <a href="report.php" class="px-2.5 py-1 rounded-full bg-blue-600/30 border border-blue-500/30 text-blue-300 text-[10px] font-medium hover:bg-blue-600 hover:text-white transition">📢 ပျောက်ဆုံးကြောင်းတင်ရန်</a>
                <a href="matches.php" class="px-2.5 py-1 rounded-full bg-blue-600/30 border border-blue-500/30 text-blue-300 text-[10px] font-medium hover:bg-blue-600 hover:text-white transition">⚡ Smart Matches</a>
            </div>

            <!-- Chat Input Form -->
            <div class="p-3 bg-[#101d42] border-t border-blue-900/40 flex items-center space-x-2">
                <input type="text" id="ai-chat-input" placeholder="မေးခွန်းတစ်ခုခု ရေးပါ..." class="flex-1 bg-[#16285a] border border-blue-900/50 rounded-full px-3.5 py-2 text-xs text-white focus:outline-none focus:border-blue-500 placeholder-slate-500">
                <button onclick="sendUserMessage()" class="w-8 h-8 rounded-full bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center text-xs transition shadow-md">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </div>

        <!-- Floating Toggle Button -->
        <button onclick="toggleChatBox()" id="ai-chat-toggle-btn" class="w-14 h-14 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white shadow-2xl flex items-center justify-center text-xl transition-all duration-300 hover:scale-105 group relative">
            <i class="fa-solid fa-robot group-hover:rotate-12 transition-transform"></i>
            <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-[#0b1329] animate-pulse"></span>
        </button>
    </div>

    <!-- Chatbot JavaScript Handler -->
    <script>
        function toggleChatBox() {
            const box = document.
                 getElementById('ai-chat-box');
            const btn = document.getElementById('ai-chat-toggle-btn');
            if (box.classList.contains('hidden')) {
                box.classList.remove('hidden');
                btn.classList.add('scale-90');
            } else {
                box.classList.add('hidden');
                btn.classList.remove('scale-90');
            }
        }

        function sendUserMessage() {
            const input = document.getElementById('ai-chat-input');
            const msgArea = document.getElementById('ai-chat-messages');
            const text = input.value.trim();
            
            if (!text) return;

            // Append User Message
            msgArea.innerHTML += `
                <div class="flex items-start justify-end space-x-2">
                    <div class="bg-blue-600 text-white p-3 rounded-xl rounded-tr-none max-w-[85%] text-xs leading-relaxed shadow-sm">
                        ${escapeHtml(text)}
                    </div>
                </div>
            `;
            input.value = '';
            msgArea.scrollTop = msgArea.scrollHeight;

            // Simulate AI Reply after 600ms
            setTimeout(() => {
                msgArea.innerHTML += `
                    <div class="flex items-start space-x-2">
                        <div class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-white text-[10px] shrink-0 mt-0.5">
                            <i class="fa-solid fa-robot"></i>
                        </div>
                        <div class="bg-[#16285a] border border-blue-900/40 p-3 rounded-xl rounded-tl-none max-w-[85%] text-slate-200 leading-relaxed">
                            ကျေးဇူးတင်ပါတယ်။ ပစ္စည်းအချက်အလက်များကို <b>Browse Items</b> သို့မဟုတ် <b>Smart Matches</b> စာမျက်နှာများတွင် အသေးစိတ် ဝင်ရောက်ရှာဖွေနိုင်ပါတယ်ရှင်။
                        </div>
                    </div>
                `;
                msgArea.scrollTop = msgArea.scrollHeight;
            }, 600);
        }

        function escapeHtml(text) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Allow Enter key to send message
        document.getElementById('ai-chat-input')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendUserMessage();
            }
        });
    </script>
</body>
</html>
<?php
}