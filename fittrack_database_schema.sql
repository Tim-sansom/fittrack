-- ==============================================================================
-- FitTrack Multi-Tenant Relational Database Schema (Final Production Version)
-- Target: MySQL 8.x / MariaDB 11.x / InnoDB
-- ==============================================================================
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS security_events;
DROP TABLE IF EXISTS rate_limits;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS recovery_codes;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS swimming_logs;
DROP TABLE IF EXISTS sport_logs;
DROP TABLE IF EXISTS gym_sets;
DROP TABLE IF EXISTS gym_session_exercises;
DROP TABLE IF EXISTS gym_sessions;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS plan_items;
DROP TABLE IF EXISTS plans;
DROP TABLE IF EXISTS activity_locations;
DROP TABLE IF EXISTS exercises;
DROP TABLE IF EXISTS activity_types;
DROP TABLE IF EXISTS user_preferences;
DROP TABLE IF EXISTS user_totp;
DROP TABLE IF EXISTS user_profiles;
DROP TABLE IF EXISTS tenant_memberships;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS tenants;
DROP TABLE IF EXISTS daily_nutrition;
DROP TABLE IF EXISTS kudos;
DROP TABLE IF EXISTS challenges;
SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- CORE TENANT & USER TABLES
-- ==============================================================================
CREATE TABLE tenants (
    tenant_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_name VARCHAR(100) NOT NULL,
    tenant_slug VARCHAR(100) NOT NULL,
    tenant_status ENUM('active', 'suspended', 'archived') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (tenant_id),
    UNIQUE KEY uq_tenants_slug (tenant_slug)
) ENGINE = InnoDB;

CREATE TABLE users (
    user_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(30) NOT NULL,
    email VARCHAR(254) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(80) NOT NULL,
    avatar_path VARCHAR(255) NULL,
    bio VARCHAR(500) NULL,
    date_of_birth_encrypted TEXT NULL,
    account_status ENUM('active', 'suspended', 'disabled') NOT NULL DEFAULT 'active',
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE = InnoDB;

-- ADDED: user_profiles table (Missing from original schema, required by Workouts page)
CREATE TABLE user_profiles (
    user_id BIGINT UNSIGNED NOT NULL,
    age_band ENUM('16-17','18-24','25-34','35-44','45-54','55-64','65-80') NOT NULL,
    gender_identity ENUM('female','male','non_binary','prefer_not_to_say') NOT NULL DEFAULT 'prefer_not_to_say',
    locality VARCHAR(80) NOT NULL,
    bio VARCHAR(500) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_dummy_profiles_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE roles (
    role_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    role_key VARCHAR(40) NOT NULL,
    role_name VARCHAR(60) NOT NULL,
    description VARCHAR(255) NOT NULL,
    PRIMARY KEY (role_id),
    UNIQUE KEY uq_roles_key (role_key)
) ENGINE = InnoDB;

CREATE TABLE permissions (
    permission_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    permission_key VARCHAR(80) NOT NULL,
    description VARCHAR(255) NOT NULL,
    PRIMARY KEY (permission_id),
    UNIQUE KEY uq_permissions_key (permission_key)
) ENGINE = InnoDB;

CREATE TABLE role_permissions (
    role_permission_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    role_id SMALLINT UNSIGNED NOT NULL,
    permission_id SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_permission_id),
    UNIQUE KEY uq_role_perm (role_id, permission_id),
    CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles (role_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions (permission_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE tenant_memberships (
    membership_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role_id SMALLINT UNSIGNED NOT NULL,
    membership_status ENUM('active', 'suspended', 'removed') NOT NULL DEFAULT 'active',
    joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (membership_id),
    UNIQUE KEY uq_membership_tenant_user (tenant_id, user_id),
    KEY idx_tm_user (user_id),
    KEY idx_tm_role (role_id),
    CONSTRAINT fk_tm_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_tm_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_tm_role FOREIGN KEY (role_id) REFERENCES roles (role_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

-- ==============================================================================
-- USER PREFERENCES & SECURITY
-- ==============================================================================
CREATE TABLE user_preferences (
    preference_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    distance_unit ENUM('km', 'miles') NOT NULL DEFAULT 'km',
    weight_unit ENUM('kg', 'stone_pounds') NOT NULL DEFAULT 'kg',
    temperature_unit ENUM('celsius', 'fahrenheit') NOT NULL DEFAULT 'celsius',
    theme_preference ENUM('system', 'light', 'dark') NOT NULL DEFAULT 'system',
    default_activity_visibility ENUM('private', 'tenant', 'public') NOT NULL DEFAULT 'private',
    notifications_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (preference_id),
    UNIQUE KEY uq_pref_tenant_user (tenant_id, user_id),
    CONSTRAINT fk_pref_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pref_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE user_totp (
    totp_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    secret_encrypted MEDIUMTEXT NOT NULL,
    is_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    setup_started_at DATETIME NULL,
    enabled_at DATETIME NULL,
    last_used_time_step BIGINT NULL,
    failed_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (totp_id),
    UNIQUE KEY uq_totp_tenant_user (tenant_id, user_id),
    CONSTRAINT fk_totp_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_totp_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- ==============================================================================
-- ACTIVITY CATALOGUE & LOCATIONS
-- ==============================================================================
CREATE TABLE activity_types (
    activity_type_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(60) NOT NULL,
    slug VARCHAR(60) NOT NULL,
    category ENUM('strength', 'cardio', 'aquatic', 'sport', 'general') NOT NULL,
    measurement_model ENUM('gym', 'swimming', 'distance', 'sport', 'duration', 'general') NOT NULL,
    default_unit ENUM('kg', 'km', 'metres', 'minutes', 'none') NOT NULL DEFAULT 'none',
    description TEXT NOT NULL,
    safety_notes TEXT NULL,
    is_published BOOLEAN NOT NULL DEFAULT FALSE,
    created_by_user_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (activity_type_id),
    UNIQUE KEY uq_at_tenant_slug (tenant_id, slug),
    KEY idx_at_tenant (tenant_id),
    KEY idx_at_creator (created_by_user_id),
    CONSTRAINT fk_at_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_at_creator FOREIGN KEY (created_by_user_id) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE exercises (
    exercise_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(60) NOT NULL,
    description TEXT NOT NULL,
    equipment VARCHAR(150) NULL,
    safety_notes TEXT NULL,
    is_published BOOLEAN NOT NULL DEFAULT FALSE,
    created_by_user_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (exercise_id),
    UNIQUE KEY uq_ex_tenant_name (tenant_id, name),
    KEY idx_ex_tenant (tenant_id),
    KEY idx_ex_creator (created_by_user_id),
    CONSTRAINT fk_ex_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ex_creator FOREIGN KEY (created_by_user_id) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE activity_locations (
    location_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    location_type ENUM('home', 'gym', 'swimming_pool', 'park', 'sports_centre', 'outdoor_route', 'other') NOT NULL,
    label VARCHAR(120) NULL,
    latitude_encrypted MEDIUMTEXT NULL,
    longitude_encrypted MEDIUMTEXT NULL,
    precision_level VARCHAR(30) NULL,
    consent_recorded_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (location_id),
    KEY idx_loc_tenant_user (tenant_id, user_id),
    CONSTRAINT fk_loc_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_loc_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- ==============================================================================
-- PLANNING & ACTIVITY LOGGING
-- ==============================================================================
CREATE TABLE plans (
    plan_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    description VARCHAR(500) NULL,
    status ENUM('draft', 'active', 'completed', 'archived') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (plan_id),
    KEY idx_pl_tenant_user (tenant_id, user_id),
    CONSTRAINT fk_pl_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pl_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE plan_items (
    plan_item_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    activity_type_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    planned_date DATE NOT NULL,
    planned_duration_minutes SMALLINT UNSIGNED NULL,
    target_distance_km DECIMAL(8,2) NULL,
    notes VARCHAR(500) NULL,
    completion_status ENUM('planned', 'completed', 'skipped') NOT NULL DEFAULT 'planned',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (plan_item_id),
    KEY idx_pi_plan (plan_id),
    KEY idx_pi_type (activity_type_id),
    CONSTRAINT fk_pi_plan FOREIGN KEY (plan_id) REFERENCES plans (plan_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pi_type FOREIGN KEY (activity_type_id) REFERENCES activity_types (activity_type_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_pi_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE activity_logs (
    activity_log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    activity_type_id BIGINT UNSIGNED NOT NULL,
    activity_date DATE NOT NULL,
    title VARCHAR(100) NOT NULL,
    duration_minutes SMALLINT UNSIGNED NULL,
    distance_km DECIMAL(8,2) NULL,
    steps INT UNSIGNED NULL,
    intensity ENUM('light', 'moderate', 'vigorous') NULL,
    notes VARCHAR(1000) NULL,
    visibility ENUM('private', 'tenant', 'public') NOT NULL DEFAULT 'private',
    location_id BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (activity_log_id),
    KEY idx_al_tenant_user_date (tenant_id, user_id, activity_date),
    KEY idx_al_type (activity_type_id),
    KEY idx_al_location (location_id),
    CONSTRAINT fk_al_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_al_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_al_type FOREIGN KEY (activity_type_id) REFERENCES activity_types (activity_type_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_al_location FOREIGN KEY (location_id) REFERENCES activity_locations (location_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

-- FIXED: daily_nutrition now includes macro columns directly in CREATE statement
CREATE TABLE daily_nutrition (
    nutrition_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    log_date DATE NOT NULL,
    calories_consumed INT UNSIGNED NOT NULL DEFAULT 0,
    calories_burned INT UNSIGNED NOT NULL DEFAULT 0,
    protein_g INT UNSIGNED NULL DEFAULT 0,
    carbs_g INT UNSIGNED NULL DEFAULT 0,
    fats_g INT UNSIGNED NULL DEFAULT 0,
    notes VARCHAR(500) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (nutrition_id),
    UNIQUE KEY uq_nutrition_user_date (tenant_id, user_id, log_date),
    CONSTRAINT fk_nutrition_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_nutrition_user FOREIGN KEY (tenant_id, user_id) REFERENCES tenant_memberships (tenant_id, user_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE kudos (
    kudos_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    activity_log_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (kudos_id),
    UNIQUE KEY uq_kudos_user_activity (user_id, activity_log_id),
    KEY idx_kudos_activity (activity_log_id),
    CONSTRAINT fk_kudos_activity FOREIGN KEY (activity_log_id) REFERENCES activity_logs (activity_log_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_kudos_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE challenges (
    challenge_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(100) NOT NULL,
    description VARCHAR(500) DEFAULT NULL,
    target_value INT UNSIGNED NOT NULL,
    current_value INT UNSIGNED NOT NULL DEFAULT 0,
    unit VARCHAR(20) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'completed', 'archived') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (challenge_id),
    KEY idx_challenges_tenant_user (tenant_id, user_id),
    CONSTRAINT fk_challenges_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_challenges_user FOREIGN KEY (tenant_id, user_id) REFERENCES tenant_memberships (tenant_id, user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- ==============================================================================
-- SPECIALISED ACTIVITY TRACKING (GYM, SWIMMING, SPORT)
-- ==============================================================================
CREATE TABLE gym_sessions (
    gym_session_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    activity_log_id BIGINT UNSIGNED NULL,
    session_date DATE NOT NULL,
    session_name VARCHAR(100) NOT NULL,
    duration_minutes SMALLINT UNSIGNED NULL,
    notes VARCHAR(1000) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (gym_session_id),
    UNIQUE KEY uq_gs_activity_log (activity_log_id),
    KEY idx_gs_tenant_user (tenant_id, user_id),
    CONSTRAINT fk_gs_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_gs_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_gs_al FOREIGN KEY (activity_log_id) REFERENCES activity_logs (activity_log_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE gym_session_exercises (
    session_exercise_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    gym_session_id BIGINT UNSIGNED NOT NULL,
    exercise_id BIGINT UNSIGNED NOT NULL,
    exercise_order SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    notes VARCHAR(500) NULL,
    PRIMARY KEY (session_exercise_id),
    UNIQUE KEY uq_gse_order (gym_session_id, exercise_order),
    KEY idx_gse_session (gym_session_id),
    KEY idx_gse_exercise (exercise_id),
    CONSTRAINT fk_gse_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_gse_session FOREIGN KEY (gym_session_id) REFERENCES gym_sessions (gym_session_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_gse_exercise FOREIGN KEY (exercise_id) REFERENCES exercises (exercise_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE gym_sets (
    set_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    session_exercise_id BIGINT UNSIGNED NOT NULL,
    set_number SMALLINT UNSIGNED NOT NULL,
    repetitions SMALLINT UNSIGNED NULL,
    weight_kg DECIMAL(8,2) NULL,
    duration_seconds INT UNSIGNED NULL,
    distance_km DECIMAL(8,2) NULL,
    completed BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (set_id),
    UNIQUE KEY uq_set_number (session_exercise_id, set_number),
    KEY idx_set_exercise (session_exercise_id),
    CONSTRAINT fk_set_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_set_exercise FOREIGN KEY (session_exercise_id) REFERENCES gym_session_exercises (session_exercise_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE swimming_logs (
    swimming_log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    activity_log_id BIGINT UNSIGNED NOT NULL,
    pool_length_metres DECIMAL(7,2) NOT NULL,
    pool_lengths INT UNSIGNED NOT NULL,
    distance_metres DECIMAL(10,2) NOT NULL,
    stroke ENUM('freestyle', 'breaststroke', 'backstroke', 'butterfly', 'mixed', 'other') NOT NULL,
    duration_minutes SMALLINT UNSIGNED NULL,
    notes VARCHAR(1000) NULL,
    PRIMARY KEY (swimming_log_id),
    UNIQUE KEY uq_sw_activity_log (activity_log_id),
    CONSTRAINT fk_sw_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_sw_al FOREIGN KEY (activity_log_id) REFERENCES activity_logs (activity_log_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE sport_logs (
    sport_log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    activity_log_id BIGINT UNSIGNED NOT NULL,
    sport_name VARCHAR(60) NOT NULL,
    session_type ENUM('match', 'training', 'casual_game', 'other') NOT NULL DEFAULT 'other',
    team_or_group VARCHAR(100) NULL,
    position_or_role VARCHAR(80) NULL,
    result VARCHAR(80) NULL,
    notes VARCHAR(1000) NULL,
    PRIMARY KEY (sport_log_id),
    UNIQUE KEY uq_sp_activity_log (activity_log_id),
    CONSTRAINT fk_sp_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_sp_al FOREIGN KEY (activity_log_id) REFERENCES activity_logs (activity_log_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- ==============================================================================
-- SECURITY, AUDIT & MODERATION
-- ==============================================================================
CREATE TABLE password_reset_tokens (
    reset_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    request_ip_hash CHAR(64) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (reset_id),
    UNIQUE KEY uq_prt_token_hash (token_hash),
    KEY idx_prt_user (user_id),
    CONSTRAINT fk_prt_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE recovery_codes (
    recovery_code_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    code_hash VARCHAR(255) NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (recovery_code_id),
    KEY idx_rc_tenant_user (tenant_id, user_id),
    CONSTRAINT fk_rc_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_rc_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE reports (
    report_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    reporter_user_id BIGINT UNSIGNED NOT NULL,
    target_user_id BIGINT UNSIGNED NULL,
    target_type VARCHAR(40) NOT NULL,
    target_id BIGINT UNSIGNED NOT NULL,
    reason VARCHAR(500) NOT NULL,
    report_status ENUM('open', 'reviewed', 'resolved', 'dismissed') NOT NULL DEFAULT 'open',
    moderator_user_id BIGINT UNSIGNED NULL,
    moderator_notes VARCHAR(1000) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    PRIMARY KEY (report_id),
    KEY idx_rep_tenant (tenant_id),
    KEY idx_rep_reporter (reporter_user_id),
    KEY idx_rep_target (target_user_id),
    KEY idx_rep_moderator (moderator_user_id),
    CONSTRAINT fk_rep_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_rep_reporter FOREIGN KEY (reporter_user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_rep_target FOREIGN KEY (target_user_id) REFERENCES users (user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_rep_moderator FOREIGN KEY (moderator_user_id) REFERENCES users (user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE security_events (
    security_event_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    event_type VARCHAR(60) NOT NULL,
    outcome ENUM('success', 'failure', 'denied') NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    details VARCHAR(1000) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (security_event_id),
    KEY idx_se_tenant (tenant_id),
    CONSTRAINT fk_se_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE audit_logs (
    audit_log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(60) NOT NULL,
    entity_type VARCHAR(60) NULL,
    entity_id BIGINT UNSIGNED NULL,
    outcome ENUM('success', 'failure', 'denied') NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    description VARCHAR(1000) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (audit_log_id),
    KEY idx_al_audit_tenant (tenant_id),
    KEY idx_al_audit_user (user_id),
    CONSTRAINT fk_al_audit_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_al_audit_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE rate_limits (
    rate_limit_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NULL,
    action VARCHAR(60) NOT NULL,
    subject_type ENUM('ip', 'username', 'user_id', 'session') NOT NULL,
    subject_hash CHAR(64) NOT NULL,
    window_start DATETIME NOT NULL,
    attempt_count INT UNSIGNED NOT NULL DEFAULT 0,
    blocked_until DATETIME NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (rate_limit_id),
    UNIQUE KEY uq_rl_bucket (tenant_id, action, subject_type, subject_hash),
    KEY idx_rl_blocked (blocked_until),
    CONSTRAINT fk_rl_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (tenant_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- ==============================================================================
-- BASE SEED DATA (Roles, Permissions, Default Tenants)
-- ==============================================================================
INSERT INTO roles (role_key, role_name, description) VALUES
('super_administrator', 'Super administrator', 'Full control within an assigned tenant'),
('administrator', 'Administrator', 'Manages tenant content and selected users'),
('moderator', 'Moderator', 'Reviews reports and permitted content'),
('user', 'User', 'Records and manages personal physical activities'),
('viewer', 'Viewer', 'Views permitted published content without editing records');

INSERT INTO permissions (permission_key, description) VALUES
('view_public_content', 'View published public content'),
('view_own_profile', 'View the current user profile'),
('edit_own_profile', 'Edit the current user profile'),
('create_activity', 'Create personal activity records'),
('edit_own_activity', 'Edit personal activity records'),
('delete_own_activity', 'Delete personal activity records'),
('manage_activity_content', 'Create, edit, publish, and remove activity content'),
('manage_exercises', 'Create, edit, publish, and remove exercises'),
('review_reports', 'Review and resolve tenant reports'),
('manage_users', 'Manage tenant user memberships and statuses'),
('manage_roles', 'Assign tenant-scoped roles'),
('view_audit_logs', 'View tenant audit logs');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id FROM roles r CROSS JOIN permissions p
WHERE r.role_key = 'viewer' AND p.permission_key IN ('view_public_content');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id FROM roles r CROSS JOIN permissions p
WHERE r.role_key = 'user' AND p.permission_key IN ('view_public_content', 'view_own_profile', 'edit_own_profile', 'create_activity', 'edit_own_activity', 'delete_own_activity');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id FROM roles r CROSS JOIN permissions p
WHERE r.role_key = 'moderator' AND p.permission_key IN ('view_public_content', 'view_own_profile', 'edit_own_profile', 'create_activity', 'edit_own_activity', 'delete_own_activity', 'review_reports');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id FROM roles r CROSS JOIN permissions p
WHERE r.role_key = 'administrator' AND p.permission_key IN ('view_public_content', 'view_own_profile', 'edit_own_profile', 'create_activity', 'edit_own_activity', 'delete_own_activity', 'manage_activity_content', 'manage_exercises', 'review_reports', 'manage_users');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id FROM roles r CROSS JOIN permissions p
WHERE r.role_key = 'super_administrator';

INSERT INTO tenants (tenant_name, tenant_slug) VALUES
('North Yorkshire Fitness', 'north-yorkshire-fitness'),
('West Yorkshire Fitness', 'west-yorkshire-fitness');