-- ============================================
-- FitTrack Dummy Data Part 2
-- ============================================

-- 1. Drop and recreate tables to ensure correct columns exist
DROP TABLE IF EXISTS nutrition_logs;
CREATE TABLE nutrition_logs (
    nutrition_log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    log_date DATE NOT NULL,
    calories_consumed INT UNSIGNED NOT NULL DEFAULT 0,
    calories_burned INT UNSIGNED NOT NULL DEFAULT 0,
    protein_g INT UNSIGNED NULL,
    carbs_g INT UNSIGNED NULL,
    fat_g INT UNSIGNED NULL,
    water_litres DECIMAL(4,1) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (nutrition_log_id),
    KEY idx_nutrition_tenant_user_date (tenant_id, user_id, log_date),
    CONSTRAINT fk_nutrition_logs_tenant 
        FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_nutrition_logs_membership 
        FOREIGN KEY (tenant_id, user_id) REFERENCES tenant_memberships (tenant_id, user_id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

DROP TABLE IF EXISTS user_measurements;
CREATE TABLE user_measurements (
    measurement_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    weight_kg DECIMAL(5,1) NULL,
    height_cm DECIMAL(5,1) NULL,
    bmi DECIMAL(4,1) NULL,
    chest_cm DECIMAL(5,1) NULL,
    waist_cm DECIMAL(5,1) NULL,
    hips_cm DECIMAL(5,1) NULL,
    left_arm_cm DECIMAL(5,1) NULL,
    right_arm_cm DECIMAL(5,1) NULL,
    left_leg_cm DECIMAL(5,1) NULL,
    right_leg_cm DECIMAL(5,1) NULL,
    recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (measurement_id),
    KEY idx_measurements_user (user_id),
    CONSTRAINT fk_measurements_user 
        FOREIGN KEY (user_id) REFERENCES users (user_id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

DROP TABLE IF EXISTS challenges;
CREATE TABLE challenges (
    challenge_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(100) NOT NULL,
    description VARCHAR(500) NULL,
    target_value INT UNSIGNED NOT NULL,
    current_value INT UNSIGNED NOT NULL DEFAULT 0,
    unit VARCHAR(20) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'completed', 'archived') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (challenge_id),
    KEY idx_challenges_tenant_user (tenant_id, user_id),
    CONSTRAINT fk_challenges_tenant 
        FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_challenges_user 
        FOREIGN KEY (tenant_id, user_id) REFERENCES tenant_memberships (tenant_id, user_id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

DROP TABLE IF EXISTS kudos;
CREATE TABLE kudos (
    kudos_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    activity_log_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (kudos_id),
    UNIQUE KEY uq_kudos_user_activity (user_id, activity_log_id),
    KEY idx_kudos_activity (activity_log_id),
    CONSTRAINT fk_kudos_activity
        FOREIGN KEY (activity_log_id) REFERENCES activity_logs (activity_log_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_kudos_user
        FOREIGN KEY (user_id) REFERENCES users (user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- 2. Set variables
SET @tenant_id = (SELECT tenant_id FROM tenants WHERE tenant_slug = 'greater-manchester-active' LIMIT 1);
SET @user_001 = (SELECT user_id FROM users WHERE username = 'fit_user_001' LIMIT 1);

-- 3. Fetch activity type IDs into variables
SET @type_running = (SELECT activity_type_id FROM activity_types WHERE tenant_id = @tenant_id AND slug = 'running' LIMIT 1);
SET @type_gym = (SELECT activity_type_id FROM activity_types WHERE tenant_id = @tenant_id AND slug = 'gym' LIMIT 1);
SET @type_swimming = (SELECT activity_type_id FROM activity_types WHERE tenant_id = @tenant_id AND slug = 'swimming' LIMIT 1);
SET @type_walking = (SELECT activity_type_id FROM activity_types WHERE tenant_id = @tenant_id AND slug = 'walking' LIMIT 1);
SET @type_football = (SELECT activity_type_id FROM activity_types WHERE tenant_id = @tenant_id AND slug = 'football' LIMIT 1);

-- 4. Create helper table for dates
DROP TEMPORARY TABLE IF EXISTS temp_numbers;
CREATE TEMPORARY TABLE temp_numbers (n INT);
INSERT INTO temp_numbers 
SELECT @row := @row + 1 AS n
FROM (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
     (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b,
     (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) c,
     (SELECT @row := 0) r
LIMIT 365;

-- 5. Activity Logs
INSERT INTO activity_logs (tenant_id, user_id, activity_type_id, activity_date, title, duration_minutes, distance_km, intensity, visibility, created_at)
SELECT 
    @tenant_id, @user_001,
    CASE FLOOR(RAND() * 5) WHEN 0 THEN @type_running WHEN 1 THEN @type_gym WHEN 2 THEN @type_swimming WHEN 3 THEN @type_walking WHEN 4 THEN @type_football END,
    DATE_SUB(CURDATE(), INTERVAL n DAY),
    CONCAT(CASE FLOOR(RAND() * 5) WHEN 0 THEN 'Morning' WHEN 1 THEN 'Evening' WHEN 2 THEN 'Afternoon' WHEN 3 THEN 'Lunch' WHEN 4 THEN 'Night' END, ' ', CASE FLOOR(RAND() * 5) WHEN 0 THEN 'run' WHEN 1 THEN 'session' WHEN 2 THEN 'workout' WHEN 3 THEN 'training' WHEN 4 THEN 'practice' END),
    FLOOR(20 + RAND() * 100),
    CASE WHEN RAND() > 0.5 THEN ROUND(1 + RAND() * 15, 2) ELSE NULL END,
    CASE FLOOR(RAND() * 3) WHEN 0 THEN 'light' WHEN 1 THEN 'moderate' WHEN 2 THEN 'vigorous' END,
    CASE FLOOR(RAND() * 3) WHEN 0 THEN 'private' WHEN 1 THEN 'tenant' WHEN 2 THEN 'public' END,
    DATE_SUB(CURDATE(), INTERVAL n DAY)
FROM temp_numbers WHERE RAND() > 0.4 AND n <= 365;

-- 6. Nutrition Logs (Now guaranteed to have the correct columns)
INSERT INTO nutrition_logs (tenant_id, user_id, log_date, calories_consumed, calories_burned, protein_g, carbs_g, fat_g, water_litres, created_at)
SELECT 
    @tenant_id, @user_001, DATE_SUB(CURDATE(), INTERVAL n DAY),
    FLOOR(1800 + RAND() * 700), FLOOR(300 + RAND() * 500),
    FLOOR(80 + RAND() * 70), FLOOR(200 + RAND() * 150), FLOOR(50 + RAND() * 40),
    ROUND(1.5 + RAND() * 1.5, 1), DATE_SUB(CURDATE(), INTERVAL n DAY)
FROM temp_numbers WHERE n <= 365;

-- 7. User Measurements
INSERT INTO user_measurements (user_id, weight_kg, height_cm, bmi, chest_cm, waist_cm, hips_cm, left_arm_cm, right_arm_cm, left_leg_cm, right_leg_cm, recorded_at)
SELECT 
    @user_001, ROUND(70 + (n / 365) * 5 + RAND() * 2 - 1, 1), 175, ROUND((70 + (n / 365) * 5) / POW(1.75, 2), 1),
    ROUND(95 + RAND() * 5, 1), ROUND(80 + RAND() * 4, 1), ROUND(98 + RAND() * 5, 1),
    ROUND(32 + RAND() * 2, 1), ROUND(32 + RAND() * 2, 1), ROUND(55 + RAND() * 3, 1), ROUND(55 + RAND() * 3, 1),
    DATE_SUB(CURDATE(), INTERVAL n DAY)
FROM temp_numbers WHERE n <= 180 AND n % 14 = 0;

-- 8. Kudos
INSERT INTO kudos (user_id, activity_log_id, created_at)
SELECT tm2.user_id, al.activity_log_id, al.created_at
FROM activity_logs al
JOIN tenant_memberships tm1 ON al.user_id = tm1.user_id AND al.tenant_id = tm1.tenant_id
JOIN tenant_memberships tm2 ON tm2.tenant_id = al.tenant_id AND tm2.user_id != al.user_id
WHERE al.tenant_id = @tenant_id AND al.visibility IN ('tenant', 'public') AND RAND() > 0.85
LIMIT 50;

-- 9. Challenges
INSERT INTO challenges (tenant_id, user_id, title, description, target_value, current_value, unit, start_date, end_date, status, created_at)
VALUES 
(@tenant_id, @user_001, '100km Running Challenge', 'Run 100km in 3 months', 100, 67, 'km', DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'active', DATE_SUB(CURDATE(), INTERVAL 60 DAY)),
(@tenant_id, @user_001, '30 Workouts in 30 Days', 'Complete 30 workouts', 30, 22, 'sessions', DATE_SUB(CURDATE(), INTERVAL 22 DAY), DATE_ADD(CURDATE(), INTERVAL 8 DAY), 'active', DATE_SUB(CURDATE(), INTERVAL 22 DAY)),
(@tenant_id, @user_001, 'Burn 10,000 Calories', 'Burn 10k calories this month', 10000, 7500, 'kcal', DATE_FORMAT(CURDATE(), '%Y-%m-01'), LAST_DAY(CURDATE()), 'active', DATE_FORMAT(CURDATE(), '%Y-%m-01'));

-- 10. Plans
INSERT INTO plans (tenant_id, user_id, title, description, start_date, end_date, status, created_at)
VALUES (@tenant_id, @user_001, 'Weekly Training Plan', 'Balanced weekly routine', DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'active', DATE_SUB(CURDATE(), INTERVAL 30 DAY));

SET @plan_id = LAST_INSERT_ID();

INSERT INTO plan_items (tenant_id, plan_id, activity_type_id, user_id, planned_date, planned_duration_minutes, notes, completion_status, created_at)
SELECT 
    @tenant_id, @plan_id,
    CASE DAYOFWEEK(DATE_ADD(CURDATE(), INTERVAL n DAY)) WHEN 2 THEN @type_gym WHEN 3 THEN @type_running WHEN 5 THEN @type_swimming WHEN 6 THEN @type_football WHEN 7 THEN @type_running WHEN 1 THEN @type_walking ELSE @type_gym END,
    @user_001, DATE_ADD(CURDATE(), INTERVAL n DAY),
    CASE DAYOFWEEK(DATE_ADD(CURDATE(), INTERVAL n DAY)) WHEN 2 THEN 60 WHEN 3 THEN 45 WHEN 5 THEN 50 WHEN 6 THEN 90 WHEN 7 THEN 60 WHEN 1 THEN 30 ELSE 45 END,
    'Scheduled session',
    CASE WHEN n < 0 THEN 'completed' ELSE 'planned' END,
    DATE_SUB(CURDATE(), INTERVAL 30 DAY)
FROM temp_numbers WHERE n BETWEEN -14 AND 28;

-- 11. Security Events
INSERT INTO security_events (tenant_id, user_id, event_type, outcome, ip_address, user_agent, created_at)
SELECT 
    @tenant_id, @user_001,
    CASE FLOOR(RAND() * 4) WHEN 0 THEN 'login' WHEN 1 THEN 'password_changed' WHEN 2 THEN 'profile_updated' WHEN 3 THEN '2fa_enabled' END,
    'success', CONCAT('192.168.1.', FLOOR(1 + RAND() * 254)), 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
    DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 30) DAY)
FROM temp_numbers WHERE n <= 15;

-- Cleanup
DROP TEMPORARY TABLE IF EXISTS temp_numbers;