<?php
// user/report.php
// Dual Searchable City & Location Filters ပါဝင်သော Report Item စာမျက်နှာ (All-Dark Theme & Fixed Photo Upload)
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/matcher.php';

// Login စစ်ဆေးခြင်း
if (current_role() !== 'user') {
    ui_header('Report Item', 'report');
    ?>
    <div class="max-w-md mx-auto text-center py-24 px-4">
        <div class="w-12 h-12 rounded-full bg-blue-950/60 border border-blue-800/40 flex items-center justify-center mx-auto mb-4 text-blue-400">
            <i class="fa-solid fa-lock text-lg"></i>
        </div>
        <h1 class="font-serif text-2xl font-normal text-white mb-2">Sign In Required</h1>
        <p class="text-xs text-slate-400 mb-6">You need an account to report items.</p>
        <a href="login.php" class="inline-block px-6 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition">Sign In</a>
    </div>
    <?php
    ui_footer();
    exit;
}

$userId = (int) ($_SESSION['user_id'] ?? $_SESSION['user']['id']);$errors = [];

$categories =$pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$cities     =$pdo->query("SELECT id, name FROM cities ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$locations  =$pdo->query("SELECT id, city_id, name, address FROM locations ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$defaultType = in_array($_GET['type'] ?? '', ['lost', 'found'], true) ?$_GET['type'] : 'lost';

// Form Submit ကိုင်တွယ်ခြင်း
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $title       = trim($_POST['title'] ?? '');$type        = trim($_POST['type'] ?? 'lost');$categoryId  = (int) ($_POST['category_id'] ?? 0);$locationId  = (int) ($_POST['location_id'] ?? 0);$cityId      = (int) ($_POST['city_id'] ?? 0);$description = trim($_POST['description'] ?? '');$photoPath   = null;

        if ($title === '') {$errors[] = 'Item title is required.';
        }
        if (!in_array($type, ['lost', 'found'], true)) {$errors[] = 'Invalid item type selected.';
        }
        if ($categoryId <= 0) {$errors[] = 'Please select a valid category.';
        }
        if ($cityId <= 0) {$errors[] = 'Please search and select a valid city from the list.';
        }
        if ($locationId <= 0) {$errors[] = 'Please search and select a valid location from the list.';
        }

        // ဓာတ်ပုံ Upload လုပ်ဆောင်ချက်
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {$fileTmpPath   = $_FILES['photo']['tmp_name'];$fileName      = $_FILES['photo']['name'];$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));$allowedExts   = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExtension, $allowedExts, true)) {$uploadDir = __DIR__ . '/../uploads/items/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newFileName = 'item_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
                $destPath    = $uploadDir .$newFileName;

                if (move_uploaded_file($fileTmpPath,$destPath)) {
                    $photoPath = 'uploads/items/' .$newFileName;
                } else {
                    $errors[] = 'Failed to upload item photo.';
                }
            } else {
                $errors[] = 'Invalid image format. Allowed formats: JPG, PNG, WEBP.';
            }
        }

        if (!$errors) {
            $stmt =$pdo->prepare('
                INSERT INTO items (user_id, category_id, location_id, title, description, type, photo_path, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, "open", NOW())
            ');
            $stmt->execute([$userId, $categoryId,$locationId, $title,$description, $type,$photoPath]);
            $newId = (int)$pdo->lastInsertId();

            $matchesFound = run_auto_matcher($pdo,$newId);

            if ($matchesFound > 0) {
                flash_set('success', "Item reported! Found {$matchesFound} potential match(es).");
            } else {
                flash_set('success', 'Your item report has been submitted successfully.');
            }
            redirect('my_reports.php');
        }
    }
}

ui_header('Report Item', 'report', 'max-w-2xl');
?>

<div class="max-w-2xl mx-auto px-4 py-10">
    <div class="p-8 bg-[#101d42] rounded-2xl border border-blue-900/40 shadow-2xl backdrop-blur-md">
        <h1 class="font-serif text-3xl font-normal text-white mb-1">Report an Item</h1>
        <p class="text-xs text-slate-400 mb-6 leading-relaxed">Submit details and upload a photo of the item you lost or found so the system can match it.</p>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-950/60 text-rose-300 text-xs border border-rose-800/50 space-y-1">
                <?php foreach ($errors as$err): ?>
                    <p><i class="fa-solid fa-triangle-exclamation mr-1.5 text-rose-400"></i><?= h($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Item Type Selection -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">Item Type</label>
                <div class="inline-flex bg-[#0b1329] p-1 rounded-full text-xs font-semibold text-slate-400 border border-blue-900/40">
                    <label class="px-5 py-2 rounded-full cursor-pointer transition has-[:checked]:bg-rose-600 has-[:checked]:text-white">
                        <input type="radio" name="type" value="lost" class="hidden" <?= $defaultType === 'lost' ? 'checked' : '' ?>>
                        I Lost Something
                    </label>
                    <label class="px-5 py-2 rounded-full cursor-pointer transition has-[:checked]:bg-emerald-600 has-[:checked]:text-white">
                        <input type="radio" name="type" value="found" class="hidden" <?= $defaultType === 'found' ? 'checked' : '' ?>>
                        I Found Something
                    </label>
                </div>
            </div>

            <!-- Item Title -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Item Title</label>
                <input 
                    type="text" 
                    name="title" 
                    value="<?= h($_POST['title'] ?? '') ?>" 
                    required 
                    maxlength="150" 
                    class="form-input-pill w-full text-sm" 
                    placeholder="e.g. Silver Water Bottle, iPhone 13, Black Leather Wallet"
                >
            </div>

            <!-- Category, City & Location Section -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Category</label>
                    <select name="category_id" required class="form-input-pill w-full text-sm cursor-pointer">
                        <option value="" class="bg-[#101d42] text-slate-400">Select Category</option>
                        <?php foreach ($categories as$c): ?>
                            <option value="<?= $c['id'] ?>" class="bg-[#101d42] text-white" <?= (isset($_POST['category_id']) && (int)$_POST['category_id'] === (int)$c['id']) ? 'selected' : '' ?>>
                                <?= h($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <!-- City Input -->
                    <div class="relative">
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">City</label>
                        <input type="hidden" name="city_id" id="hidden_city_id" value="<?= h($_POST['city_id'] ?? '') ?>">

                        <div class="relative">
                            <input 
                                type="text" 
                                id="city_search_input" 
                                placeholder="Search city (e.g. Yangon, Mandalay)..." 
                                autocomplete="off"
                                class="form-input-pill w-full text-sm pl-9 pr-8 cursor-text transition focus:border-blue-500"
                                onfocus="showCityDropdown()"
                                oninput="filterCities(this.value)"
                            >
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-xs pointer-events-none"></i>
                            <button type="button" id="clear_city_btn" onclick="clearCitySelection()" class="hidden absolute right-3 top-2.5 text-slate-400 hover:text-white text-sm">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <div id="city_dropdown" class="hidden absolute left-0 right-0 top-full mt-1.5 bg-[#0e1938] border border-blue-900/50 rounded-xl shadow-2xl max-h-56 overflow-y-auto z-50 divide-y divide-blue-950">
                        </div>
                    </div>

                    <!-- Location Input -->
                    <div class="relative">
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Specific Location</label>
                        <input type="hidden" name="location_id" id="hidden_location_id" value="<?= h($_POST['location_id'] ?? '') ?>">

                        <div class="relative">
                            <input 
                                type="text" 
                                id="location_search_input" 
                                placeholder="-- Please select a city first --" 
                                disabled
                                autocomplete="off"
                                class="form-input-pill w-full text-sm bg-slate-900/50 border-slate-800 text-slate-500 pl-9 pr-8 cursor-not-allowed transition"
                                onfocus="showLocationDropdown()"
                                oninput="filterLocations(this.value)"
                            >
                            <i class="fa-solid fa-location-dot absolute left-3.5 top-3.5 text-slate-500 text-xs pointer-events-none"></i>
                            <button type="button" id="clear_loc_btn" onclick="clearLocationSelection()" class="hidden absolute right-3 top-2.5 text-slate-400 hover:text-white text-sm">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <div id="location_dropdown" class="hidden absolute left-0 right-0 top-full mt-1.5 bg-[#0e1938] border border-blue-900/50 rounded-xl shadow-2xl max-h-56 overflow-y-auto z-50 divide-y divide-blue-950">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item Photo Drag & Drop Zone (ပြင်ဆင်ထားသော အပိုင်း) -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Item Photo (Optional)</label>
                
                <div 
                    id="drop_zone" 
                    class="border-2 border-dashed border-blue-900/50 hover:border-blue-400 rounded-2xl p-6 text-center transition bg-[#0b1329]/70 relative cursor-pointer group"
                >
                    <!-- File Input ကို မျက်နှာပြင်တစ်ခုလုံး နေရာယူစေပြီး z-index ဖြင့် အပေါ်တင်ထားပါသည် -->
                    <input 
                        type="file" 
                        name="photo" 
                        id="photo_input" 
                        accept="image/png, image/jpeg, image/jpg, image/webp" 
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" 
                        onchange="previewItemImage(this)"
                    >
                    
                    <div id="upload_prompt" class="space-y-2 pointer-events-none">
                        <div class="w-12 h-12 rounded-full bg-blue-950/80 text-blue-400 flex items-center justify-center mx-auto text-base border border-blue-800/40 group-hover:scale-105 transition">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <p class="text-xs text-slate-300 font-medium">Click or drag a photo here to upload</p>
                        <p class="text-[10px] text-slate-500">Supports JPG, PNG, WEBP (Max 5MB)</p>
                    </div>

                    <!-- ဓာတ်ပုံ Preview Container -->
                    <div id="image_preview_container" class="hidden relative z-30">
                        <img id="image_preview" src="" alt="Selected Photo" class="h-36 mx-auto rounded-xl object-cover border border-blue-800/50 shadow-md">
                        <p id="image_name" class="text-xs text-blue-300 mt-2 font-medium truncate max-w-xs mx-auto"></p>
                        <button 
                            type="button" 
                            onclick="removeUploadedImage(event)" 
                            class="mt-2 text-xs text-rose-400 hover:text-rose-300 font-semibold inline-flex items-center gap-1 bg-rose-950/80 px-3 py-1 rounded-full border border-rose-800/50"
                        >
                            <i class="fa-solid fa-trash-can text-[10px]"></i>
                            <span>Remove Photo</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Description &amp; Key Details</label>
                <textarea 
                    name="description" 
                    rows="4" 
                    class="w-full rounded-2xl border border-blue-900/30 bg-[#111e47] text-white p-4 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/25 placeholder-slate-500 transition" 
                    placeholder="Describe distinct colors, brands, marks, or where exactly you saw or misplaced it..."
                ><?= h($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="pt-4 flex items-center justify-between border-t border-blue-900/40">
                <a href="items.php" class="text-xs text-slate-400 hover:text-white transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition shadow-lg shadow-blue-950/50">Submit Report</button>
            </div>
        </form>
    </div>
</div>

<script>
const allCities    = <?= json_encode($cities) ?>;
const allLocations = <?= json_encode($locations) ?>;
let activeCityLocations = [];

const cityInput    = document.getElementById('city_search_input');
const hiddenCityId = document.getElementById('hidden_city_id');
const cityDropdown = document.getElementById('city_dropdown');
const clearCityBtn = document.getElementById('clear_city_btn');

const locInput     = document.getElementById('location_search_input');
const hiddenLocId  = document.getElementById('hidden_location_id');
const locDropdown  = document.getElementById('location_dropdown');
const clearLocBtn  = document.getElementById('clear_loc_btn');

// City Search
function showCityDropdown() {
    filterCities(cityInput.value);
    cityDropdown.classList.remove('hidden');
}

function filterCities(query) {
    const cleanQuery = query.toLowerCase().trim();
    clearCityBtn.classList.toggle('hidden', cleanQuery === '');
    const filtered = allCities.filter(c => c.name.toLowerCase().includes(cleanQuery));
    renderCityDropdownItems(filtered);
    cityDropdown.classList.remove('hidden');
}

function renderCityDropdownItems(list) {
    cityDropdown.innerHTML = '';
    if (list.length === 0) {
        cityDropdown.innerHTML = `<div class="p-3 text-xs text-slate-500 text-center">No matching cities found</div>`;
        return;
    }
    list.forEach(c => {
        const item = document.createElement('div');
        item.className = 'px-4 py-2.5 text-xs hover:bg-blue-600/20 cursor-pointer text-slate-300 hover:text-white font-medium flex items-center transition';
        item.innerHTML = `<i class="fa-solid fa-city text-blue-400 text-[11px] mr-2"></i><span>${escapeHtml(c.name)}</span>`;
        item.onclick = () => selectCity(c);
        cityDropdown.appendChild(item);
    });
}

function selectCity(c) {
    cityInput.value = c.name;
    hiddenCityId.value = c.id;
    cityDropdown.classList.add('hidden');
    clearCityBtn.classList.remove('hidden');

    clearLocationSelection();
    activeCityLocations = allLocations.filter(l => String(l.city_id) === String(c.id));

    locInput.disabled = false;
    locInput.className = 'form-input-pill w-full text-sm pl-9 pr-8 cursor-text transition focus:border-blue-500';
    locInput.placeholder = 'Search Junction, Mall, Campus...';
    locInput.focus();
}

function clearCitySelection() {
    cityInput.value = '';
    hiddenCityId.value = '';
    clearCityBtn.classList.add('hidden');
    cityDropdown.classList.add('hidden');

    clearLocationSelection();
    activeCityLocations = [];
    locInput.disabled = true;
    locInput.className = 'form-input-pill w-full text-sm bg-slate-900/50 border-slate-800 text-slate-500 pl-9 pr-8 cursor-not-allowed transition';
    locInput.placeholder = '-- Please select a city first --';
}

// Location Search
function showLocationDropdown() {
    if (!hiddenCityId.value || activeCityLocations.length === 0) return;
    renderLocationDropdownItems(activeCityLocations);
    locDropdown.classList.remove('hidden');
}

function filterLocations(query) {
    const cleanQuery = query.toLowerCase().trim();
    clearLocBtn.classList.toggle('hidden', cleanQuery === '');
    const filtered = activeCityLocations.filter(loc => 
        loc.name.toLowerCase().includes(cleanQuery) || 
        (loc.address && loc.address.toLowerCase().includes(cleanQuery))
    );
    renderLocationDropdownItems(filtered);
    locDropdown.classList.remove('hidden');
}

function renderLocationDropdownItems(list) {
    locDropdown.innerHTML = '';
    if (list.length === 0) {
        locDropdown.innerHTML = `<div class="p-3 text-xs text-slate-500 text-center">No matching locations found</div>`;
        return;
    }
    list.forEach(loc => {
        const item = document.createElement('div');
        item.className = 'px-4 py-2.5 text-xs hover:bg-blue-600/20 cursor-pointer text-slate-300 hover:text-white flex flex-col gap-0.5 transition';
        item.innerHTML = `
            <span class="font-semibold text-white"><i class="fa-solid fa-location-dot text-blue-400 text-[11px] mr-1.5"></i>${escapeHtml(loc.name)}</span>
            <span class="text-[10px] text-slate-500 truncate pl-4">${escapeHtml(loc.address || '')}</span>
        `;
        item.onclick = () => selectLocation(loc);
        locDropdown.appendChild(item);
    });
}

function selectLocation(loc) {
    locInput.value = loc.name;
    hiddenLocId.value = loc.id;
    locDropdown.classList.add('hidden');
    clearLocBtn.classList.remove('hidden');
}

function clearLocationSelection() {
    locInput.value = '';
    hiddenLocId.value = '';
    clearLocBtn.classList.add('hidden');
    locDropdown.classList.add('hidden');
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('#city_search_input') && !e.target.closest('#city_dropdown')) {
        cityDropdown.classList.add('hidden');
    }
    if (!e.target.closest('#location_search_input') && !e.target.closest('#location_dropdown')) {
        locDropdown.classList.add('hidden');
    }
});

function escapeHtml(text) {
    return text.replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}

// ==========================================
// IMAGE PREVIEW & REMOVE FUNCTIONALITY
// ==========================================
function previewItemImage(input) {
    const prompt = document.getElementById('upload_prompt');
    const previewContainer = document.getElementById('image_preview_container');
    const previewImg = document.getElementById('image_preview');
    const nameLabel = document.getElementById('image_name');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // 5MB Limit Check
        if (file.size > 5 * 1024 * 1024) {
            alert('Image size exceeds 5MB. Please choose a smaller file.');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            nameLabel.textContent = file.name;
            prompt.classList.add('hidden');
            previewContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}

function removeUploadedImage(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const fileInput = document.getElementById('photo_input');
    const prompt = document.getElementById('upload_prompt');
    const previewContainer = document.getElementById('image_preview_container');
    const previewImg = document.getElementById('image_preview');

    fileInput.value = '';
    previewImg.src = '';
    previewContainer.classList.add('hidden');
    prompt.classList.remove('hidden');
}

// Drag & Drop Styling
const dropZone = document.getElementById('drop_zone');
['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, (e) => {
        e.preventDefault();
        dropZone.classList.add('border-blue-400', 'bg-blue-950/40');
    });
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-blue-400', 'bg-blue-950/40');
    });
});
</script>

<?php ui_footer(); ?>