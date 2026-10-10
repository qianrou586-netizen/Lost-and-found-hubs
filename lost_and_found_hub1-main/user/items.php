<?php
// user/items.php
// Browse Items စာမျက်နှာ (All-Dark Midnight Theme - Reset Button Removed)
require_once __DIR__ . '/../includes/bootstrap.php';

// URL Filter Parameters ရယူခြင်း
$typeFilter =$_GET['type'] ?? 'all';
$catFilter  = (int)($_GET['category_id'] ?? 0);
$cityFilter = (int)($_GET['city_id'] ?? 0);
$locFilter  = (int)($_GET['location_id'] ?? 0);
$search     = trim($_GET['q'] ?? '');

// Filter Dropdown အတွက် Categories, Cities နှင့် Locations စာရင်းများကို ဆွဲယူခြင်း
$categories =$pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$cities     =$pdo->query("SELECT id, name FROM cities ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$locations  =$pdo->query("SELECT id, city_id, name FROM locations ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// ရွေးချယ်ထားသော City နှင့် Location အမည်များကို ရှာဖွေခြင်း
$selectedCityName = '';
if ($cityFilter > 0) {
    foreach ($cities as$c) {
        if ((int)$c['id'] ===$cityFilter) {
            $selectedCityName =$c['name'];
            break;
        }
    }
}

$selectedLocName = '';
if ($locFilter > 0) {
    foreach ($locations as$l) {
        if ((int)$l['id'] ===$locFilter) {
            $selectedLocName =$l['name'];
            break;
        }
    }
}

// Items စာရင်းနှင့်အတူ ပိုင်ရှင်အချက်အလက်၊ avatar နှင့် metadata များ ချိတ်ဆက်ဆွဲယူခြင်း
$sql = "SELECT i.*, 
               c.name AS category_name, 
               l.name AS location_name,
               ci.name AS city_name,
               u.username AS owner_username,
               up.avatar AS owner_avatar,
               up.phone AS owner_phone
        FROM items i 
        JOIN categories c ON c.id = i.category_id 
        JOIN locations l ON l.id = i.location_id 
        LEFT JOIN cities ci ON ci.id = l.city_id
        LEFT JOIN users u ON u.id = i.user_id
        LEFT JOIN user_profiles up ON up.user_id = i.user_id
        WHERE 1=1";
$params = [];

if ($typeFilter !== 'all' && in_array($typeFilter, ['lost', 'found'], true)) {$sql .= " AND i.type = ?";
    $params[] =$typeFilter;
}
if ($catFilter > 0) {$sql .= " AND i.category_id = ?";
    $params[] =$catFilter;
}
if ($cityFilter > 0) {$sql .= " AND ci.id = ?";
    $params[] =$cityFilter;
}
if ($locFilter > 0) {$sql .= " AND i.location_id = ?";
    $params[] =$locFilter;
}
if ($search !== '') {$sql .= " AND (i.title LIKE ? OR i.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY i.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items =$stmt->fetchAll(PDO::FETCH_ASSOC);

ui_header('Browse Items', 'browse');
?>

<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold tracking-widest uppercase text-blue-400 bg-blue-950/80 px-2.5 py-1 rounded-full border border-blue-800/50">Live Registry</span>
            <h1 class="font-serif text-3xl md:text-4xl font-normal text-white mt-2">Browse Items</h1>
            <p class="text-xs text-slate-400 mt-1">Explore verified lost &amp; found belongings across regions.</p>
        </div>
        <div class="text-xs text-slate-300 bg-[#101d42] px-3 py-1.5 rounded-xl border border-blue-900/40 shadow-xs inline-flex items-center gap-1.5 self-start md:self-auto">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span><strong class="text-white"><?= count($items) ?></strong> items available</span>
        </div>
    </div>

    <!-- Filter Control Bar (Reset ခလုတ်မပါဝင်တော့သော သပ်ရပ်သည့် Filter Bar) -->
    <div class="p-4 mb-8 bg-[#101d42] rounded-2xl shadow-xl border border-blue-900/40 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
        
        <!-- Type Switcher -->
        <div class="inline-flex bg-[#0b1329] p-1 rounded-xl text-xs font-semibold text-slate-400 shrink-0 self-start lg:self-auto border border-blue-900/30">
            <a href="?<?= http_build_query(array_merge($_GET, ['type' => 'all'])) ?>" 
               class="px-4 py-2 rounded-lg transition <?= $typeFilter === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'hover:text-white' ?>">All</a>
            <a href="?<?= http_build_query(array_merge($_GET, ['type' => 'lost'])) ?>" 
               class="px-4 py-2 rounded-lg transition <?= $typeFilter === 'lost' ? 'bg-rose-600 text-white shadow-xs' : 'hover:text-white' ?>">Lost</a>
            <a href="?<?= http_build_query(array_merge($_GET, ['type' => 'found'])) ?>" 
               class="px-4 py-2 rounded-lg transition <?= $typeFilter === 'found' ? 'bg-emerald-600 text-white shadow-xs' : 'hover:text-white' ?>">Found</a>
        </div>

        <!-- Filter Inputs Container -->
        <form id="filterForm" method="get" class="flex flex-wrap items-center gap-2.5 flex-1 lg:justify-end">
            <input type="hidden" name="type" value="<?= h($typeFilter) ?>">

            <!-- Category Dropdown -->
            <div class="relative min-w-[140px] flex-1 sm:flex-none">
                <select name="category_id" onchange="this.form.submit()" class="form-input-pill w-full text-xs cursor-pointer appearance-none pr-8">
                    <option value="0" class="bg-[#101d42] text-slate-400">All Categories</option>
                    <?php foreach ($categories as$c): ?>
                        <option value="<?= $c['id'] ?>" class="bg-[#101d42] text-white" <?= $catFilter == $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3 top-3.5 text-slate-400 text-[10px] pointer-events-none"></i>
            </div>

            <!-- City Search Bar Input -->
            <div class="relative min-w-[140px] flex-1 sm:flex-none">
                <input type="hidden" name="city_id" id="filter_city_id" value="<?= $cityFilter > 0 ?$cityFilter : '' ?>">
                <div class="relative">
                    <input 
                        type="text" 
                        id="filter_city_input" 
                        placeholder="Search City..." 
                        value="<?= h($selectedCityName) ?>"
                        autocomplete="off"
                        class="form-input-pill w-full text-xs pl-8 pr-7 py-2.5"
                        onfocus="showFilterCityDropdown()"
                        oninput="filterCityList(this.value)"
                    >
                    <i class="fa-solid fa-city absolute left-3 top-3 text-slate-400 text-[11px] pointer-events-none"></i>
                    <button type="button" id="clear_city_btn" onclick="clearCityFilter()" class="<?= empty($selectedCityName) ? 'hidden' : '' ?> absolute right-2.5 top-2.5 text-slate-400 hover:text-white text-xs">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>

                <div id="city_dropdown_menu" class="hidden absolute left-0 top-full mt-1.5 w-48 bg-[#0e1938] border border-blue-900/50 rounded-xl shadow-2xl max-h-56 overflow-y-auto z-50 divide-y divide-blue-950">
                </div>
            </div>

            <!-- Location Search Bar Input -->
            <div class="relative min-w-[150px] flex-1 sm:flex-none">
                <input type="hidden" name="location_id" id="filter_loc_id" value="<?= $locFilter > 0 ?$locFilter : '' ?>">
                <div class="relative">
                    <input 
                        type="text" 
                        id="filter_loc_input" 
                        placeholder="Search Location..." 
                        value="<?= h($selectedLocName) ?>"
                        autocomplete="off"
                        class="form-input-pill w-full text-xs pl-8 pr-7 py-2.5"
                        onfocus="showFilterLocDropdown()"
                        oninput="filterLocList(this.value)"
                    >
                    <i class="fa-solid fa-location-dot absolute left-3 top-3 text-slate-400 text-[11px] pointer-events-none"></i>
                    <button type="button" id="clear_loc_btn" onclick="clearLocFilter()" class="<?= empty($selectedLocName) ? 'hidden' : '' ?> absolute right-2.5 top-2.5 text-slate-400 hover:text-white text-xs">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>

                <div id="loc_dropdown_menu" class="hidden absolute left-0 top-full mt-1.5 w-60 bg-[#0e1938] border border-blue-900/50 rounded-xl shadow-2xl max-h-56 overflow-y-auto z-50 divide-y divide-blue-950">
                </div>
            </div>

            <!-- General Keyword Search -->
            <div class="relative min-w-[150px] flex-1 sm:flex-none">
                <input type="text" name="q" value="<?= h($search) ?>" placeholder="Keywords..." class="form-input-pill w-full text-xs pl-8 pr-4 py-2.5">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-[11px] pointer-events-none"></i>
            </div>
        </form>
    </div>

    <!-- Empty State -->
    <?php if (empty($items)): ?>
        <div class="text-center py-20 bg-[#101d42] rounded-2xl border border-blue-900/40 p-8 shadow-xl">
            <div class="w-12 h-12 rounded-full bg-blue-950/60 text-blue-400 flex items-center justify-center mx-auto mb-4 border border-blue-800/40">
                <i class="fa-solid fa-magnifying-glass text-xl"></i>
            </div>
            <p class="text-white font-semibold text-sm">No items match your filters</p>
            <p class="text-slate-400 text-xs mt-1">Try resetting the filters or searching for different keywords.</p>
            <a href="items.php" class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 text-xs font-semibold transition shadow-xs">Clear All Filters</a>
        </div>
    <?php else: ?>
        <!-- Data Cards Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($items as$item): 
                $actionLink = ($item['type'] === 'found') 
                    ? "claim.php?item_id=" . (int)$item['id'] 
                    : "item_detail.php?id=" . (int)$item['id'];
            ?>
                <div class="overflow-hidden flex flex-col justify-between bg-[#101d42] shadow-xl hover:shadow-2xl transition-all duration-200 hover:-translate-y-1 group rounded-2xl border border-blue-900/40">
                    
                    <?php 
                    $hasPhoto = !empty($item['photo_path']) && file_exists(__DIR__ . '/../' . ltrim($item['photo_path'], '/'));
                    if ($hasPhoto): 
                    ?>
                        <a href="<?= $actionLink ?>" class="block w-full h-48 bg-[#0b1329] overflow-hidden border-b border-blue-900/40 relative">
                            <img src="../<?= h(ltrim($item['photo_path'], '/')) ?>?v=<?= time() ?>" alt="<?= h($item['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <span class="absolute top-3 right-3 bg-slate-950/80 backdrop-blur-md text-white text-[10px] font-mono px-2.5 py-0.5 rounded-full font-semibold border border-blue-900/40">
                                #<?= str_pad((string)$item['id'], 4, '0', STR_PAD_LEFT) ?>
                            </span>
                        </a>
                    <?php endif; ?>

                    <div class="p-5 flex-grow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-1.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $item['type'] === 'lost' ? 'bg-rose-950/80 text-rose-400 border border-rose-800/50' : 'bg-emerald-950/80 text-emerald-400 border border-emerald-800/50' ?>">
                                    <?= h($item['type']) ?>
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider <?= ($item['status'] ?? 'open') === 'open' ? 'bg-blue-950/80 text-blue-400 border border-blue-800/50' : 'bg-slate-800 text-slate-300' ?>">
                                    <?= h($item['status'] ?? 'open') ?>
                                </span>
                            </div>
                            <span class="text-xs text-slate-400 font-medium"><?= h($item['category_name']) ?></span>
                        </div>

                        <a href="<?= $actionLink ?>" class="block font-serif text-lg font-normal text-white mb-1 group-hover:text-blue-400 transition">
                            <?= h($item['title']) ?>
                        </a>
                        <p class="text-xs text-slate-300 line-clamp-2 mb-3 leading-relaxed"><?= h($item['description'] ?? 'No additional description provided.') ?></p>
                        
                        <div class="space-y-1.5 pt-2 border-t border-blue-900/30 text-[11px] text-slate-400">
                            <?php if (!empty($item['specific_location'])): ?>
                                <p class="flex items-center truncate">
                                    <i class="fa-solid fa-map-pin w-4 text-slate-500 flex-shrink-0"></i>
                                    <span class="truncate"><?= h($item['specific_location']) ?></span>
                                </p>
                            <?php endif; ?>
                            
                            <p class="flex items-center text-slate-400">
                                <i class="fa-regular fa-clock w-4 text-slate-500 flex-shrink-0"></i>
                                <span>Reported: <?= date('M d, Y', strtotime($item['created_at'])) ?></span>
                            </p>
                        </div>

                        <div class="flex items-center space-x-2 pt-3 mt-3 border-t border-blue-900/30">
                            <?php 
                            $ownerAvatar = !empty($item['owner_avatar']) && file_exists(__DIR__ . '/../' . ltrim($item['owner_avatar'], '/')) 
                                ? '../' . h(ltrim($item['owner_avatar'], '/')) 
                                : null;
                            ?>
                            <?php if ($ownerAvatar): ?>
                                <img src="<?= $ownerAvatar ?>?v=<?= time() ?>" class="w-6 h-6 rounded-full object-cover border border-blue-800/40 shadow-xs">
                            <?php else: ?>
                                <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-[10px] flex items-center justify-center font-bold">
                                    <?= strtoupper(substr($item['owner_username'] ?? 'U', 0, 1)) ?>
                                </span>
                            <?php endif; ?>
                            <span class="text-xs text-slate-300 font-medium"><?= h($item['owner_username'] ?? 'Anonymous') ?></span>
                        </div>
                    </div>
                    
                    <div class="px-5 py-3 border-t border-blue-900/40 flex items-center justify-between text-xs text-slate-400 bg-[#0b1329]/60">
                        <span class="truncate mr-2 font-medium">
                            <i class="fa-solid fa-location-dot mr-1.5 text-blue-400"></i>
                            <?= h($item['location_name']) ?><?= !empty($item['city_name']) ? ', ' . h($item['city_name']) : '' ?>
                        </span>
                        
                        <?php if ($item['type'] === 'found'): ?>
                            <a href="claim.php?item_id=<?= (int)$item['id'] ?>" class="text-emerald-400 hover:text-emerald-300 font-bold hover:underline flex-shrink-0">
                                Claim &rarr;
                            </a>
                        <?php else: ?>
                            <a href="item_detail.php?id=<?= (int)$item['id'] ?>" class="text-rose-400 hover:text-rose-300 font-bold hover:underline flex-shrink-0">
                                Contact &rarr;
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
const filterCities = <?= json_encode($cities) ?>;
const filterLocs   = <?= json_encode($locations) ?>;

const form          = document.getElementById('filterForm');
const cityInput     = document.getElementById('filter_city_input');
const cityHidden    = document.getElementById('filter_city_id');
const cityMenu      = document.getElementById('city_dropdown_menu');
const clearCityBtn  = document.getElementById('clear_city_btn');

const locInput      = document.getElementById('filter_loc_input');
const locHidden     = document.getElementById('filter_loc_id');
const locMenu       = document.getElementById('loc_dropdown_menu');
const clearLocBtn   = document.getElementById('clear_loc_btn');

function showFilterCityDropdown() {
    filterCityList(cityInput.value);
    cityMenu.classList.remove('hidden');
}

function filterCityList(query) {
    const cleanQuery = query.toLowerCase().trim();
    clearCityBtn.classList.toggle('hidden', cleanQuery === '');
    const filtered = filterCities.filter(c => c.name.toLowerCase().includes(cleanQuery));
    renderCityDropdown(filtered);
    cityMenu.classList.remove('hidden');
}

function renderCityDropdown(list) {
    cityMenu.innerHTML = '';
    const allOpt = document.createElement('div');
    allOpt.className = 'px-3 py-2 text-xs hover:bg-blue-600/20 cursor-pointer text-slate-400 font-semibold';
    allOpt.textContent = 'All Cities';
    allOpt.onclick = () => selectCityFilter(0, '');
    cityMenu.appendChild(allOpt);

    if (list.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'px-3 py-2 text-xs text-slate-500 italic';
        empty.textContent = 'No matching city';
        cityMenu.appendChild(empty);
        return;
    }

    list.forEach(c => {
        const item = document.createElement('div');
        item.className = 'px-3 py-2.5 text-xs hover:bg-blue-600/20 cursor-pointer text-slate-300 hover:text-white font-medium flex items-center gap-2 transition';
        item.innerHTML = `<i class="fa-solid fa-city text-blue-400 text-[11px] mr-2"></i><span>${escapeHtml(c.name)}</span>`;
        item.onclick = () => selectCityFilter(c.id, c.name);
        cityMenu.appendChild(item);
    });
}

function selectCityFilter(id, name) {
    cityHidden.value = id > 0 ? id : '';
    cityInput.value  = name;
    cityMenu.classList.add('hidden');
    form.submit();
}

function clearCityFilter() {
    cityHidden.value = '';
    cityInput.value  = '';
    clearCityBtn.classList.add('hidden');
    cityMenu.classList.add('hidden');
    form.submit();
}

function showFilterLocDropdown() {
    filterLocList(locInput.value);
    locMenu.classList.remove('hidden');
}

function filterLocList(query) {
    const cleanQuery = query.toLowerCase().trim();
    clearLocBtn.classList.toggle('hidden', cleanQuery === '');
    const currentCityId = cityHidden.value;
    let pool = filterLocs;
    if (currentCityId) {
        pool = filterLocs.filter(l => String(l.city_id) === String(currentCityId));
    }
    const filtered = pool.filter(l => l.name.toLowerCase().includes(cleanQuery));
    renderLocDropdown(filtered);
    locMenu.classList.remove('hidden');
}

function renderLocDropdown(list) {
    locMenu.innerHTML = '';
    const allOpt = document.createElement('div');
    allOpt.className = 'px-3 py-2 text-xs hover:bg-blue-600/20 cursor-pointer text-slate-400 font-semibold';
    allOpt.textContent = 'All Locations';
    allOpt.onclick = () => selectLocFilter(0, '');
    locMenu.appendChild(allOpt);

    if (list.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'px-3 py-2 text-xs text-slate-500 italic';
        empty.textContent = 'No matching location';
        locMenu.appendChild(empty);
        return;
    }

    list.forEach(l => {
        const item = document.createElement('div');
        item.className = 'px-3 py-2.5 text-xs hover:bg-blue-600/20 cursor-pointer text-slate-300 hover:text-white font-medium flex items-center gap-2 transition';
        item.innerHTML = `<i class="fa-solid fa-location-dot text-blue-400 text-[10px]"></i><span>${escapeHtml(l.name)}</span>`;
        item.onclick = () => selectLocFilter(l.id, l.name);
        locMenu.appendChild(item);
    });
}

function selectLocFilter(id, name) {
    locHidden.value = id > 0 ? id : '';
    locInput.value  = name;
    locMenu.classList.add('hidden');
    form.submit();
}

function clearLocFilter() {
    locHidden.value = '';
    locInput.value  = '';
    clearLocBtn.classList.add('hidden');
    locMenu.classList.add('hidden');
    form.submit();
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('#filter_city_input') && !e.target.closest('#city_dropdown_menu')) {
        cityMenu.classList.add('hidden');
    }
    if (!e.target.closest('#filter_loc_input') && !e.target.closest('#loc_dropdown_menu')) {
        locMenu.classList.add('hidden');
    }
});

function escapeHtml(text) {
    return text.replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}
</script>

<?php ui_footer(); ?>