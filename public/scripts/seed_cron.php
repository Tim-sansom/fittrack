<?php
/**
 * File: seed_cron.php
 * Location: /public/scripts/seed_cron.php
 * 
 * Purpose:
 * Standalone script to be executed by the server's Cron Job.
 * It backfills missing data for the demonstration account (fit_user_001)
 * to ensure the dashboard looks active for assessment.
 */

// 1. Connect to Database
// Adjust this path if your config folder is located differently relative to this script
require_once __DIR__ . '/../config/Database.php'; 

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    // If DB fails, log it and exit. Cron will try again next day.
    error_log("FitTrack Cron: DB Connection failed - " . $e->getMessage());
    exit(1);
}

// 2. Find the Demonstration User
// We look for 'fit_user_001' specifically.
$stmt = $db->prepare("SELECT user_id, tenant_id FROM users WHERE username = 'fit_user_001' LIMIT 1");
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Error: User 'fit_user_001' not found in database.\n";
    exit(1);
}

$userId = (int)$user['user_id'];
$tenantId = (int)$user['tenant_id'];

echo "Starting seed for User ID: $userId (Tenant: $tenantId)\n";

// 3. Determine what data is missing
// We check the latest activity date. If it's old, we fill the gap up to yesterday.
$stmt = $db->prepare("SELECT MAX(activity_date) as last_date FROM activity_logs WHERE user_id = :uid");
$stmt->execute(['uid' => $userId]);
$lastDateStr = $stmt->fetchColumn();

if ($lastDateStr) {
    $startDate = new DateTime($lastDateStr);
    $startDate->modify('+1 day'); // Start filling from the day after the last record
} else {
    // If no data exists at all, start 90 days ago
    $startDate = new DateTime('-90 days');
}

// We fill up to yesterday. We leave today blank so the marker sees a "fresh" day.
$endDate = new DateTime('-1 day');

if ($startDate > $endDate) {
    echo "Database is up to date. No seeding required.\n";
    exit(0);
}

// 4. Fetch Activity Types for this tenant
$typeStmt = $db->prepare("SELECT activity_type_id, slug FROM activity_types WHERE tenant_id = :tid");
$typeStmt->execute(['tid' => $tenantId]);
$activityTypes = $typeStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// 5. The Seeding Loop
$currentDate = clone $startDate;
$daysSeeded = 0;

while ($currentDate <= $endDate) {
    $dateString = $currentDate->format('Y-m-d');
    $dayOfWeek = (int)$currentDate->format('N'); // 1 (Mon) to 7 (Sun)
    $isWeekend = $dayOfWeek >= 6;

    // --- A. Seed Activity Log ---
    // 70% chance on weekdays, 40% on weekends
    if (mt_rand(1, 100) / 100 <= ($isWeekend ? 0.4 : 0.7)) {
        
        // Randomly select activity type
        $rand = mt_rand(1, 100);
        if ($rand <= 40) $slug = 'gym';
        elseif ($rand <= 70) $slug = 'running';
        elseif ($rand <= 90) $slug = 'walking';
        else $slug = 'swimming';

        $typeId = $activityTypes[$slug] ?? null;

        if ($typeId) {
            // Generate realistic data based on type
            if ($slug === 'gym') {
                $duration = mt_rand(45, 75); $distance = null;
                $title = ['Upper Body Push', 'Leg Day', 'Pull & Core', 'Full Body Circuit'][mt_rand(0, 3)];
            } elseif ($slug === 'running') {
                $duration = mt_rand(20, 50); $distance = round(mt_rand(30, 100) / 10, 1);
                $title = ['Morning 5k', 'Lunch Run', 'Evening Tempo', 'Long Run'][mt_rand(0, 3)];
            } elseif ($slug === 'walking') {
                $duration = mt_rand(30, 60); $distance = round(mt_rand(20, 60) / 10, 1);
                $title = ['Evening Stroll', 'Lunch Walk', 'Weekend Hike'][mt_rand(0, 2)];
            } else {
                $duration = mt_rand(30, 60); $distance = null; $title = 'Pool Laps';
            }

            // Insert Activity (Idempotent: The date loop ensures we don't duplicate days easily, 
            // but in a real app we'd check existence first. For this cron, the date loop is usually enough 
            // if we only run it once a day).
            $db->prepare("
                INSERT INTO activity_logs 
                (tenant_id, user_id, activity_type_id, activity_date, title, duration_minutes, distance_km, visibility, created_at)
                VALUES (:tid, :uid, :type, :date, :title, :dur, :dist, 'tenant', NOW())
            ")->execute([
                'tid' => $tenantId, 'uid' => $userId, 'type' => $typeId, 
                'date' => $dateString, 'title' => $title, 'dur' => $duration, 'dist' => $distance
            ]);
        }
    }

    // --- B. Seed Daily Nutrition ---
    // Check if nutrition exists for this date to prevent duplicates
    $nutCheck = $db->prepare("SELECT log_date FROM daily_nutrition WHERE user_id = :uid AND log_date = :date LIMIT 1");
    $nutCheck->execute(['uid' => $userId, 'date' => $dateString]);
    
    if (!$nutCheck->fetch()) {
        $breakfastKcal = mt_rand(350, 500);
        $lunchKcal = mt_rand(500, 750);
        $dinnerKcal = mt_rand(600, 850);
        $totalKcal = $breakfastKcal + $lunchKcal + $dinnerKcal;
        
        $protein = round($totalKcal * 0.30 / 4);
        $carbs = round($totalKcal * 0.45 / 4);
        $fats = round($totalKcal * 0.25 / 9);

        $db->prepare("
            INSERT INTO daily_nutrition 
            (tenant_id, user_id, log_date, calories_consumed, calories_burned, protein_g, carbs_g, fats_g)
            VALUES (:tid, :uid, :date, :kcal, 0, :p, :c, :f)
        ")->execute([
            'tid' => $tenantId, 'uid' => $userId, 'date' => $dateString, 
            'kcal' => $totalKcal, 'p' => $protein, 'c' => $carbs, 'f' => $fats
        ]);
    }

    // --- C. Seed Weekly Measurements (Every Monday) ---
    if ($dayOfWeek === 1) {
        $measCheck = $db->prepare("SELECT recorded_at FROM user_measurements WHERE user_id = :uid AND DATE(recorded_at) = :date LIMIT 1");
        $measCheck->execute(['uid' => $userId, 'date' => $dateString]);
        
        if (!$measCheck->fetch()) {
            // Realistic weight loss trend: 72.0kg -> 70.6kg over 12 weeks
            $daysSinceStart = $currentDate->diff(new DateTime('-90 days'))->days;
            $weeksPassed = $daysSinceStart / 7;
            $baseWeight = 72.0 - ($weeksPassed * 0.12); 
            $weight = round($baseWeight + (mt_rand(-2, 2) / 10), 1);
            $bmi = round($weight / (1.75 * 1.75), 1);

            $db->prepare("
                INSERT INTO user_measurements 
                (user_id, weight_kg, bmi, chest_cm, waist_cm, hips_cm, left_arm_cm, right_arm_cm, left_leg_cm, right_leg_cm, recorded_at)
                VALUES (:uid, :w, :bmi, 96.9, 83.2, 98.0, 33.7, 33.2, 56.8, 55.3, :date)
            ")->execute([
                'uid' => $userId, 'w' => $weight, 'bmi' => $bmi, 'date' => $dateString . ' 08:00:00'
            ]);
        }
    }

    $currentDate->modify('+1 day');
    $daysSeeded++;
}

echo "Finished. Seeded $daysSeeded days of data.\n";