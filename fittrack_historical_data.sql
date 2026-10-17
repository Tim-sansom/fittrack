-- ============================================
-- FitTrack Historical Chart Data (365 Days)
-- ============================================
-- Purpose: Populates activity_logs, daily_nutrition, 
-- and user_measurements with 1 year of data so the 
-- charts display historical trends for the marker.
-- 
-- Target User: fit_user_001 (User ID 16 in base data)
-- Target Tenant: Greater Manchester Active (Tenant ID 3)
-- ============================================

USE fittrack;

-- 1. Set variables for the target user and tenant
SET @tenant_id = (SELECT tenant_id FROM tenants WHERE tenant_slug = 'greater-manchester-active' LIMIT 1);
SET @user_001 = (SELECT user_id FROM users WHERE username = 'fit_user_001' LIMIT 1);

-- Fetch activity type IDs
SET @type_running = (SELECT activity_type_id FROM activity_types WHERE tenant_id = @tenant_id AND slug = 'running' LIMIT 1);
SET @type_gym = (SELECT activity_type_id FROM activity_types WHERE tenant_id = @tenant_id AND slug = 'gym' LIMIT 1);
SET @type_swimming = (SELECT activity_type_id FROM activity_types WHERE tenant_id = @tenant_id AND slug = 'swimming' LIMIT 1);
SET @type_walking = (SELECT activity_type_id FROM activity_types WHERE tenant_id = @tenant_id AND slug = 'walking' LIMIT 1);
SET @type_football = (SELECT activity_type_id FROM activity_types WHERE tenant_id = @tenant_id AND slug = 'football' LIMIT 1);

-- 2. Create a temporary helper table to generate 365 dates
DROP TEMPORARY TABLE IF EXISTS temp_numbers;
CREATE TEMPORARY TABLE temp_numbers (n INT);
INSERT INTO temp_numbers
SELECT @row := @row + 1 AS n
FROM (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
     (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b,
     (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) c,
     (SELECT @row := 0) r
LIMIT 365;

-- 3. Generate 365 days of Activity Logs
INSERT INTO activity_logs (tenant_id, user_id, activity_type_id, activity_date, title, duration_minutes, distance_km, intensity, visibility, created_at)
SELECT 
    @tenant_id, @user_001,
    CASE FLOOR(RAND() * 5) 
        WHEN 0 THEN @type_running 
        WHEN 1 THEN @type_gym 
        WHEN 2 THEN @type_swimming 
        WHEN 3 THEN @type_walking 
        WHEN 4 THEN @type_football 
    END,
    DATE_SUB(CURDATE(), INTERVAL n DAY),
    CONCAT(
        CASE FLOOR(RAND() * 5) WHEN 0 THEN 'Morning' WHEN 1 THEN 'Evening' WHEN 2 THEN 'Afternoon' WHEN 3 THEN 'Lunch' WHEN 4 THEN 'Night' END, 
        ' ', 
        CASE FLOOR(RAND() * 5) WHEN 0 THEN 'run' WHEN 1 THEN 'session' WHEN 2 THEN 'workout' WHEN 3 THEN 'training' WHEN 4 THEN 'practice' END
    ),
    FLOOR(20 + RAND() * 100),
    CASE WHEN RAND() > 0.5 THEN ROUND(1 + RAND() * 15, 2) ELSE NULL END,
    CASE FLOOR(RAND() * 3) WHEN 0 THEN 'light' WHEN 1 THEN 'moderate' WHEN 2 THEN 'vigorous' END,
    CASE FLOOR(RAND() * 3) WHEN 0 THEN 'private' WHEN 1 THEN 'tenant' WHEN 2 THEN 'public' END,
    DATE_SUB(CURDATE(), INTERVAL n DAY)
FROM temp_numbers 
WHERE RAND() > 0.3 AND n <= 365;

-- 4. Generate 365 days of Nutrition Data (Fixed table name and columns)
INSERT INTO daily_nutrition (tenant_id, user_id, log_date, calories_consumed, calories_burned, protein_g, carbs_g, fats_g, created_at)
SELECT 
    @tenant_id, @user_001, 
    DATE_SUB(CURDATE(), INTERVAL n DAY),
    FLOOR(1800 + RAND() * 700), 
    FLOOR(300 + RAND() * 500),
    FLOOR(80 + RAND() * 70), 
    FLOOR(200 + RAND() * 150), 
    FLOOR(50 + RAND() * 40), 
    DATE_SUB(CURDATE(), INTERVAL n DAY)
FROM temp_numbers 
WHERE n <= 365;

-- 5. Generate Weekly Measurements (Every 7 days for the last 180 days)
INSERT INTO user_measurements (user_id, weight_kg, height_cm, bmi, chest_cm, waist_cm, hips_cm, left_arm_cm, right_arm_cm, left_leg_cm, right_leg_cm, recorded_at)
SELECT 
    @user_001, 
    ROUND(70 + (n / 365) * 5 + RAND() * 2 - 1, 1), 
    175, 
    ROUND((70 + (n / 365) * 5) / POW(1.75, 2), 1),
    ROUND(95 + RAND() * 5, 1), 
    ROUND(80 + RAND() * 4, 1), 
    ROUND(98 + RAND() * 5, 1),
    ROUND(32 + RAND() * 2, 1), 
    ROUND(32 + RAND() * 2, 1), 
    ROUND(55 + RAND() * 3, 1), 
    ROUND(55 + RAND() * 3, 1),
    DATE_SUB(CURDATE(), INTERVAL n DAY)
FROM temp_numbers 
WHERE n <= 180 AND n % 7 = 0;

-- 6. Generate Kudos for public activities
INSERT INTO kudos (user_id, activity_log_id, created_at)
SELECT tm2.user_id, al.activity_log_id, al.created_at
FROM activity_logs al
JOIN tenant_memberships tm1 ON al.user_id = tm1.user_id AND al.tenant_id = tm1.tenant_id
JOIN tenant_memberships tm2 ON tm2.tenant_id = al.tenant_id AND tm2.user_id != al.user_id
WHERE al.tenant_id = @tenant_id 
  AND al.visibility IN ('tenant', 'public') 
  AND RAND() > 0.9
LIMIT 50;

-- 7. Generate Challenges
INSERT INTO challenges (tenant_id, user_id, title, description, target_value, current_value, unit, start_date, end_date, status, created_at)
VALUES
(@tenant_id, @user_001, '100km Running Challenge', 'Run 100km in 3 months', 100, 67, 'km', DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'active', DATE_SUB(CURDATE(), INTERVAL 60 DAY)),
(@tenant_id, @user_001, '30 Workouts in 30 Days', 'Complete 30 workouts', 30, 22, 'sessions', DATE_SUB(CURDATE(), INTERVAL 22 DAY), DATE_ADD(CURDATE(), INTERVAL 8 DAY), 'active', DATE_SUB(CURDATE(), INTERVAL 22 DAY)),
(@tenant_id, @user_001, 'Burn 10,000 Calories', 'Burn 10k calories this month', 10000, 7500, 'kcal', DATE_FORMAT(CURDATE(), '%Y-%m-01'), LAST_DAY(CURDATE()), 'active', DATE_FORMAT(CURDATE(), '%Y-%m-01'));

-- 8. Generate a Training Plan with future and past items
INSERT INTO plans (tenant_id, user_id, title, description, start_date, end_date, status, created_at)
VALUES (@tenant_id, @user_001, 'Weekly Training Plan', 'Balanced weekly routine', DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'active', DATE_SUB(CURDATE(), INTERVAL 30 DAY));

SET @plan_id = LAST_INSERT_ID();

INSERT INTO plan_items (tenant_id, plan_id, activity_type_id, user_id, planned_date, planned_duration_minutes, notes, completion_status, created_at)
SELECT 
    @tenant_id, @plan_id,
    CASE DAYOFWEEK(DATE_ADD(CURDATE(), INTERVAL n DAY)) 
        WHEN 2 THEN @type_gym 
        WHEN 3 THEN @type_running 
        WHEN 5 THEN @type_swimming 
        WHEN 6 THEN @type_football 
        WHEN 7 THEN @type_running 
        WHEN 1 THEN @type_walking 
        ELSE @type_gym 
    END,
    @user_001, 
    DATE_ADD(CURDATE(), INTERVAL n DAY),
    CASE DAYOFWEEK(DATE_ADD(CURDATE(), INTERVAL n DAY)) 
        WHEN 2 THEN 60 WHEN 3 THEN 45 WHEN 5 THEN 50 WHEN 6 THEN 90 WHEN 7 THEN 60 WHEN 1 THEN 30 ELSE 45 
    END,
    'Scheduled session',
    CASE WHEN n < 0 THEN 'completed' ELSE 'planned' END,
    DATE_SUB(CURDATE(), INTERVAL 30 DAY)
FROM temp_numbers 
WHERE n BETWEEN -14 AND 28;

-- 9. Cleanup
DROP TEMPORARY TABLE IF EXISTS temp_numbers;