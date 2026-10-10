<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Language ပြောင်းလဲရန် URL က ပို့လိုက်လျှင် session ထဲ သိမ်းမည်
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Default ဘာသာစကားကို English ဟု သတ်မှတ်မည်
$lang = $_SESSION['lang'] ?? 'en';

// Dictionary
$translations = [
    'en' => [
        'nav_home'     => 'Home',
        'nav_browse'   => 'Browse Items',
        'nav_report'   => 'Report Lost/Found',
        'nav_matches'  => 'Matches',
        'nav_my_items' => 'My Items',
        'nav_stats'    => 'Stats',
        'btn_claim'    => 'Claim Item',
        'btn_submit'   => 'Submit',
    ],
    'mm' => [
        'nav_home'     => 'ပင်မစာမျက်နှာ',
        'nav_browse'   => 'ပစ္စည်းများရှာရန်',
        'nav_report'   => 'ပျောက်/တွေ့တင်ရန်',
        'nav_matches'  => 'တိုက်ဆိုင်မှုများ',
        'nav_my_items' => 'ကျွန်ုပ်၏ပစ္စည်းများ',
        'nav_stats'    => 'စာရင်းဇယားများ',
        'btn_claim'    => 'ပစ္စည်းတောင်းဆိုမည်',
        'btn_submit'   => 'ပေးပို့မည်',
    ]
];

function __($key) {
    global $translations, $lang;
    return $translations[$lang][$key] ?? $key;
}
?>