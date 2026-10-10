<?php
// Email ပို့သည့် function များ

// Email ပို့ရန် အဓိက function: အောင်မြင်လျှင် true ပြန်သည်
function send_mail(string $to, string $subject, string $body): bool
{
    // Header injection မဖြစ်စေရန် စာကြောင်းအသစ် (CR/LF) များကို ဖယ်ရှားသည်
    $to      = str_replace(["\r", "\n"], '', $to);
    $subject = str_replace(["\r", "\n"], '', $subject);

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    switch (MAIL_DRIVER) {
        case 'smtp':
            return smtp_send($to, $subject, $body);
        case 'mail':
            return mail_native($to, $subject, $body);
        default:
            return mail_to_log($to, $subject, $body);
    }
}

// စမ်းသပ်ရန်: Email မပို့ဘဲ storage/mail.log ထဲတွင် မှတ်ထားသည်
function mail_to_log(string $to, string $subject, string $body): bool
{
    $entry = '[' . date('Y-m-d H:i:s') . "] To: $to | Subject: $subject\n$body\n" . str_repeat('-', 50) . "\n";
    return @file_put_contents(MAIL_LOG_FILE, $entry, FILE_APPEND | LOCK_EX) !== false;
}

// PHP ၏ ပင်ကိုယ် mail() function ဖြင့် ပို့သည်
function mail_native(string $to, string $subject, string $body): bool
{
    $headers  = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    return @mail($to, $encodedSubject, $body, $headers);
}

// SMTP server သို့ တိုက်ရိုက်ချိတ်ဆက်၍ ပို့သည် (Port 587 / TLS)
function smtp_send(string $to, string $subject, string $body): bool
{
    // Local SSL Context Options တည်ဆောက်ခြင်း
    $context = stream_context_create([
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
            'cafile'            => 'C:/xampp/php/cacert.pem',
            'peer_name'         => 'smtp.gmail.com',
            'SNI_enabled'       => true,
        ]
    ]);

    $prefix = (SMTP_SECURE === 'ssl') ? 'ssl://' : '';
    $fp = @stream_socket_client(
        $prefix . SMTP_HOST . ':' . SMTP_PORT,
        $errno,
        $errstr,
        15,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$fp) {
        return false;
    }
    stream_set_timeout($fp, 15);

    // Server ၏ တုံ့ပြန်ချက် (multi-line ပါ) ကို ဖတ်သည်
    $read = function () use ($fp): string {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };

    // Command တစ်ခု ပို့ပြီး မျှော်လင့်သော status code ဟုတ်မဟုတ် စစ်သည်
    $cmd = function (string $command, array $okCodes) use ($fp, $read): bool {
        fwrite($fp, $command . "\r\n");
        return in_array((int) substr($read(), 0, 3), $okCodes, true);
    };

    $ok = true;
    $read(); // Server greeting
    $ok = $ok && $cmd('EHLO localhost', [250]);

    // Port 587 (TLS) အတွက် STARTTLS ဖြင့် လုံခြုံသော ချိတ်ဆက်မှု ပြောင်းသည်
    if ($ok && SMTP_SECURE === 'tls') {
        // stream_socket_enable_crypto မခေါ်မီ SSL options များကို socket သို့ တိုက်ရိုက် bind လုပ်ခြင်း
        stream_context_set_option($fp, 'ssl', 'verify_peer', false);
        stream_context_set_option($fp, 'ssl', 'verify_peer_name', false);
        stream_context_set_option($fp, 'ssl', 'allow_self_signed', true);
        stream_context_set_option($fp, 'ssl', 'cafile', 'C:/xampp/php/cacert.pem');
        stream_context_set_option($fp, 'ssl', 'peer_name', 'smtp.gmail.com');

        $crypto_method = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
        $ok = $cmd('STARTTLS', [220])
            && stream_socket_enable_crypto($fp, true, $crypto_method)
            && $cmd('EHLO localhost', [250]);
    }

    // Login ဝင်ခြင်း
    $ok = $ok && $cmd('AUTH LOGIN', [334])
              && $cmd(base64_encode(SMTP_USERNAME), [334])
              && $cmd(base64_encode(SMTP_PASSWORD), [235]);

    $ok = $ok && $cmd('MAIL FROM:<' . MAIL_FROM_ADDRESS . '>', [250])
              && $cmd('RCPT TO:<' . $to . '>', [250, 251])
              && $cmd('DATA', [354]);

    if ($ok) {
        $headers  = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . ">\r\n";
        $headers .= 'To: <' . $to . ">\r\n";
        $headers .= 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n";
        $headers .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n";
        $headers .= 'Date: ' . date('r') . "\r\n";

        // စာကြောင်းအဆုံးကို \r\n သို့ ပြောင်း၍ လိုင်းအစ '.' ကို နှစ်ခု ပြုလုပ်သည် (dot-stuffing)
        $clean = preg_replace('/\r\n|\r|\n/', "\r\n", $body);
        $clean = preg_replace('/^\./m', '..', $clean);

        fwrite($fp, $headers . "\r\n" . $clean . "\r\n.\r\n");
        $ok = ((int) substr($read(), 0, 3)) === 250;
    }

    @fwrite($fp, "QUIT\r\n");
    fclose($fp);
    return $ok;
}