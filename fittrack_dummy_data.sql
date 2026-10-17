-- FitTrack synthetic North West England demonstration data
-- Purpose:
-- This file supplies synthetic test data for development and testing only.
-- It is designed for the FitTrack multi-tenant schema and assumes that the
-- schema and role/permission seed data have already been imported.
-- Important:
-- - All names, email addresses, locations, and activity records are fictional.
-- - The .test email domain is reserved for testing.
-- - Do not use this file with real personal data.
-- - Do not use these accounts in production.
-- - The password hash below is a development-only shared test hash.
-- - Replace or remove this file before final deployment if it is not required.
-- - The data is focused on fictional North West England tenants.
-- Expected schema:
-- fittrack_database_schema.sql
-- This file is intentionally deterministic so cross-tenant tests can be repeated.

USE fittrack;
START TRANSACTION;

-- Optional profile table for synthetic test attributes.
-- This table is separate from the core schema so the test-only age band,
-- locality, and gender fields do not become mandatory production data.
CREATE TABLE IF NOT EXISTS user_profiles (
    user_id BIGINT UNSIGNED NOT NULL,
    age_band ENUM('16-17', '18-24', '25-34', '35-44', '45-54', '55-64', '65-80') NOT NULL,
    gender_identity ENUM('female', 'male', 'non_binary', 'prefer_not_to_say') NOT NULL DEFAULT 'prefer_not_to_say',
    locality VARCHAR(80) NOT NULL,
    bio VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_dummy_profiles_user
    FOREIGN KEY (user_id) REFERENCES users (user_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- Fictional tenants focused on Greater Manchester, Merseyside, and the wider North West.
INSERT INTO tenants (tenant_name, tenant_slug, tenant_status)
VALUES
    ('Greater Manchester Active', 'greater-manchester-active', 'active'),
    ('Liverpool City Region Fitness', 'liverpool-city-region-fitness', 'active'),
    ('North West Community Sport', 'north-west-community-sport', 'active')
ON DUPLICATE KEY UPDATE
    tenant_name = VALUES(tenant_name),
    tenant_status = VALUES(tenant_status);

-- Development-only password hash.
-- This hash is for the password: TestPassword-ChangeMe-2026!
-- Change or remove all demonstration credentials before deployment.
SET @demo_password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC7hK3e5z9KpXKZq3K2K';

-- Synthetic users. Display names and email addresses are fictional.
INSERT INTO users
(username, email, password_hash, display_name, bio, account_status)
VALUES
    ('fit_super_001',  'super001@example.test', @demo_password_hash, 'Alex Morgan', 'Synthetic demonstration account.', 'active'),
    ('fit_super_002', 'super002@example.test', @demo_password_hash, 'Priya Shah', 'Synthetic demonstration account.', 'active'),
    ('fit_super_003', 'super003@example.test', @demo_password_hash, 'Daniel Hughes', 'Synthetic demonstration account.', 'active'), 
    ('fit_admin_001', 'admin001@example.test', @demo_password_hash, 'Emily Carter', 'Synthetic demonstration account.', 'active'),
    ('fit_admin_002', 'admin002@example.test', @demo_password_hash, 'Jordan Williams', 'Synthetic demonstration account.', 'active'),
    ('fit_admin_003', 'admin003@example.test', @demo_password_hash, 'Sophie Evans', 'Synthetic demonstration account.', 'active'),
    ('fit_admin_004', 'admin004@example.test', @demo_password_hash, 'Owen Taylor', 'Synthetic demonstration account.', 'active'),
    ('fit_admin_005', 'admin005@example.test', @demo_password_hash, 'Amelia Robinson', 'Synthetic demonstration account.', 'active'),
    ('fit_admin_006', 'admin006@example.test', @demo_password_hash, 'Thomas Davies', 'Synthetic demonstration account.', 'active'),
    ('fit_mod_001', 'moderator001@example.test', @demo_password_hash, 'Grace Wilson', 'Synthetic demonstration account.', 'active'),
    ('fit_mod_002', 'moderator002@example.test', @demo_password_hash, 'Noah Thompson', 'Synthetic demonstration account.', 'active'),
    ('fit_mod_003', 'moderator003@example.test', @demo_password_hash, 'Mia Roberts', 'Synthetic demonstration account.', 'active'),
    ('fit_mod_004', 'moderator004@example.test', @demo_password_hash, 'Oliver Johnson', 'Synthetic demonstration account.', 'active'),
    ('fit_mod_005', 'moderator005@example.test', @demo_password_hash, 'Isla Brown', 'Synthetic demonstration account.', 'active'),
    ('fit_mod_006', 'moderator006@example.test', @demo_password_hash, 'George Walker', 'Synthetic demonstration account.', 'active'),
    ('fit_user_001', 'user001@example.test', @demo_password_hash, 'Lily Wright', 'Synthetic demonstration account.', 'active'),
    ('fit_user_002', 'user002@example.test', @demo_password_hash, 'Arthur Wilson', 'Synthetic demonstration account.', 'active'),
    ('fit_user_003', 'user003@example.test', @demo_password_hash, 'Ella Thomas', 'Synthetic demonstration account.', 'active'),
    ('fit_user_004', 'user004@example.test', @demo_password_hash, 'Harry Green', 'Synthetic demonstration account.', 'active'),
    ('fit_user_005', 'user005@example.test', @demo_password_hash, 'Freya Hall', 'Synthetic demonstration account.', 'active'),
    ('fit_user_006', 'user006@example.test', @demo_password_hash, 'Jack Clarke', 'Synthetic demonstration account.', 'active'),
    ('fit_user_007', 'user007@example.test', @demo_password_hash, 'Evie Lewis', 'Synthetic demonstration account.', 'active'),
    ('fit_user_008', 'user008@example.test', @demo_password_hash, 'Charlie Harris', 'Synthetic demonstration account.', 'active'),
    ('fit_user_009', 'user009@example.test', @demo_password_hash, 'Poppy Baker', 'Synthetic demonstration account.', 'active'),
    ('fit_user_010', 'user010@example.test', @demo_password_hash, 'Oscar King', 'Synthetic demonstration account.', 'active'),
    ('fit_user_011', 'user011@example.test', @demo_password_hash, 'Isabelle Adams', 'Synthetic demonstration account.', 'active'),
    ('fit_user_012', 'user012@example.test', @demo_password_hash, 'Leo Allen', 'Synthetic demonstration account.', 'active'),
    ('fit_user_013', 'user013@example.test', @demo_password_hash, 'Daisy Scott', 'Synthetic demonstration account.', 'active'),
    ('fit_user_014', 'user014@example.test', @demo_password_hash, 'Arthur Young', 'Synthetic demonstration account.', 'active'),
    ('fit_user_015', 'user015@example.test', @demo_password_hash, 'Sienna Mitchell', 'Synthetic demonstration account.', 'active'),
    ('fit_user_016', 'user016@example.test', @demo_password_hash, 'Henry Collins', 'Synthetic demonstration account.', 'active'),
    ('fit_user_017', 'user017@example.test', @demo_password_hash, 'Ruby Stewart', 'Synthetic demonstration account.', 'active'),
    ('fit_user_018', 'user018@example.test', @demo_password_hash, 'Finley Morris', 'Synthetic demonstration account.', 'active'),
    ('fit_user_019', 'user019@example.test', @demo_password_hash, 'Ivy Rogers', 'Synthetic demonstration account.', 'active'),
    ('fit_user_020', 'user020@example.test', @demo_password_hash, 'William Reed', 'Synthetic demonstration account.', 'active'),
    ('fit_user_021', 'user021@example.test', @demo_password_hash, 'Maya Cook', 'Synthetic demonstration account.', 'active'),
    ('fit_user_022', 'user022@example.test', @demo_password_hash, 'Freddie Morgan', 'Synthetic demonstration account.', 'active'),
    ('fit_user_023', 'user023@example.test', @demo_password_hash, 'Esme Bell', 'Synthetic demonstration account.', 'active'),
    ('fit_user_024', 'user024@example.test', @demo_password_hash, 'Arthur Murphy', 'Synthetic demonstration account.', 'active'),
    ('fit_user_025', 'user025@example.test', @demo_password_hash, 'Florence Bailey', 'Synthetic demonstration account.', 'active'),
    ('fit_viewer_001', 'viewer001@example.test', @demo_password_hash, 'Lucy Miller', 'Synthetic demonstration account.', 'active'),
    ('fit_viewer_002', 'viewer002@example.test', @demo_password_hash, 'Theodore Cox', 'Synthetic demonstration account.', 'active'),
    ('fit_viewer_003', 'viewer003@example.test', @demo_password_hash, 'Matilda Richards', 'Synthetic demonstration account.', 'active'),
    ('fit_viewer_004', 'viewer004@example.test', @demo_password_hash, 'Archie Bailey', 'Synthetic demonstration account.', 'active'),
    ('fit_viewer_005', 'viewer005@example.test', @demo_password_hash, 'Alice Cooper', 'Synthetic demonstration account.', 'active'),
    ('fit_viewer_006', 'viewer006@example.test', @demo_password_hash, 'Edward Phillips', 'Synthetic demonstration account.', 'active'),
    ('fit_viewer_007', 'viewer007@example.test', @demo_password_hash, 'Ada Bennett', 'Synthetic demonstration account.', 'active'),
    ('fit_viewer_008', 'viewer008@example.test', @demo_password_hash, 'Arthur Foster', 'Synthetic demonstration account.', 'active'),
    ('fit_viewer_009', 'viewer009@example.test', @demo_password_hash, 'Chloe Gray', 'Synthetic demonstration account.', 'active'),
    ('fit_viewer_010', 'viewer010@example.test', @demo_password_hash, 'James Ellis', 'Synthetic demonstration account.', 'active')
ON DUPLICATE KEY UPDATE
    display_name = VALUES(display_name),
    bio = VALUES(bio),
    account_status = VALUES(account_status);

-- Synthetic profile attributes. Age is represented as an age band to minimise data.
INSERT INTO user_profiles (user_id, age_band, gender_identity, locality, bio)
SELECT u.user_id,
    CASE MOD(u.user_id, 7)
        WHEN 0 THEN '16-17' WHEN 1 THEN '18-24' WHEN 2 THEN '25-34'
        WHEN 3 THEN '35-44' WHEN 4 THEN '45-54' WHEN 5 THEN '55-64'
        ELSE '65-80'
    END,
    CASE MOD(u.user_id, 4)
        WHEN 0 THEN 'female' WHEN 1 THEN 'male' WHEN 2 THEN 'non_binary'
        ELSE 'prefer_not_to_say'
    END,
    CASE MOD(u.user_id, 12)
        WHEN 0 THEN 'Manchester' WHEN 1 THEN 'Salford' WHEN 2 THEN 'Trafford'
        WHEN 3 THEN 'Stockport' WHEN 4 THEN 'Oldham' WHEN 5 THEN 'Bolton'
        WHEN 6 THEN 'Liverpool' WHEN 7 THEN 'Wirral' WHEN 8 THEN 'Sefton'
        WHEN 9 THEN 'St Helens' WHEN 10 THEN 'Warrington' ELSE 'Preston'
    END,
    'Synthetic demonstration profile.'
FROM users u
WHERE u.email LIKE '%@example.test'
ON DUPLICATE KEY UPDATE
    age_band = VALUES(age_band),
    gender_identity = VALUES(gender_identity),
    locality = VALUES(locality),
    bio = VALUES(bio);

-- Role IDs.
SET @role_super = (SELECT role_id FROM roles WHERE role_key = 'super_administrator');
SET @role_admin = (SELECT role_id FROM roles WHERE role_key = 'administrator');
SET @role_mod = (SELECT role_id FROM roles WHERE role_key = 'moderator');
SET @role_user = (SELECT role_id FROM roles WHERE role_key = 'user');
SET @role_viewer = (SELECT role_id FROM roles WHERE role_key = 'viewer');

-- Tenant IDs.
SET @tenant_gm = (SELECT tenant_id FROM tenants WHERE tenant_slug = 'greater-manchester-active');
SET @tenant_merseyside = (SELECT tenant_id FROM tenants WHERE tenant_slug = 'liverpool-city-region-fitness');
SET @tenant_northwest = (SELECT tenant_id FROM tenants WHERE tenant_slug = 'north-west-community-sport');

-- Memberships for Tenant A: Greater Manchester Active.
INSERT INTO tenant_memberships (tenant_id, user_id, role_id, membership_status)
SELECT @tenant_gm, u.user_id,
    CASE
        WHEN u.username = 'fit_super_001' THEN @role_super
        WHEN u.username IN ('fit_admin_001', 'fit_admin_002') THEN @role_admin
        WHEN u.username IN ('fit_mod_001', 'fit_mod_002') THEN @role_mod
        WHEN u.username IN ('fit_viewer_001', 'fit_viewer_002', 'fit_viewer_003', 'fit_viewer_004', 'fit_viewer_005') THEN @role_viewer
        ELSE @role_user
    END,
    'active'
FROM users u
WHERE u.username IN (
    'fit_super_001', 'fit_admin_001', 'fit_admin_002',
    'fit_mod_001', 'fit_mod_002',
    'fit_user_001', 'fit_user_002', 'fit_user_003', 'fit_user_004', 'fit_user_005',
    'fit_user_006', 'fit_user_007', 'fit_user_008', 'fit_user_009', 'fit_user_010',
    'fit_viewer_001', 'fit_viewer_002', 'fit_viewer_003', 'fit_viewer_004', 'fit_viewer_005'
)
ON DUPLICATE KEY UPDATE
    role_id = VALUES(role_id),
    membership_status = VALUES(membership_status);

-- Memberships for Tenant B: Liverpool City Region Fitness.
INSERT INTO tenant_memberships (tenant_id, user_id, role_id, membership_status)
SELECT @tenant_merseyside, u.user_id,
    CASE
        WHEN u.username = 'fit_super_002' THEN @role_super
        WHEN u.username IN ('fit_admin_003', 'fit_admin_004') THEN @role_admin
        WHEN u.username IN ('fit_mod_003', 'fit_mod_004') THEN @role_mod
        WHEN u.username IN ('fit_viewer_006', 'fit_viewer_007', 'fit_viewer_008', 'fit_viewer_009', 'fit_viewer_010') THEN @role_viewer
        ELSE @role_user
    END,
    'active'
FROM users u
WHERE u.username IN (
    'fit_super_002', 'fit_admin_003', 'fit_admin_004',
    'fit_mod_003', 'fit_mod_004',
    'fit_user_011', 'fit_user_012', 'fit_user_013', 'fit_user_014', 'fit_user_015',
    'fit_user_016', 'fit_user_017', 'fit_user_018', 'fit_user_019', 'fit_user_020',
    'fit_viewer_006', 'fit_viewer_007', 'fit_viewer_008', 'fit_viewer_009', 'fit_viewer_010'
)
ON DUPLICATE KEY UPDATE
    role_id = VALUES(role_id),
    membership_status = VALUES(membership_status);

-- Memberships for Tenant C: North West Community Sport.
INSERT INTO tenant_memberships (tenant_id, user_id, role_id, membership_status)
SELECT @tenant_northwest, u.user_id,
    CASE
        WHEN u.username = 'fit_super_003' THEN @role_super
        WHEN u.username IN ('fit_admin_005', 'fit_admin_006') THEN @role_admin
        WHEN u.username IN ('fit_mod_005', 'fit_mod_006') THEN @role_mod
        ELSE @role_user
    END,
    'active'
FROM users u
WHERE u.username IN (
    'fit_super_003', 'fit_admin_005', 'fit_admin_006',
    'fit_mod_005', 'fit_mod_006',
    'fit_user_021', 'fit_user_022', 'fit_user_023', 'fit_user_024', 'fit_user_025'
)
ON DUPLICATE KEY UPDATE
    role_id = VALUES(role_id),
    membership_status = VALUES(membership_status);

-- Deliberate multi-tenant memberships for isolation and role-switching tests.
INSERT INTO tenant_memberships (tenant_id, user_id, role_id, membership_status)
SELECT @tenant_gm, u.user_id, @role_admin, 'active'
FROM users u WHERE u.username = 'fit_admin_005'
ON DUPLICATE KEY UPDATE role_id = VALUES(role_id), membership_status = VALUES(membership_status);

INSERT INTO tenant_memberships (tenant_id, user_id, role_id, membership_status)
SELECT @tenant_merseyside, u.user_id, @role_mod, 'active'
FROM users u WHERE u.username = 'fit_mod_006'
ON DUPLICATE KEY UPDATE role_id = VALUES(role_id), membership_status = VALUES(membership_status);

INSERT INTO tenant_memberships (tenant_id, user_id, role_id, membership_status)
SELECT @tenant_northwest, u.user_id, @role_viewer, 'active'
FROM users u WHERE u.username = 'fit_mod_006'
ON DUPLICATE KEY UPDATE role_id = VALUES(role_id), membership_status = VALUES(membership_status);

-- Tenant-scoped preferences.
INSERT INTO user_preferences
(tenant_id, user_id, distance_unit, weight_unit, temperature_unit, theme_preference, default_activity_visibility)
SELECT tm.tenant_id, tm.user_id,
    CASE WHEN MOD(tm.user_id, 5) = 0 THEN 'miles' ELSE 'km' END,
    CASE WHEN MOD(tm.user_id, 4) = 0 THEN 'stone_pounds' ELSE 'kg' END,
    CASE WHEN MOD(tm.user_id, 6) = 0 THEN 'fahrenheit' ELSE 'celsius' END,
    CASE WHEN MOD(tm.user_id, 3) = 0 THEN 'dark' WHEN MOD(tm.user_id, 3) = 1 THEN 'light' ELSE 'system' END,
    CASE WHEN MOD(tm.user_id, 7) = 0 THEN 'tenant' ELSE 'private' END
FROM tenant_memberships tm
WHERE tm.membership_status = 'active'
ON DUPLICATE KEY UPDATE
    distance_unit = VALUES(distance_unit),
    weight_unit = VALUES(weight_unit),
    temperature_unit = VALUES(temperature_unit),
    theme_preference = VALUES(theme_preference),
    default_activity_visibility = VALUES(default_activity_visibility);

-- =====================================================================
-- ROBUST SEED: Activity Types for all active tenants.
-- Uses INSERT IGNORE to safely skip existing rows and ensures is_published = 1.
-- =====================================================================
INSERT IGNORE INTO activity_types
(tenant_id, name, slug, category, measurement_model, default_unit, description, safety_notes, is_published, created_by_user_id)
SELECT tm.tenant_id, x.name, x.slug, x.category, x.measurement_model, x.default_unit,
    x.description, x.safety_notes, 1,
    (SELECT tm2.user_id FROM tenant_memberships tm2 WHERE tm2.tenant_id = tm.tenant_id AND tm2.membership_status = 'active' ORDER BY tm2.user_id LIMIT 1)
FROM (SELECT DISTINCT tenant_id FROM tenant_memberships WHERE membership_status = 'active') tm
CROSS JOIN (
    SELECT 'Gym' AS name, 'gym' AS slug, 'strength' AS category, 'gym' AS measurement_model, 'kg' AS default_unit,
    'Strength training, resistance exercise, machines, free weights, and bodyweight movements.' AS description,
    'Use equipment appropriate to your ability and follow venue guidance.' AS safety_notes
    UNION ALL SELECT 'Swimming', 'swimming', 'aquatic', 'swimming', 'metres',
    'Pool-based activity recorded using lengths, distance, stroke, and duration.',
    'Follow the swimming venue rules and exercise at an intensity appropriate to your ability.'
    UNION ALL SELECT 'Running', 'running', 'cardio', 'distance', 'km',
    'Outdoor or indoor running recorded using distance, duration, and intensity.',
    'Warm up before vigorous activity and increase intensity gradually.'
    UNION ALL SELECT 'Walking', 'walking', 'cardio', 'distance', 'km',
    'Walking activity recorded using distance, duration, steps, and intensity.',
    'Choose a route and pace appropriate to your ability and surroundings.'
    UNION ALL SELECT 'Football', 'football', 'sport', 'sport', 'minutes',
    'Football matches, training sessions, and casual games.',
    'Warm up, use suitable footwear, and follow the rules of the venue or organiser.'
    UNION ALL SELECT 'Other sport', 'other-sport', 'sport', 'sport', 'minutes',
    'Other sports and organised physical activities.',
    'Use appropriate equipment and follow the activity organiser guidance.'
    UNION ALL SELECT 'Other activity', 'other-activity', 'general', 'general', 'none',
    'A physical activity that does not fit the other categories.',
    'Choose an intensity and duration appropriate to your ability.'
) x;

-- Exercises for each tenant.
INSERT INTO exercises
(tenant_id, name, category, description, equipment, safety_notes, is_published, created_by_user_id)
SELECT t.tenant_id, x.name, x.category, x.description, x.equipment, x.safety_notes, TRUE,
    (SELECT tm.user_id FROM tenant_memberships tm WHERE tm.tenant_id = t.tenant_id AND tm.role_id IN (@role_super, @role_admin) ORDER BY tm.user_id LIMIT 1)
FROM tenants t
CROSS JOIN (
    SELECT 'Barbell squat' AS name, 'legs' AS category, 'A lower-body resistance exercise.' AS description, 'Barbell and rack' AS equipment, 'Use suitable loading and controlled technique.' AS safety_notes
    UNION ALL SELECT 'Bench press', 'chest', 'A horizontal pressing exercise.', 'Barbell and bench', 'Use a safe setup and appropriate assistance where required.'
    UNION ALL SELECT 'Seated row', 'back', 'A pulling exercise for the upper back.', 'Cable machine', 'Keep movement controlled and use suitable resistance.'
    UNION ALL SELECT 'Dumbbell shoulder press', 'shoulders', 'An overhead pressing exercise.', 'Dumbbells', 'Use a stable position and suitable weight.'
    UNION ALL SELECT 'Bodyweight lunge', 'legs', 'A bodyweight lower-body exercise.', 'None', 'Use a stable surface and controlled movement.'
) x
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    equipment = VALUES(equipment),
    safety_notes = VALUES(safety_notes),
    is_published = VALUES(is_published);

-- Manual general locations. No precise home addresses or coordinates are used.
INSERT INTO activity_locations (tenant_id, user_id, location_type, label)
SELECT tm.tenant_id, tm.user_id,
    CASE MOD(tm.user_id, 6)
        WHEN 0 THEN 'gym' WHEN 1 THEN 'swimming_pool' WHEN 2 THEN 'park'
        WHEN 3 THEN 'sports_centre' WHEN 4 THEN 'outdoor_route' ELSE 'other'
    END,
    CASE MOD(tm.user_id, 10)
        WHEN 0 THEN 'Manchester area' WHEN 1 THEN 'Salford area' WHEN 2 THEN 'Trafford area'
        WHEN 3 THEN 'Stockport area' WHEN 4 THEN 'Liverpool area' WHEN 5 THEN 'Wirral area'
        WHEN 6 THEN 'Sefton area' WHEN 7 THEN 'St Helens area' WHEN 8 THEN 'Warrington area'
        ELSE 'Preston area'
    END
FROM tenant_memberships tm
WHERE tm.membership_status = 'active'
AND tm.role_id IN (@role_user, @role_admin, @role_mod, @role_super)
ON DUPLICATE KEY UPDATE label = VALUES(label);

-- Reusable variables for generating activity records.
SET @activity_type_gym = 'gym';
SET @activity_type_running = 'running';
SET @activity_type_walking = 'walking';
SET @activity_type_swimming = 'swimming';
SET @activity_type_football = 'football';

-- Create a deterministic set of general activity logs for ordinary users.
INSERT INTO activity_logs
(tenant_id, user_id, activity_type_id, activity_date, title, duration_minutes, distance_km, steps, intensity, notes, visibility, location_id)
SELECT tm.tenant_id, tm.user_id, at.activity_type_id,
    DATE_SUB(CURRENT_DATE, INTERVAL MOD(tm.user_id, 45) DAY),
    CASE at.slug
        WHEN 'running' THEN 'North West training run'
        WHEN 'walking' THEN 'Local area walk'
        WHEN 'football' THEN 'Football training session'
        WHEN 'swimming' THEN 'Pool swimming session'
        ELSE 'Physical activity session'
    END,
    CASE at.slug
        WHEN 'running' THEN 25 + MOD(tm.user_id, 45)
        WHEN 'walking' THEN 20 + MOD(tm.user_id, 90)
        WHEN 'football' THEN 60 + MOD(tm.user_id, 35)
        WHEN 'swimming' THEN 25 + MOD(tm.user_id, 50)
        ELSE 30 + MOD(tm.user_id, 40)
    END,
    CASE at.slug
        WHEN 'running' THEN 3.00 + (MOD(tm.user_id, 80) / 10)
        WHEN 'walking' THEN 1.00 + (MOD(tm.user_id, 100) / 10)
        ELSE NULL
    END,
    CASE at.slug WHEN 'walking' THEN 1500 + (MOD(tm.user_id, 20) * 500) ELSE NULL END,
    CASE MOD(tm.user_id, 3) WHEN 0 THEN 'light' WHEN 1 THEN 'moderate' ELSE 'vigorous' END,
    'Synthetic demonstration activity record.',
    CASE WHEN MOD(tm.user_id, 7) = 0 THEN 'tenant' ELSE 'private' END,
    (SELECT MIN(al.location_id) FROM activity_locations al WHERE al.tenant_id = tm.tenant_id AND al.user_id = tm.user_id)
FROM tenant_memberships tm
JOIN activity_types at ON at.tenant_id = tm.tenant_id
WHERE tm.role_id IN (@role_user, @role_admin, @role_mod, @role_super)
AND at.slug IN ('running', 'walking', 'football', 'swimming')
AND MOD(tm.user_id, 2) = 0;

-- Swimming-specific child records for swimming activity logs.
INSERT INTO swimming_logs
(tenant_id, activity_log_id, pool_length_metres, pool_lengths, distance_metres, stroke, duration_minutes, notes)
SELECT al.tenant_id, al.activity_log_id,
    CASE MOD(al.activity_log_id, 3) WHEN 0 THEN 20.00 WHEN 1 THEN 25.00 ELSE 50.00 END,
    10 + MOD(al.activity_log_id, 40),
    (CASE MOD(al.activity_log_id, 3) WHEN 0 THEN 20.00 WHEN 1 THEN 25.00 ELSE 50.00 END) * (10 + MOD(al.activity_log_id, 40)),
    CASE MOD(al.activity_log_id, 5)
        WHEN 0 THEN 'freestyle' WHEN 1 THEN 'breaststroke' WHEN 2 THEN 'backstroke'
        WHEN 3 THEN 'mixed' ELSE 'other'
    END,
    al.duration_minutes, 'Synthetic swimming record.'
FROM activity_logs al
JOIN activity_types at ON at.tenant_id = al.tenant_id AND at.activity_type_id = al.activity_type_id
WHERE at.slug = 'swimming';

-- Sports-specific child records for football logs.
INSERT INTO sport_logs
(tenant_id, activity_log_id, sport_name, session_type, team_or_group, position_or_role, result, notes)
SELECT al.tenant_id, al.activity_log_id, 'Football',
    CASE MOD(al.activity_log_id, 3) WHEN 0 THEN 'match' WHEN 1 THEN 'training' ELSE 'casual_game' END,
    'Synthetic local sports group',
    CASE MOD(al.activity_log_id, 4) WHEN 0 THEN 'midfield' WHEN 1 THEN 'defence' WHEN 2 THEN 'forward' ELSE NULL END,
    CASE WHEN MOD(al.activity_log_id, 3) = 0 THEN 'Synthetic result' ELSE NULL END,
    'Synthetic football record.'
FROM activity_logs al
JOIN activity_types at ON at.tenant_id = al.tenant_id AND at.activity_type_id = al.activity_type_id
WHERE at.slug = 'football';

-- Create plans for a subset of ordinary users.
INSERT INTO plans
(tenant_id, user_id, title, start_date, end_date, description, status)
SELECT tm.tenant_id, tm.user_id, 'Weekly movement plan',
    CURRENT_DATE, DATE_ADD(CURRENT_DATE, INTERVAL 6 DAY),
    'Synthetic demonstration plan for testing.',
    CASE WHEN MOD(tm.user_id, 3) = 0 THEN 'active' ELSE 'draft' END
FROM tenant_memberships tm
WHERE tm.role_id = @role_user AND MOD(tm.user_id, 3) = 0;

INSERT INTO plan_items
(tenant_id, plan_id, activity_type_id, user_id, planned_date, planned_duration_minutes, target_distance_km, notes, completion_status)
SELECT p.tenant_id, p.plan_id, at.activity_type_id, p.user_id,
    DATE_ADD(p.start_date, INTERVAL MOD(p.plan_id, 6) DAY),
    CASE at.slug WHEN 'running' THEN 30 WHEN 'walking' THEN 45 WHEN 'swimming' THEN 30 ELSE 60 END,
    CASE at.slug WHEN 'running' THEN 5.00 WHEN 'walking' THEN 3.00 ELSE NULL END,
    'Synthetic plan item.',
    CASE WHEN MOD(p.plan_id, 3) = 0 THEN 'completed' ELSE 'planned' END
FROM plans p
JOIN activity_types at ON at.tenant_id = p.tenant_id
WHERE at.slug = CASE MOD(p.plan_id, 3) WHEN 0 THEN 'running' WHEN 1 THEN 'walking' ELSE 'swimming' END;

-- A small number of synthetic reports for moderator testing.
INSERT INTO reports
(tenant_id, reporter_user_id, target_user_id, target_type, target_id, reason, report_status)
SELECT tm.tenant_id, tm.user_id, NULL, 'activity_log', al.activity_log_id,
    'Synthetic moderation test report.',
    CASE WHEN MOD(al.activity_log_id, 2) = 0 THEN 'open' ELSE 'reviewed' END
FROM tenant_memberships tm
JOIN activity_logs al ON al.tenant_id = tm.tenant_id
WHERE tm.role_id IN (@role_user, @role_mod)
AND al.user_id <> tm.user_id
AND MOD(al.activity_log_id, 17) = 0
LIMIT 6;

-- Synthetic audit events for tenant-scoped audit-log testing.
INSERT INTO audit_logs
(tenant_id, user_id, action, entity_type, entity_id, outcome, ip_address, user_agent, description)
SELECT tm.tenant_id, tm.user_id,
    CASE MOD(tm.user_id, 4)
        WHEN 0 THEN 'login_success' WHEN 1 THEN 'profile_updated'
        WHEN 2 THEN 'activity_created' ELSE 'preferences_updated'
    END,
    'synthetic_test_record', tm.user_id, 'success', '192.0.2.10',
    'FitTrack synthetic test client', 'Synthetic audit event for development testing.'
FROM tenant_memberships tm
WHERE tm.membership_status = 'active';

COMMIT;
-- updates to lazy sritp to ensure rings populates correctly when user login.
UPDATE activity_logs 
SET activity_date = DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 7) DAY) 
WHERE user_id = 16 AND tenant_id = 3;

UPDATE daily_nutrition 
SET log_date = DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 7) DAY) 
WHERE user_id = 16 AND tenant_id = 3;
-- Suggested test accounts
-- All use the development-only password represented by @demo_password_hash:
-- super001@example.test
-- admin001@example.test
-- moderator001@example.test
-- user001@example.test
-- viewer001@example.test