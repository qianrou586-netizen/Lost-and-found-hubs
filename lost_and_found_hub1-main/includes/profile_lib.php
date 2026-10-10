<?php
// includes/profile_lib.php
// Profile စီမံခန့်ခွဲမှု (User နှင့် Admin သီးခြားစီ)

// မြန်မာနိုင်ငံ၏ ပြည်နယ်နှင့် တိုင်းဒေသကြီးများ
function myanmar_regions(): array
{
    return [
        'Yangon Region', 'Mandalay Region', 'Nay Pyi Taw Union Territory', 'Ayeyarwady Region',
        'Bago Region', 'Magway Region', 'Sagaing Region', 'Tanintharyi Region',
        'Kachin State', 'Kayah State', 'Kayin State', 'Chin State', 'Mon State',
        'Rakhine State', 'Shan State',
    ];
}

// Role အလိုက် table အမည်ကို ရွေးသည်
function profile_table(string $role): string
{
    return $role === 'admin' ? 'admin_profiles' : 'user_profiles';
}

// Profile ကို ဖတ်သည်
function profile_get(PDO $pdo, string $role, int $userId): array
{
    $table = profile_table($role);
    $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE user_id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $empty = [
        'full_name'     => '', 
        'phone'         => '', 
        'state_region'  => '', 
        'city_township' => '', 
        'address'       => '',
        'avatar'        => ''
    ];
    if ($role === 'admin') {
        $empty['position_title'] = '';
    }
    return $row ? array_merge($empty, $row) : $empty;
}

// Form မှ ရောက်လာသော အချက်အလက်များကို စစ်ဆေးသည်
function profile_validate(array $input, string $role): array
{
    $clean = [
        'full_name'     => trim((string) ($input['full_name'] ?? '')),
        'phone'         => preg_replace('/[\s\-]/', '', (string) ($input['phone'] ?? '')),
        'state_region'  => trim((string) ($input['state_region'] ?? '')),
        'city_township' => trim((string) ($input['city_township'] ?? '')),
        'address'       => trim((string) ($input['address'] ?? '')),
        'avatar'        => trim((string) ($input['avatar'] ?? '')),
    ];
    if ($role === 'admin') {
        $clean['position_title'] = trim((string) ($input['position_title'] ?? ''));
    }

    $errors = [];

    if ($clean['full_name'] === '' || mb_strlen($clean['full_name']) > 100) {
        $errors[] = 'Full name is required (maximum 100 characters).';
    }

    if ($clean['phone'] !== '' && !preg_match('/^(?:\+?959|09)\d{7,9}$/', $clean['phone'])) {
        $errors[] = 'Phone must be a valid Myanmar number, for example 09123456789 or +959123456789.';
    }

    if ($clean['state_region'] !== '' && !in_array($clean['state_region'], myanmar_regions(), true)) {
        $errors[] = 'Please choose a valid State or Region.';
    }
    if (mb_strlen($clean['city_township']) > 100) {
        $errors[] = 'City / Township is too long (maximum 100 characters).';
    }
    if (mb_strlen($clean['address']) > 255) {
        $errors[] = 'Address is too long (maximum 255 characters).';
    }
    if ($role === 'admin' && mb_strlen($clean['position_title']) > 100) {
        $errors[] = 'Position title is too long (maximum 100 characters).';
    }

    return [$clean, $errors];
}

// Profile ကို သိမ်းသည်
function profile_save(PDO $pdo, string $role, int $userId, array $clean): void
{
    $table = profile_table($role);

    if ($role === 'admin') {
        $sql = "INSERT INTO {$table} (user_id, full_name, phone, state_region, city_township, address, avatar, position_title)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    full_name = VALUES(full_name), 
                    phone = VALUES(phone),
                    state_region = VALUES(state_region), 
                    city_township = VALUES(city_township),
                    address = VALUES(address), 
                    avatar = VALUES(avatar),
                    position_title = VALUES(position_title)";
        $params = [$userId, $clean['full_name'], $clean['phone'], $clean['state_region'],
                   $clean['city_township'], $clean['address'], $clean['avatar'], $clean['position_title']];
    } else {
        $sql = "INSERT INTO {$table} (user_id, full_name, phone, state_region, city_township, address, avatar)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    full_name = VALUES(full_name), 
                    phone = VALUES(phone),
                    state_region = VALUES(state_region), 
                    city_township = VALUES(city_township),
                    address = VALUES(address),
                    avatar = VALUES(avatar)";
        $params = [$userId, $clean['full_name'], $clean['phone'], $clean['state_region'],
                   $clean['city_township'], $clean['address'], $clean['avatar']];
    }
    $pdo->prepare($sql)->execute($params);
}