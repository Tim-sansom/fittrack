<?php
/**
 * File: lazy_seed.php
 * Location: /public/includes/lazy_seed.php
 *
 * Purpose:
 * Application-level "Lazy Cron" for the demonstration account.
 * Ensures the database has 90 days of historical data and continues 
 * to generate daily data without requiring server-side cron access.
 *
 * How it works:
 * Triggered on every authenticated page load. It checks the last 
 * activity date for 'fit_user_001'. If there is a gap, it backfills 
 * the missing days up to yesterday.
 */
declare(strict_types=1);

// Only run for the specific demonstration account
if (($_SESSION['username'] ?? '') !== 'fit_user_001') {
    return;
}

$demoUserId = (int)$_SESSION['user_id'];
$demoTenantId = (int)$_SESSION['tenant_id'];

try {
    // 1. Check the last recorded activity date for this user
    $checkStmt = $db->prepare("SELECT MAX(activity_date) as last_date FROM activity_logs WHERE user_id = :uid");
    $checkStmt->execute(['uid' => $demoUserId]);
    $lastDateStr = $checkStmt->fetchColumn();

    // Determine the start date for backfilling
    if ($lastDateStr) {
        $startDate = new DateTime($lastDateStr);
        $startDate->modify('+1 day'); // Start filling from the day after the last record
    } else {
        // If no data exists at all, start 90 days ago
        $startDate = new DateTime('-90 days');
    }

    // We only backfill up to yesterday. We leave today blank so the marker 
    // can see an "empty" state and potentially log a workout themselves.
    $endDate = new DateTime('+1 day'); 

    // If the start date is in the future, we have no work to do.
    if ($startDate > $endDate) {
        return;
    }

    // 2. Fetch valid Activity Types for this tenant (cached for the loop)
    $typeStmt = $db->prepare("SELECT activity_type_id, slug FROM activity_types WHERE tenant_id = :tid");
    $typeStmt->execute(['tid' => $demoTenantId]);
    $activityTypes = $typeStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // 3. Loop through the missing days and generate data
    $currentDate = clone $startDate;
    while ($currentDate <= $endDate) {
        $dateString = $currentDate->format('Y-m-d');
        $dayOfWeek = (int)$currentDate->format('N'); // 1 (Mon) to 7 (Sun)
        $isWeekend = $dayOfWeek >= 6;

        // --- A. Seed Activity Log ---
        $workoutChance = $isWeekend ? 0.4 : 0.7;
        if (mt_rand(1, 100) / 100 <= $workoutChance) {
            $rand = mt_rand(1, 100);
            if ($rand <= 40) $slug = 'gym';
            elseif ($rand <= 70) $slug = 'running';
            elseif ($rand <= 90) $slug = 'walking';
            else $slug = 'swimming';

            $typeId = $activityTypes[$slug] ?? null;
            if ($typeId) {
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

                $db->prepare("
                    INSERT INTO activity_logs 
                    (tenant_id, user_id, activity_type_id, activity_date, title, duration_minutes, distance_km, visibility, created_at)
                    VALUES (:tid, :uid, :type, :date, :title, :dur, :dist, 'tenant', NOW())
                ")->execute([
                    'tid' => $demoTenantId, 'uid' => $demoUserId, 'type' => $typeId, 
                    'date' => $dateString, 'title' => $title, 'dur' => $duration, 'dist' => $distance
                ]);
            }
        }

        // --- B. Seed Daily Nutrition ---
        // Check idempotency
        $nutCheck = $db->prepare("SELECT log_date FROM daily_nutrition WHERE user_id = :uid AND log_date = :date LIMIT 1");
        $nutCheck->execute(['uid' => $demoUserId, 'date' => $dateString]);
        
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
                'tid' => $demoTenantId, 'uid' => $demoUserId, 'date' => $dateString, 
                'kcal' => $totalKcal, 'p' => $protein, 'c' => $carbs, 'f' => $fats
            ]);
        }

        // --- C. Seed Weekly Measurements (Every Monday) ---
        if ($dayOfWeek === 1) {
            $measCheck = $db->prepare("SELECT recorded_at FROM user_measurements WHERE user_id = :uid AND DATE(recorded_at) = :date LIMIT 1");
            $measCheck->execute(['uid' => $demoUserId, 'date' => $dateString]);
            
            if (!$measCheck->fetch()) {
                $daysSinceStart = $currentDate->diff(new DateTime('-90 days'))->days;
                $weeksPassed = $daysSinceStart / 7;
                $baseWeight = 72.0 - ($weeksPassed * 0.12); 
                $weight = round($baseWeight + (mt_rand(-2, 2) / 10), 1);
                $bmi = round($weight / (1.75 * 1.75), 1);

                // FIXED: Removed tenant_id as user_measurements table does not have this column
                $db->prepare("
                    INSERT INTO user_measurements 
                    (user_id, weight_kg, bmi, chest_cm, waist_cm, hips_cm, left_arm_cm, right_arm_cm, left_leg_cm, right_leg_cm, recorded_at)
                    VALUES (:uid, :w, :bmi, 96.9, 83.2, 98.0, 33.7, 33.2, 56.8, 55.3, :date)
                ")->execute([
                    'uid' => $demoUserId, 'w' => $weight, 'bmi' => $bmi, 'date' => $dateString . ' 08:00:00'
                ]);
            }
        }

        $currentDate->modify('+1 day');
    }
} catch (PDOException $e) {
    // Silently fail. We don't want to break the UI if seeding fails.
    error_log('Lazy seed failed: ' . $e->getMessage());
}