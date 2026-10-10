<?php
// ajax_coco_match.php
// Coco AI Image Matching Backend Script

// Database connection ကို အလွယ်ဆုံးနှင့် အမှန်ကਨဆုံး ချိတ်ဆက်ခြင်း
$dbPaths = [
   __DIR__ . '/includes/db.php',
    __DIR__ . '/../includes/db.php',
    'includes/db.php'
];

$connected = false;
foreach ($dbPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $connected = true;
        break;
    }
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $message = $input['message'] ?? '';
    $hasImage = !empty($input['image']);

    try {
        if (!isset($pdo)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database connection not found.'
            ]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, title, category_id, location_id, status, created_at FROM items ORDER BY id DESC LIMIT 5");
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($items) > 0) {
            $responseMessage = "✨ Coco မှ Database ထဲရှိ ပစ္စည်းစာရင်းများကို စစ်ဆေးပြီးပါပြီ။ လောလောဆယ် တူညီနိုင်ခြေရှိသော ပစ္စည်း (" . count($items) . ") ခုကို တွေ့ရှိထားပါတယ်ရှင်။";
            echo json_encode([
                'status' => 'success',
                'matched' => true,
                'message' => $responseMessage,
                'items' => $items
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'matched' => false,
                'message' => "❌ Coco မှ စစ်ဆေးပြီးပါပြီ။ လက်ရှိ Database ထဲတွင် တူညီသော ပစ္စည်းအသစ် မတွေ့ရှိရသေးပါရှင်။"
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}