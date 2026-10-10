<?php
// ဤဖိုင်သည် OTP Email ပို့ခြင်းနှင့် OTP စည်းမျဉ်းများ၏ ဆက်တင်များ ဖြစ်သည်။

// Email ပို့မည့် နည်းလမ်း: 'log' (စမ်းသပ်ရန်၊ ဖိုင်ထဲသို့သာ မှတ်သည်) | 'mail' (PHP mail()) | 'smtp'
define('MAIL_DRIVER', 'log');

// ပို့သူ၏ အမည်နှင့် Email
define('MAIL_FROM_ADDRESS', 'no-reply@lostfound.local');
define('MAIL_FROM_NAME', 'Lost & Found Hub');

// SMTP ဆက်တင် (MAIL_DRIVER = 'smtp' ဖြစ်မှသာ အသုံးပြုသည်)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');            // 'tls' = STARTTLS (port 587), 'ssl' = SSL (port 465)
define('SMTP_USERNAME', 'qianrou586@gmail.com');

define('SMTP_PASSWORD', 'gfeavisjqnnmjuxg');

// OTP စည်းမျဉ်းများ
define('OTP_LENGTH', 6);                 // OTP ဂဏန်း အရေအတွက်
define('OTP_TTL_SECONDS', 300);          // OTP သက်တမ်း (စက္ကန့် ၃၀၀ = ၅ မိနစ်)
define('OTP_RESEND_SECONDS', 60);        // OTP ပြန်တောင်းရန် စောင့်ရမည့်အချိန်
define('OTP_MAX_ATTEMPTS', 5);           // မှားယွင်းထည့်သွင်းခွင့် အများဆုံးအကြိမ်ရေ
define('OTP_PENDING_SECONDS', 600);      // OTP စာမျက်နှာတွင် နေခွင့်ရှိသော အချိန်

// OTP ကို hash လုပ်ရာတွင် သုံးသော လျှို့ဝှက်စာကြောင်း (ကိုယ်ပိုင်စာကြောင်းရှည်ဖြင့် လဲလှယ်ပါ)
define('OTP_PEPPER', 'CHANGE_THIS_TO_A_LONG_RANDOM_STRING');

// 'log' mode တွင် Email အကြောင်းအရာကို မှတ်မည့်ဖိုင်
define('MAIL_LOG_FILE', __DIR__ . '/../storage/mail.log');
