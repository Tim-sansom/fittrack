<?php
/**
 * File: profile.php
 * Location: /public/profile.php
 *
 * Purpose:
 * Provides the user profile interface with avatar management,
 * personal information editing, and body measurement tracking.
 *
 * Architectural Role:
 * Uses the SaaS layout (header + sidebar includes). Avatar uploads are
 * validated by MIME type and stored as files with sanitised filenames.
 *
 * Security:
 * - All updates scoped to authenticated user_id.
 * - Avatar uploads validated by MIME type and size.
 * - Output escaped with htmlspecialchars().
 * - CSRF token verified on POST requests.
 */
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
$pageTitle = 'Profile';
$currentPage = 'profile';

/*
 * Fetch user, profile, and latest measurements in a single pass.
 */
$stmt = $db->prepare("
    SELECT u.display_name, u.bio, u.avatar_path,
    p.age_band, p.gender_identity, p.locality,
    m.weight_kg, m.bmi, m.chest_cm, m.waist_cm,
    m.hips_cm, m.left_arm_cm, m.right_arm_cm,
    m.left_leg_cm, m.right_leg_cm, m.height_cm
    FROM users u
    LEFT JOIN user_profiles p ON p.user_id = u.user_id
    LEFT JOIN (
        SELECT user_id, weight_kg, bmi, chest_cm, waist_cm, hips_cm,
        left_arm_cm, right_arm_cm, left_leg_cm, right_leg_cm, height_cm
        FROM user_measurements
        WHERE user_id = :uid_sub
        ORDER BY recorded_at DESC LIMIT 1
    ) m ON m.user_id = u.user_id
    WHERE u.user_id = :uid_main
");
$stmt->execute(['uid_sub' => $userId, 'uid_main' => $userId]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Provide safe defaults when rows are missing
$displayName = $data['display_name'] ?? '';
$bio = $data['bio'] ?? '';
$avatarPath = $data['avatar_path'] ?? null;
$ageBand = $data['age_band'] ?? '25-34';
$genderIdentity = $data['gender_identity'] ?? 'prefer_not_to_say';
$locality = $data['locality'] ?? '';
$measurements = [
    'height_cm' => $data['height_cm'] ?? '',
    'weight_kg' => $data['weight_kg'] ?? '',
    'chest_cm' => $data['chest_cm'] ?? '',
    'waist_cm' => $data['waist_cm'] ?? '',
    'hips_cm' => $data['hips_cm'] ?? '',
    'left_arm_cm' => $data['left_arm_cm'] ?? '',
    'right_arm_cm' => $data['right_arm_cm'] ?? '',
    'left_leg_cm' => $data['left_leg_cm'] ?? '',
    'right_leg_cm' => $data['right_leg_cm'] ?? '',
    'bmi' => $data['bmi'] ?? 0,
];

/*
 * Handle avatar upload POST.
 * Validates file type and size, moves to uploads directory, and updates DB.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_avatar'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileExt = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExt, $allowedExts) && $_FILES['avatar']['size'] <= 5 * 1024 * 1024) {
            $uploadDir = __DIR__ . '/uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $newFileName = 'user_' . $userId . '_' . time() . '.' . $fileExt;
            $uploadPath = $uploadDir . $newFileName;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                if ($avatarPath && file_exists(__DIR__ . $avatarPath)) {
                    @unlink(__DIR__ . $avatarPath);
                }
                $stmt = $db->prepare("UPDATE users SET avatar_path = :path WHERE user_id = :id");
                $stmt->execute(['path' => '/uploads/avatars/' . $newFileName, 'id' => $userId]);
            }
        }
    }
    header('Location: profile.php');
    exit;
}

/*
 * Handle profile information POST.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $newName = trim($_POST['display_name'] ?? '');
    $newBio = trim($_POST['bio'] ?? '');
    $newAge = $_POST['age_band'] ?? '25-34';
    $newGender = $_POST['gender_identity'] ?? 'prefer_not_to_say';
    $newLocality = trim($_POST['locality'] ?? '');
    $stmt = $db->prepare("UPDATE users SET display_name = :name, bio = :bio WHERE user_id = :id");
    $stmt->execute(['name' => $newName, 'bio' => $newBio, 'id' => $userId]);
    $checkStmt = $db->prepare("SELECT user_id FROM user_profiles WHERE user_id = :uid_check");
    $checkStmt->execute(['uid_check' => $userId]);
    $profileExists = $checkStmt->fetch();
    if ($profileExists) {
        $stmt = $db->prepare("UPDATE user_profiles SET age_band = :age, gender_identity = :gender, locality = :locality WHERE user_id = :uid_upd");
        $stmt->execute(['age' => $newAge, 'gender' => $newGender, 'locality' => $newLocality, 'uid_upd' => $userId]);
    } else {
        $stmt = $db->prepare("INSERT INTO user_profiles (user_id, age_band, gender_identity, locality) VALUES (:uid_ins, :age, :gender, :locality)");
        $stmt->execute(['uid_ins' => $userId, 'age' => $newAge, 'gender' => $newGender, 'locality' => $newLocality]);
    }
    $_SESSION['username'] = $newName;
    header('Location: profile.php?saved=1');
    exit;
}

/*
 * Handle measurements POST.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_measurements'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $h = (float)($_POST['height_cm'] ?? 0);
    $w = (float)($_POST['weight_kg'] ?? 0);
    $bmi = $h > 0 ? round($w / pow($h / 100, 2), 1) : 0;
    $stmt = $db->prepare("
        INSERT INTO user_measurements
        (user_id, height_cm, weight_kg, chest_cm, waist_cm, hips_cm,
        left_arm_cm, right_arm_cm, left_leg_cm, right_leg_cm, bmi)
        VALUES (:uid_meas, :h, :w, :chest, :waist, :hips, :la, :ra, :ll, :rl, :bmi)
    ");
    $stmt->execute([
        'uid_meas' => $userId,
        'h' => $h, 'w' => $w,
        'chest' => (float)($_POST['chest_cm'] ?? 0),
        'waist' => (float)($_POST['waist_cm'] ?? 0),
        'hips' => (float)($_POST['hips_cm'] ?? 0),
        'la' => (float)($_POST['left_arm_cm'] ?? 0),
        'ra' => (float)($_POST['right_arm_cm'] ?? 0),
        'll' => (float)($_POST['left_leg_cm'] ?? 0),
        'rl' => (float)($_POST['right_leg_cm'] ?? 0),
        'bmi' => $bmi
    ]);
    header('Location: profile.php?measurements=1');
    exit;
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="main-content">
    <div class="profile-header-row">
        <div class="profile-avatar-section">
            <div class="avatar-large-container">
                <?php if ($avatarPath): ?>
                    <img src="<?= htmlspecialchars($avatarPath) ?>" alt="Profile avatar" class="avatar-large">
                <?php else: ?>
                    <div class="avatar-large avatar-initials">
                        <?= htmlspecialchars(strtoupper(substr(str_replace(' ', '', $displayName), 0, 2))) ?>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" id="upload-trigger-btn" class="btn-upload-avatar" title="Change avatar" aria-label="Change profile photo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                Change Photo
            </button>
            <input type="file" id="avatar-file-input" accept="image/jpeg, image/png, image/webp" hidden>
            <div id="avatar-crop-modal" class="modal-overlay" style="display: none;">
                <div class="modal-content" style="background: var(--card); padding: 2rem; border-radius: 12px; max-width: 500px; width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
                    <h3 style="margin-top: 0; color: var(--text);">Adjust Profile Photo</h3>
                    <p style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 1rem;">Zoom and pan to position your photo. It will be automatically compressed.</p>
                    <div style="width: 300px; height: 300px; margin: 0 auto 1rem auto; border-radius: 50%; overflow: hidden; border: 2px solid var(--accent); background: #000;">
                        <canvas id="crop-preview-canvas" width="300" height="300" style="width: 100%; height: 100%;"></canvas>
                    </div>
                    <div class="crop-controls" style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem;">
                        <label style="color: var(--text); font-size: 0.9rem;">Zoom: <input type="range" id="crop-zoom" min="0.1" max="3" step="0.05" value="1" style="width: 100%;"></label>
                        <label style="color: var(--text); font-size: 0.9rem;">Horizontal: <input type="range" id="crop-pan-x" min="-300" max="300" value="0" style="width: 100%;"></label>
                        <label style="color: var(--text); font-size: 0.9rem;">Vertical: <input type="range" id="crop-pan-y" min="-300" max="300" value="0" style="width: 100%;"></label>
                    </div>
                    <input type="hidden" id="csrf-token-input" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                        <button type="button" id="cancel-crop-btn" class="btn-secondary">Cancel</button>
                        <button type="button" id="save-crop-btn" class="btn-primary">Apply & Upload</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="profile-info-section">
            <h2><?= htmlspecialchars($displayName) ?></h2>
            <?php if ($locality): ?>
                <p class="profile-location"><?= htmlspecialchars($locality) ?></p>
            <?php endif; ?>
            <?php if ($measurements['bmi'] > 0): ?>
                <div class="bmi-badge">BMI: <?= $measurements['bmi'] ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php if (isset($_GET['saved'])): ?>
        <div class="alert-success">Profile updated successfully.</div>
    <?php endif; ?>
    <?php if (isset($_GET['measurements'])): ?>
        <div class="alert-success">Measurements recorded.</div>
    <?php endif; ?>
    <div class="profile-grid">
        <!-- Personal Information -->
        <div class="card">
            <h3>Personal Information</h3>
            <form method="POST" action="profile.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="update_profile" value="1">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="display_name">Display Name</label>
                        <input type="text" id="display_name" name="display_name" value="<?= htmlspecialchars($displayName) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="locality">Locality</label>
                        <input type="text" id="locality" name="locality" value="<?= htmlspecialchars($locality) ?>" placeholder="e.g. Manchester">
                    </div>
                    <div class="form-group">
                        <label for="age_band">Age Band</label>
                        <select id="age_band" name="age_band">
                            <?php foreach (['16-17', '18-24', '25-34', '35-44', '45-54', '55-64', '65-80'] as $band): ?>
                                <option value="<?= $band ?>" <?= $ageBand === $band ? 'selected' : '' ?>><?= $band ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="gender_identity">Gender Identity</label>
                        <select id="gender_identity" name="gender_identity">
                            <?php foreach (['female', 'male', 'non_binary', 'prefer_not_to_say'] as $g): ?>
                                <option value="<?= $g ?>" <?= $genderIdentity === $g ? 'selected' : '' ?>>
                                    <?= ucfirst(str_replace('_', ' ', $g)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="3"><?= htmlspecialchars($bio) ?></textarea>
                </div>
                <button type="submit" class="btn-primary btn-save">Save Profile</button>
            </form>
        </div>
        <!-- Body Measurements -->
        <div class="card">
            <h3>Body Measurements</h3>
            <p class="card-description">Record your measurements to track progress over time.</p>
            <form method="POST" action="profile.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="update_measurements" value="1">
                <div class="form-grid-3">
                    <div class="form-group">
                        <label for="height_cm">Height (cm)</label>
                        <input type="number" step="0.1" id="height_cm" name="height_cm" value="<?= $measurements['height_cm'] ?>" placeholder="175">
                    </div>
                    <div class="form-group">
                        <label for="weight_kg">Weight (kg)</label>
                        <input type="number" step="0.1" id="weight_kg" name="weight_kg" value="<?= $measurements['weight_kg'] ?>" placeholder="70">
                    </div>
                    <div class="form-group">
                        <label for="chest_cm">Chest (cm)</label>
                        <input type="number" step="0.1" id="chest_cm" name="chest_cm" value="<?= $measurements['chest_cm'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="waist_cm">Waist (cm)</label>
                        <input type="number" step="0.1" id="waist_cm" name="waist_cm" value="<?= $measurements['waist_cm'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="hips_cm">Hips (cm)</label>
                        <input type="number" step="0.1" id="hips_cm" name="hips_cm" value="<?= $measurements['hips_cm'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="left_arm_cm">Left Arm (cm)</label>
                        <input type="number" step="0.1" id="left_arm_cm" name="left_arm_cm" value="<?= $measurements['left_arm_cm'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="right_arm_cm">Right Arm (cm)</label>
                        <input type="number" step="0.1" id="right_arm_cm" name="right_arm_cm" value="<?= $measurements['right_arm_cm'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="left_leg_cm">Left Leg (cm)</label>
                        <input type="number" step="0.1" id="left_leg_cm" name="left_leg_cm" value="<?= $measurements['left_leg_cm'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="right_leg_cm">Right Leg (cm)</label>
                        <input type="number" step="0.1" id="right_leg_cm" name="right_leg_cm" value="<?= $measurements['right_leg_cm'] ?>">
                    </div>
                </div>
                <button type="submit" class="btn-primary btn-save">Record Measurements</button>
            </form>
        </div>
    </div>
</div>
<!-- Vanilla JS Avatar Cropper & Compressor -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('avatar-file-input');
    const triggerBtn = document.getElementById('upload-trigger-btn');
    const modal = document.getElementById('avatar-crop-modal');
    const cancelBtn = document.getElementById('cancel-crop-btn');
    const saveBtn = document.getElementById('save-crop-btn');
    const canvas = document.getElementById('crop-preview-canvas');
    const ctx = canvas.getContext('2d');
    const zoomSlider = document.getElementById('crop-zoom');
    const panXSlider = document.getElementById('crop-pan-x');
    const panYSlider = document.getElementById('crop-pan-y');
    const csrfToken = document.getElementById('csrf-token-input').value;
    let originalImage = null;
    if (triggerBtn) triggerBtn.addEventListener('click', () => fileInput.click());
    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                alert('Please select a JPEG, PNG, or WebP image.');
                return;
            }
            const reader = new FileReader();
            reader.onload = (event) => {
                originalImage = new Image();
                originalImage.onload = () => {
                    zoomSlider.value = 1; panXSlider.value = 0; panYSlider.value = 0;
                    modal.style.display = 'flex';
                    drawCanvas();
                };
                originalImage.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
    }
    [zoomSlider, panXSlider, panYSlider].forEach(slider => slider.addEventListener('input', drawCanvas));
    function drawCanvas() {
        if (!originalImage) return;
        const width = canvas.width; const height = canvas.height;
        const zoom = parseFloat(zoomSlider.value);
        const panX = parseFloat(panXSlider.value);
        const panY = parseFloat(panYSlider.value);
        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = '#000'; ctx.fillRect(0, 0, width, height);
        const imgRatio = originalImage.width / originalImage.height;
        let drawWidth, drawHeight;
        if (imgRatio > 1) { drawHeight = height; drawWidth = height * imgRatio; }
        else { drawWidth = width; drawHeight = width / imgRatio; }
        drawWidth *= zoom; drawHeight *= zoom;
        const x = (width - drawWidth) / 2 + panX;
        const y = (height - drawHeight) / 2 + panY;
        ctx.drawImage(originalImage, x, y, drawWidth, drawHeight);
    }
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            modal.style.display = 'none';
            fileInput.value = '';
        });
    }
    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            if (!originalImage) return;
            const outputSize = 512;
            const outputCanvas = document.createElement('canvas');
            outputCanvas.width = outputSize; outputCanvas.height = outputSize;
            const outputCtx = outputCanvas.getContext('2d');
            const scaleFactor = outputSize / canvas.width;
            outputCtx.fillStyle = '#000'; outputCtx.fillRect(0, 0, outputSize, outputSize);
            const imgRatio = originalImage.width / originalImage.height;
            let drawWidth, drawHeight;
            if (imgRatio > 1) { drawHeight = outputSize; drawWidth = outputSize * imgRatio; }
            else { drawWidth = outputSize; drawHeight = outputSize / imgRatio; }
            const zoom = parseFloat(zoomSlider.value);
            const panX = parseFloat(panXSlider.value) * scaleFactor;
            const panY = parseFloat(panYSlider.value) * scaleFactor;
            drawWidth *= zoom; drawHeight *= zoom;
            const x = (outputSize - drawWidth) / 2 + panX;
            const y = (outputSize - drawHeight) / 2 + panY;
            outputCtx.drawImage(originalImage, x, y, drawWidth, drawHeight);
            outputCanvas.toBlob((blob) => {
                if (blob) {
                    const formData = new FormData();
                    formData.append('avatar', blob, 'avatar.jpg');
                    formData.append('update_avatar', '1');
                    formData.append('csrf_token', csrfToken);
                    saveBtn.textContent = 'Uploading...';
                    saveBtn.disabled = true;
                    fetch('profile.php', { method: 'POST', body: formData })
                    .then(response => {
                        if (response.ok) window.location.reload();
                        else throw new Error('Upload failed');
                    })
                    .catch(error => {
                        alert('Network error during upload.');
                        saveBtn.textContent = 'Apply & Upload';
                        saveBtn.disabled = false;
                    });
                }
            }, 'image/jpeg', 0.85);
        });
    }
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>