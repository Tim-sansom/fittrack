<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Session.php';
require_once __DIR__ . '/../config/Database.php';
Session::start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id'];
$tenantId = (int)$_SESSION['tenant_id'];
$message = '';
$messageType = '';

// Fetch user data
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = :id");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch or create profile
$stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = :id");
$stmt->execute(['id' => $userId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$profile) {
    $db->prepare("INSERT INTO user_profiles (user_id, age_band, gender_identity, locality, bio) VALUES (:id, '25-34', 'prefer_not_to_say', '', '')")->execute(['id' => $userId]);
    $profile = ['age_band' => '25-34', 'gender_identity' => 'prefer_not_to_say', 'locality' => '', 'bio' => ''];
}

// Fetch measurements
$stmt = $db->prepare("SELECT * FROM user_measurements WHERE user_id = :id ORDER BY recorded_at DESC LIMIT 1");
$stmt->execute(['id' => $userId]);
$measurements = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    // 1. Update Profile & Avatar
    if (isset($_POST['update_profile'])) {
        $displayName = trim($_POST['display_name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $ageBand = $_POST['age_band'] ?? '25-34';
        $genderIdentity = $_POST['gender_identity'] ?? 'prefer_not_to_say';
        $locality = trim($_POST['locality'] ?? '');
        $avatarPath = $user['avatar_path'];

        // Handle Avatar Upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/avatars/';
            $fileExt = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($fileExt, $allowedExts)) {
                $newFileName = 'user_' . $userId . '_' . time() . '.' . $fileExt;
                $uploadPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                    // Delete old avatar if exists
                    if ($avatarPath && file_exists(__DIR__ . $avatarPath)) {
                        unlink(__DIR__ . $avatarPath);
                    }
                    $avatarPath = '/uploads/avatars/' . $newFileName;
                }
            }
        }

        $stmt = $db->prepare("UPDATE users SET display_name = :name, bio = :bio, avatar_path = :avatar WHERE user_id = :id");
        $stmt->execute(['name' => $displayName, 'bio' => $bio, 'avatar' => $avatarPath, 'id' => $userId]);

        $stmt = $db->prepare("UPDATE user_profiles SET age_band = :age, gender_identity = :gender, locality = :locality WHERE user_id = :id");
        $stmt->execute(['age' => $ageBand, 'gender' => $genderIdentity, 'locality' => $locality, 'id' => $userId]);

        $message = 'Profile updated successfully.';
        $messageType = 'success';
        // Refresh user data
        $user['display_name'] = $displayName;
        $user['bio'] = $bio;
        $user['avatar_path'] = $avatarPath;
    }

    // 2. Update Measurements
    if (isset($_POST['update_measurements'])) {
        $heightCm = (float)($_POST['height_cm'] ?? 0);
        $weightKg = (float)($_POST['weight_kg'] ?? 0);
        $chestCm = (float)($_POST['chest_cm'] ?? 0);
        $waistCm = (float)($_POST['waist_cm'] ?? 0);
        $hipsCm = (float)($_POST['hips_cm'] ?? 0);
        $leftArmCm = (float)($_POST['left_arm_cm'] ?? 0);
        $rightArmCm = (float)($_POST['right_arm_cm'] ?? 0);
        $leftLegCm = (float)($_POST['left_leg_cm'] ?? 0);
        $rightLegCm = (float)($_POST['right_leg_cm'] ?? 0);

        $bmi = $heightCm > 0 ? round($weightKg / pow($heightCm / 100, 2), 1) : 0;

        $stmt = $db->prepare("
            INSERT INTO user_measurements 
            (user_id, height_cm, weight_kg, chest_cm, waist_cm, hips_cm, left_arm_cm, right_arm_cm, left_leg_cm, right_leg_cm, bmi)
            VALUES (:uid, :height, :weight, :chest, :waist, :hips, :larm, :rarm, :lleg, :rleg, :bmi)
            ON DUPLICATE KEY UPDATE
            height_cm = :height, weight_kg = :weight, chest_cm = :chest, waist_cm = :waist,
            hips_cm = :hips, left_arm_cm = :larm, right_arm_cm = :rarm, left_leg_cm = :lleg, right_leg_cm = :rleg, bmi = :bmi
        ");
        $stmt->execute([
            'uid' => $userId, 'height' => $heightCm, 'weight' => $weightKg,
            'chest' => $chestCm, 'waist' => $waistCm, 'hips' => $hipsCm,
            'larm' => $leftArmCm, 'rarm' => $rightArmCm, 'lleg' => $leftLegCm, 'rleg' => $rightLegCm,
            'bmi' => $bmi
        ]);

        $message = 'Measurements updated. BMI: ' . $bmi;
        $messageType = 'success';
    }
}

function getBmiCategory($bmi) {
    if ($bmi < 18.5) return 'Underweight';
    if ($bmi < 25) return 'Normal weight';
    if ($bmi < 30) return 'Overweight';
    return 'Obese';
}
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | FitTrack</title>
    <link rel="stylesheet" href="/css/dashboard.css">
    <style>
        .profile-header { display: flex; align-items: center; gap: 2rem; margin-bottom: 2rem; }
        .avatar-container { position: relative; width: 120px; height: 120px; }
        .avatar-img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 4px solid var(--card); box-shadow: 0 4px 12px var(--shadow); }
        .avatar-upload { position: absolute; bottom: 0; right: 0; background: var(--accent); color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .unit-toggle { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; }
        .unit-toggle button { padding: 0.5rem 1rem; border: 1px solid var(--border); background: var(--card); color: var(--text); border-radius: 6px; cursor: pointer; font-weight: 500; }
        .unit-toggle button.active { background: var(--accent); color: white; border-color: var(--accent); }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
        .bmi-indicator { padding: 1rem; border-radius: 8px; text-align: center; font-weight: 600; margin-top: 1rem; }
        .bmi-underweight { background: #e3f2fd; color: #1976d2; }
        .bmi-normal { background: #e8f5e9; color: #388e3c; }
        .bmi-overweight { background: #fff3e0; color: #f57c00; }
        .bmi-obese { background: #ffebee; color: #d32f2f; }
    </style>
</head>
<body>
    <?php $pageTitle = 'My Profile'; require_once __DIR__ . '/includes/header.php'; ?>

    <div class="profile-header">
        <div class="avatar-container">
            <img src="<?= $user['avatar_path'] ? htmlspecialchars($user['avatar_path']) : 'https://via.placeholder.com/120' ?>" alt="Profile Avatar" class="avatar-img" id="avatarPreview">
            <label for="avatarInput" class="avatar-upload" title="Change Avatar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
            </label>
            <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
        </div>
        <div>
            <h1><?= htmlspecialchars($user['display_name'] ?? 'User') ?></h1>
            <p style="color: var(--text); opacity: 0.7;"><?= htmlspecialchars($profile['locality'] ?? '') ?></p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="card" style="background: <?= $messageType === 'success' ? 'rgba(76, 175, 80, 0.1)' : 'rgba(226, 105, 114, 0.1)' ?>; color: <?= $messageType === 'success' ? 'var(--success)' : 'var(--alert)' ?>; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-grid" style="grid-template-columns: 1fr;">
        <div class="card">
            <h2>Personal Information</h2>
            <form method="POST" action="profile.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="stats-grid">
                    <div class="form-group">
                        <label for="display_name">Display Name</label>
                        <input type="text" id="display_name" name="display_name" value="<?= htmlspecialchars($user['display_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="locality">Locality</label>
                        <input type="text" id="locality" name="locality" value="<?= htmlspecialchars($profile['locality'] ?? '') ?>" placeholder="e.g. Manchester">
                    </div>
                    <div class="form-group">
                        <label for="age_band">Age Band</label>
                        <select id="age_band" name="age_band">
                            <?php foreach (['16-17', '18-24', '25-34', '35-44', '45-54', '55-64', '65-80'] as $band): ?>
                                <option value="<?= $band ?>" <?= ($profile['age_band'] ?? '') === $band ? 'selected' : '' ?>><?= $band ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="gender_identity">Gender Identity</label>
                        <select id="gender_identity" name="gender_identity">
                            <?php foreach (['female', 'male', 'non_binary', 'prefer_not_to_say'] as $gender): ?>
                                <option value="<?= $gender ?>" <?= ($profile['gender_identity'] ?? '') === $gender ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $gender)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="3"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn-primary" style="width: auto; padding: 0.75rem 1.5rem;">Update Profile</button>
            </form>
        </div>

        <div class="card">
            <h2>Body Measurements</h2>
            <div class="unit-toggle">
                <button type="button" class="active" id="btn-metric" onclick="setUnits('metric')">Metric (cm/kg)</button>
                <button type="button" id="btn-imperial" onclick="setUnits('imperial')">Imperial (in/st)</button>
            </div>
            
            <form method="POST" action="profile.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="update_measurements" value="1">
                
                <div class="stats-grid">
                    <div class="form-group">
                        <label>Height <span class="unit-label">(cm)</span></label>
                        <input type="number" step="0.1" name="height_cm" value="<?= $measurements['height_cm'] ?? '' ?>" data-unit="cm" placeholder="e.g. 175">
                    </div>
                    <div class="form-group">
                        <label>Weight <span class="unit-label">(kg)</span></label>
                        <input type="number" step="0.1" name="weight_kg" value="<?= $measurements['weight_kg'] ?? '' ?>" data-unit="kg" placeholder="e.g. 70">
                    </div>
                    <div class="form-group">
                        <label>Chest <span class="unit-label">(cm)</span></label>
                        <input type="number" step="0.1" name="chest_cm" value="<?= $measurements['chest_cm'] ?? '' ?>" data-unit="cm">
                    </div>
                    <div class="form-group">
                        <label>Waist <span class="unit-label">(cm)</span></label>
                        <input type="number" step="0.1" name="waist_cm" value="<?= $measurements['waist_cm'] ?? '' ?>" data-unit="cm">
                    </div>
                    <div class="form-group">
                        <label>Hips <span class="unit-label">(cm)</span></label>
                        <input type="number" step="0.1" name="hips_cm" value="<?= $measurements['hips_cm'] ?? '' ?>" data-unit="cm">
                    </div>
                    <div class="form-group">
                        <label>Left Arm <span class="unit-label">(cm)</span></label>
                        <input type="number" step="0.1" name="left_arm_cm" value="<?= $measurements['left_arm_cm'] ?? '' ?>" data-unit="cm">
                    </div>
                    <div class="form-group">
                        <label>Right Arm <span class="unit-label">(cm)</span></label>
                        <input type="number" step="0.1" name="right_arm_cm" value="<?= $measurements['right_arm_cm'] ?? '' ?>" data-unit="cm">
                    </div>
                    <div class="form-group">
                        <label>Left Leg <span class="unit-label">(cm)</span></label>
                        <input type="number" step="0.1" name="left_leg_cm" value="<?= $measurements['left_leg_cm'] ?? '' ?>" data-unit="cm">
                    </div>
                    <div class="form-group">
                        <label>Right Leg <span class="unit-label">(cm)</span></label>
                        <input type="number" step="0.1" name="right_leg_cm" value="<?= $measurements['right_leg_cm'] ?? '' ?>" data-unit="cm">
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="width: auto; padding: 0.75rem 1.5rem;">Update Measurements</button>
            </form>

            <?php if ($measurements && ($measurements['bmi'] ?? 0) > 0): 
                $bmi = $measurements['bmi'];
                $bmiCat = getBmiCategory($bmi);
                $bmiClass = 'bmi-' . strtolower(str_replace(' ', '-', $bmiCat));
            ?>
                <div class="bmi-indicator <?= $bmiClass ?>">
                    BMI: <?= $bmi ?> - <?= $bmiCat ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => document.getElementById('avatarPreview').src = e.target.result;
                reader.readAsDataURL(input.files[0]);
            }
        }

        function setUnits(unit) {
            document.getElementById('btn-metric').classList.toggle('active', unit === 'metric');
            document.getElementById('btn-imperial').classList.toggle('active', unit === 'imperial');
            
            const inputs = document.querySelectorAll('input[data-unit]');
            const labels = document.querySelectorAll('.unit-label');
            
            inputs.forEach(input => {
                const val = parseFloat(input.value);
                if (isNaN(val)) return;
                
                if (unit === 'imperial') {
                    if (input.dataset.unit === 'cm') input.value = (val * 0.393701).toFixed(1);
                    if (input.dataset.unit === 'kg') input.value = (val * 0.157473).toFixed(1); // to stone
                } else {
                    if (input.dataset.unit === 'cm' && input.value !== (val * 0.393701).toFixed(1)) input.value = (val / 0.393701).toFixed(1);
                    if (input.dataset.unit === 'kg' && input.value !== (val * 0.157473).toFixed(1)) input.value = (val / 0.157473).toFixed(1);
                }
            });

            labels.forEach(label => {
                if (unit === 'imperial') {
                    if (label.textContent.includes('cm')) label.textContent = '(in)';
                    if (label.textContent.includes('kg')) label.textContent = '(st)';
                } else {
                    if (label.textContent.includes('in')) label.textContent = '(cm)';
                    if (label.textContent.includes('st')) label.textContent = '(kg)';
                }
            });
        }
    </script>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>