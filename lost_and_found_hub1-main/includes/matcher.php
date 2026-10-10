<?php
// includes/matcher.php
// Lost and Found Smart Weighted Matching Algorithm

/**
 * စာသားနှစ်ခုအကြား တူညီမှု ရာခိုင်နှုန်းကို တွက်ချက်ခြင်း (0.0 မှ 1.0 အထိ)
 */
function calculate_text_similarity(string $str1, string $str2): float
{
    $str1 = mb_strtolower(trim($str1));
    $str2 = mb_strtolower(trim($str2));

    if ($str1 === '' || $str2 === '') {
        return 0.0;
    }

    if ($str1 === $str2) {
        return 1.0;
    }

    similar_text($str1, $str2, $percent);
    return round($percent / 100, 4);
}

/**
 * Description ထဲမှ အရေးကြီး Keyword များ တူညီမှုကို တွက်ချက်ခြင်း (0.0 မှ 1.0 အထိ)
 */
function calculate_keyword_overlap(string $desc1, string $desc2): float
{
    $desc1 = mb_strtolower(trim($desc1));
    $desc2 = mb_strtolower(trim($desc2));

    if ($desc1 === '' || $desc2 === '') {
        return 0.0;
    }

    // စာလုံးများကို ခွဲထုတ်ပြီး အရှည် ၂ လုံးထက်ပိုသော စာလုံးများကိုသာ စစ်ဆေးသည်
    $words1 = array_filter(preg_split('/[\s,\.\-_]+/', $desc1), fn($w) => mb_strlen($w) >= 3);
    $words2 = array_filter(preg_split('/[\s,\.\-_]+/', $desc2), fn($w) => mb_strlen($w) >= 3);

    if (empty($words1) || empty($words2)) {
        return 0.0;
    }

    $intersection = array_intersect(array_unique($words1), array_unique($words2));
    $union = array_unique(array_merge($words1, $words2));

    return round(count($intersection) / count($union), 4);
}

/**
 * ပစ္စည်းနှစ်ခုအကြား Overall Match Score ကို Weighted Formula ဖြင့် တွက်ချက်ခြင်း
 * Score Range: 0 to 100%
 */
function compute_match_score(array $itemA, array $itemB): array
{
    // 1. Title Similarity (Weight: 35%)
    $titleSim = calculate_text_similarity($itemA['title'], $itemB['title']);
    $titleScore = $titleSim * 35;

    // 2. Category Match (Weight: 25%)
    $catScore = ((int)$itemA['category_id'] === (int)$itemB['category_id']) ? 25 : 0;

    // 3. Location Match (Weight: 20%)
    $locScore = ((int)$itemA['location_id'] === (int)$itemB['location_id']) ? 20 : 0;

    // 4. Description Keywords Overlap (Weight: 10%)
    $descSim = calculate_keyword_overlap($itemA['description'] ?? '', $itemB['description'] ?? '');
    $descScore = $descSim * 10;

    // 5. Date Proximity (Weight: 10%)
    $dateScore = 0;
    $dateA = !empty($itemA['date_occurred']) ? strtotime($itemA['date_occurred']) : (!empty($itemA['created_at']) ? strtotime($itemA['created_at']) : time());
    $dateB = !empty($itemB['date_occurred']) ? strtotime($itemB['date_occurred']) : (!empty($itemB['created_at']) ? strtotime($itemB['created_at']) : time());

    $daysDiff = abs($dateA - $dateB) / 86400;
    if ($daysDiff <= 1) {
        $dateScore = 10;
    } elseif ($daysDiff <= 3) {
        $dateScore = 8;
    } elseif ($daysDiff <= 7) {
        $dateScore = 5;
    } elseif ($daysDiff <= 14) {
        $dateScore = 2;
    }

    $totalScore = round($titleScore + $catScore + $locScore + $descScore + $dateScore);

    // Stars Rating တွက်ချက်ခြင်း (1 to 5 Stars)
    $stars = match (true) {
        $totalScore >= 80 => 5,
        $totalScore >= 65 => 4,
        $totalScore >= 50 => 3,
        $totalScore >= 35 => 2,
        default           => 1,
    };

    return [
        'score'       => $totalScore,
        'stars'       => $stars,
        'breakdown'   => [
            'title'       => round($titleScore),
            'category'    => $catScore,
            'location'    => $locScore,
            'description' => round($descScore),
            'date'        => $dateScore,
        ]
    ];
}

/**
 * Item အသစ်တင်သည့်အခါ ဆန့်ကျင်ဘက် Item များနှင့် အလိုအလျောက် Score တွက်ပြီး matches table သို့ ထည့်သွင်းခြင်း
 */
function run_auto_matcher(PDO $pdo, int $newItemId): int
{
    // Item အချက်အလက်ကို ဖတ်ယူခြင်း
    $stmt = $pdo->prepare('SELECT * FROM items WHERE id = ?');
    $stmt->execute([$newItemId]);
    $newItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$newItem) return 0;

    $oppositeType = ($newItem['type'] === 'lost') ? 'found' : 'lost';

    // Status 'open' ဖြစ်ပြီး ဆန့်ကျင်ဘက် အမျိုးအစားရှိသော အခြား user များ၏ item များကို ရှာဖွေခြင်း
    $candsStmt = $pdo->prepare('
        SELECT * FROM items 
        WHERE type = ? AND status = "open" AND user_id != ? AND id != ?
    ');
    $candsStmt->execute([$oppositeType, $newItem['user_id'], $newItemId]);
    $candidates = $candsStmt->fetchAll(PDO::FETCH_ASSOC);

    $matchCount = 0;
    $insMatch = $pdo->prepare('
        INSERT INTO matches (lost_item_id, found_item_id, score, status) 
        VALUES (?, ?, ?, "pending")
        ON DUPLICATE KEY UPDATE score = VALUES(score)
    ');

    foreach ($candidates as $cand) {
        $matchResult = compute_match_score($newItem, $cand);

        // Overall Score 35% နှင့် အထက်ရှိပါက Match အဖြစ် မှတ်ယူသိမ်းဆည်းသည်
        if ($matchResult['score'] >= 35) {
            $lostId  = ($newItem['type'] === 'lost') ? $newItemId : $cand['id'];
            $foundId = ($newItem['type'] === 'found') ? $newItemId : $cand['id'];

            $insMatch->execute([$lostId, $foundId, $matchResult['score']]);
            $matchCount++;
        }
    }

    return $matchCount;
}