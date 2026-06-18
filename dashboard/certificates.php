<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

// Data file
$certificatesFile = __DIR__ . '/../data/certificates.json';

// Load certificates
$certificates = [];
if (file_exists($certificatesFile)) {
    $certificates = json_decode(file_get_contents($certificatesFile), true) ?: [];
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add certificate
    if (isset($_POST['add_certificate'])) {
        $title = trim($_POST['title'] ?? '');
        $issuer = trim($_POST['issuer'] ?? '');
        $period = trim($_POST['period'] ?? '');
        $desc = trim($_POST['desc'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fas fa-certificate');
        
        if ($title && $issuer) {
            $newCert = [
                'id' => 'cert_' . uniqid(),
                'title' => $title,
                'issuer' => $issuer,
                'period' => $period,
                'desc' => $desc,
                'icon' => $icon,
                'image' => ''
            ];
            
            // Handle image upload
            if (isset($_FILES['cert_image']) && $_FILES['cert_image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($_FILES['cert_image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $filename = 'cert_' . uniqid() . '.' . $ext;
                    $uploadDir = __DIR__ . '/../uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    if (move_uploaded_file($_FILES['cert_image']['tmp_name'], $uploadDir . $filename)) {
                        $newCert['image'] = $filename;
                    }
                }
            }
            
            $certificates[] = $newCert;
            file_put_contents($certificatesFile, json_encode($certificates, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $success = 'تم إضافة الشهادة بنجاح.';
        } else {
            $error = 'أدخل العنوان والجهة المصدرة.';
        }
    }
    
    // Edit certificate
    elseif (isset($_POST['edit_certificate'])) {
        $id = $_POST['cert_id'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $issuer = trim($_POST['issuer'] ?? '');
        $period = trim($_POST['period'] ?? '');
        $desc = trim($_POST['desc'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fas fa-certificate');
        
        $found = false;
        foreach ($certificates as &$cert) {
            if ($cert['id'] === $id) {
                $found = true;
                $cert['title'] = $title;
                $cert['issuer'] = $issuer;
                $cert['period'] = $period;
                $cert['desc'] = $desc;
                $cert['icon'] = $icon;
                
                // Handle image upload
                if (isset($_FILES['cert_image']) && $_FILES['cert_image']['error'] === UPLOAD_ERR_OK) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $ext = strtolower(pathinfo($_FILES['cert_image']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, $allowed)) {
                        // Delete old image
                        $oldImage = $cert['image'] ?? '';
                        if ($oldImage && file_exists(__DIR__ . '/../uploads/' . $oldImage)) {
                            unlink(__DIR__ . '/../uploads/' . $oldImage);
                        }
                        
                        $filename = 'cert_' . uniqid() . '.' . $ext;
                        $uploadDir = __DIR__ . '/../uploads/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                        if (move_uploaded_file($_FILES['cert_image']['tmp_name'], $uploadDir . $filename)) {
                            $cert['image'] = $filename;
                        }
                    }
                }
                break;
            }
        }
        
        if ($found && $title && $issuer) {
            file_put_contents($certificatesFile, json_encode($certificates, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $success = 'تم تعديل الشهادة بنجاح.';
        } else {
            $error = 'خطأ في التعديل.';
        }
    }
    
    // Delete certificate
    elseif (isset($_POST['delete_certificate'])) {
        $id = $_POST['cert_id'] ?? '';
        foreach ($certificates as $i => $cert) {
            if ($cert['id'] === $id) {
                // Delete image
                $image = $cert['image'] ?? '';
                if ($image && file_exists(__DIR__ . '/../uploads/' . $image)) {
                    unlink(__DIR__ . '/../uploads/' . $image);
                }
                
                array_splice($certificates, $i, 1);
                file_put_contents($certificatesFile, json_encode($certificates, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                $success = 'تم حذف الشهادة بنجاح.';
                break;
            }
        }
    }
}

// Icon options
$iconOptions = [
    'fas fa-certificate' => 'شهادة',
    'fas fa-award' => 'جائزة',
    'fas fa-trophy' => 'كأس',
    'fas fa-medal' => 'ميدالية',
    'fas fa-star' => 'نجمة',
    'fas fa-graduation-cap' => 'تعليم',
    'fas fa-rocket' => 'صاروخ',
    'fas fa-code' => 'برمجة',
    'fas fa-palette' => 'فن',
    'fas fa-video' => 'فيديو',
    'fas fa-camera' => 'كاميرا',
    'fas fa-microphone' => 'مايك',
    'fas fa-globe' => 'عالم',
    'fas fa-briefcase' => 'عمل',
    'fas fa-check-circle' => 'تحقق'
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>الشهادات - Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --dark: #0A1D37;
      --teal: #00C2C7;
      --white: #FFFFFF;
      --gold: #D4AF37;
      --gold-light: #f0d060;
      --dark2: #071428;
      --teal-dim: rgba(0,194,199,0.15);
      --gold-dim: rgba(212,175,55,0.12);
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: linear-gradient(135deg, var(--dark), var(--dark2));
      color: var(--white);
      font-family: 'Cairo', sans-serif;
      min-height: 100vh;
    }
    .header {
      background: rgba(255,255,255,0.05);
      border-bottom: 1px solid var(--teal);
      padding: 18px 28px;
      display: flex; justify-content: space-between; align-items: center;
    }
    .header h1 {
      font-size: 1.4rem; color: #D4AF37; font-weight: 800; }
    .nav-links {
      display: flex; gap: 14px; }
    .nav-links a {
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.15);
      color: rgba(255,255,255,0.7);
      padding: 8px 16px;
      border-radius: 20px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
    }
    .nav-links a:hover {
      background: rgba(0,194,199,0.15);
      border-color: #00C2C7;
      color: #00C2C7;
    }
    .nav-links a.active {
      background: rgba(0,194,199,0.15);
      border-color: #00C2C7;
      color: #00C2C7;
    }
    .logout a {
      background: rgba(255,80,80,0.1);
      border: 1px solid rgba(255,80,80,0.4);
      color: #ff6060;
      padding: 8px 20px;
      border-radius: 20px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
    }
    .logout a:hover {
      background: rgba(255,80,80,0.2);
      transform: translateY(-2px);
    }
    .main {
      padding: 28px;
      max-width: 1200px;
      margin: 0 auto;
    }
    .section {
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--teal);
      border-radius: 16px;
      padding: 24px;
      margin-bottom: 24px;
    }
    .section-title {
      font-size: 1.2rem;
      color: var(--teal);
      font-weight: 700;
      margin-bottom: 20px;
      letter-spacing: 1px;
    }
    .form-group {
      margin-bottom: 18px;
    }
    .form-group label {
      display: block;
      font-size: 0.9rem;
      color: rgba(255,255,255,0.75);
      margin-bottom: 8px;
      letter-spacing: 0.5px;
    }
    .form-group input[type="text"],
    .form-group select,
    .form-group textarea {
      width: 100%;
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.15);
      border-radius: 10px;
      padding: 12px 14px;
      color: #fff;
      font-size: 0.95rem;
      font-family: 'Cairo', sans-serif;
      transition: all 0.3s;
    }
    .form-group input[type="file"] {
      width: 100%;
      padding: 10px;
      background: rgba(255,255,255,0.05);
      border: 2px dashed rgba(255,255,255,0.2);
      border-radius: 10px;
      color: rgba(255,255,255,0.7);
      cursor: pointer;
    }
    .form-group input[type="text"]:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #00C2C7;
      box-shadow: 0 0 0 3px rgba(0,194,199,0.1);
    }
    .form-group textarea {
      min-height: 80px;
      resize: vertical;
    }
    .btn {
      background: linear-gradient(135deg, var(--gold), #b8942e);
      color: var(--dark);
      border: none;
      padding: 12px 28px;
      border-radius: 10px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s;
      letter-spacing: 0.5px;
    }
    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 40px var(--gold-dim);
    }
    .btn-danger {
      background: linear-gradient(135deg, #ff6060, #e04444);
      color: #fff;
      padding: 8px 16px;
      font-size: 0.85rem;
    }
    .btn-small {
      padding: 8px 16px;
      font-size: 0.9rem;
    }
    .btn-secondary {
      background: linear-gradient(135deg, var(--teal), #00a0a5);
      color: #fff;
    }
    .msg {
      padding: 14px;
      border-radius: 10px;
      margin-bottom: 20px;
      text-align: center;
      font-weight: 600;
    }
    .msg.success {
      background: var(--teal-dim);
      border: 1px solid var(--teal);
      color: var(--teal);
    }
    .msg.error {
      background: rgba(255,80,80,0.1);
      border: 1px solid rgba(255,80,80,0.4);
      color: #ff6060;
    }
    .cert-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }
    .cert-card {
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 12px;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .cert-icon {
      width: 50px;
      height: 50px;
      background: linear-gradient(135deg, var(--gold), var(--gold-light));
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: var(--dark);
    }
    .cert-title {
      font-weight: 700;
      color: var(--gold);
      font-size: 1.1rem;
    }
    .cert-issuer {
      color: rgba(255,255,255,0.7);
      font-size: 0.9rem;
    }
    .cert-period {
      color: var(--teal);
      font-size: 0.85rem;
    }
    .cert-desc {
      color: rgba(255,255,255,0.6);
      font-size: 0.9rem;
      line-height: 1.5;
    }
    .cert-image {
      width: 100%;
      height: 120px;
      object-fit: cover;
      border-radius: 8px;
      background: rgba(255,255,255,0.05);
    }
    .cert-actions {
      display: flex;
      gap: 8px;
      margin-top: auto;
    }
    .empty-state {
      text-align: center;
      padding: 40px;
      color: rgba(255,255,255,0.5);
    }
    .add-form {
      background: rgba(255,255,255,0.03);
      border-radius: 12px;
      padding: 24px;
      margin-bottom: 30px;
    }
    .modal {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.8);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }
    .modal.active {
      display: flex;
    }
    .modal-content {
      background: linear-gradient(135deg, var(--dark), var(--dark2));
      border: 1px solid var(--teal);
      border-radius: 16px;
      padding: 24px;
      max-width: 600px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .modal-title {
      font-size: 1.2rem;
      color: var(--gold);
      font-weight: 700;
    }
    .modal-close {
      background: none;
      border: none;
      color: rgba(255,255,255,0.5);
      font-size: 1.5rem;
      cursor: pointer;
    }
    .current-image {
      max-width: 200px;
      max-height: 100px;
      border-radius: 8px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <header class="header">
    <h1>Dashboard - الشهادات</h1>
    <div class="nav-links">
      <a href="index.php" class="nav-tab">الرئيسية</a>
      <a href="projects.php" class="nav-tab">المشاريع</a>
      <a href="theme.php" class="nav-tab">الألوان</a>
      <a href="skills_clients.php" class="nav-tab">الخلفيات</a>
      <a href="certificates.php" class="nav-tab active">الشهادات</a>
      <a href="sections.php" class="nav-tab">الأقسام</a>
    </div>
    <div class="logout"><a href="logout.php">تسجيل خروج</a></div>
  </header>
  
  <main class="main">
    <?php if ($success): ?>
      <div class="msg success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="msg error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Add Certificate Form -->
    <section class="section">
      <h2 class="section-title">إضافة شهادة جديدة</h2>
      <form method="POST" action="" enctype="multipart/form-data" class="add-form">
        <input type="hidden" name="add_certificate" value="1">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div class="form-group">
            <label>عنوان الشهادة *</label>
            <input type="text" name="title" placeholder="مثال: DEPI - Adobe Motion Graphics" required>
          </div>
          <div class="form-group">
            <label>الجهة المصدرة *</label>
            <input type="text" name="issuer" placeholder="مثال: MCIT Ministry" required>
          </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div class="form-group">
            <label>الفترة</label>
            <input type="text" name="period" placeholder="مثال: 05/2024 - 11/2024">
          </div>
          <div class="form-group">
            <label>أيقونة</label>
            <select name="icon">
              <?php foreach ($iconOptions as $icon => $label): ?>
                <option value="<?= htmlspecialchars($icon) ?>"><?= htmlspecialchars($label) ?> (<?= $icon ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label>وصف الشهادة</label>
          <textarea name="desc" placeholder="وصف مختصر للشهادة..."></textarea>
        </div>
        
        <div class="form-group">
          <label>صورة الشهادة (اختياري - ستظهر عند الضغط على الكارد)</label>
          <input type="file" name="cert_image" accept=".jpg,.jpeg,.png,.gif,.webp">
        </div>
        
        <button type="submit" class="btn">إضافة شهادة</button>
      </form>
    </section>

    <!-- Certificates List -->
    <section class="section">
      <h2 class="section-title">الشهادات المضافة</h2>
      
      <?php if (!empty($certificates)): ?>
        <div class="cert-grid">
          <?php foreach ($certificates as $cert): ?>
          <div class="cert-card">
            <div class="cert-icon">
              <i class="<?= htmlspecialchars($cert['icon'] ?? 'fas fa-certificate') ?>"></i>
            </div>
            <div class="cert-title"><?= htmlspecialchars($cert['title']) ?></div>
            <div class="cert-issuer"><?= htmlspecialchars($cert['issuer']) ?></div>
            <div class="cert-period"><?= htmlspecialchars($cert['period'] ?? '') ?></div>
            <?php if ($cert['desc']): ?>
              <div class="cert-desc"><?= htmlspecialchars($cert['desc']) ?></div>
            <?php endif; ?>
            
            <?php if ($cert['image']): ?>
              <img src="../uploads/<?= htmlspecialchars($cert['image']) ?>" alt="" class="cert-image">
            <?php endif; ?>
            
            <div class="cert-actions">
              <button type="button" class="btn btn-small btn-secondary" onclick="editCertificate('<?= htmlspecialchars($cert['id']) ?>', '<?= htmlspecialchars(addslashes($cert['title'])) ?>', '<?= htmlspecialchars(addslashes($cert['issuer'])) ?>', '<?= htmlspecialchars(addslashes($cert['period'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($cert['desc'] ?? '')) ?>', '<?= htmlspecialchars($cert['icon'] ?? 'fas fa-certificate') ?>', '<?= htmlspecialchars($cert['image'] ?? '') ?>')">تعديل</button>
              <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="delete_certificate" value="1">
                <input type="hidden" name="cert_id" value="<?= htmlspecialchars($cert['id']) ?>">
                <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذه الشهادة؟')">حذف</button>
              </form>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">لا توجد شهادات مضافة بعد</div>
      <?php endif; ?>
    </section>
  </main>

  <!-- Edit Certificate Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">تعديل الشهادة</h3>
        <button class="modal-close" onclick="closeModal()">&times;</button>
      </div>
      <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="edit_certificate" value="1">
        <input type="hidden" name="cert_id" id="editCertId">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div class="form-group">
            <label>عنوان الشهادة *</label>
            <input type="text" name="title" id="editTitle" required>
          </div>
          <div class="form-group">
            <label>الجهة المصدرة *</label>
            <input type="text" name="issuer" id="editIssuer" required>
          </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div class="form-group">
            <label>الفترة</label>
            <input type="text" name="period" id="editPeriod">
          </div>
          <div class="form-group">
            <label>أيقونة</label>
            <select name="icon" id="editIcon">
              <?php foreach ($iconOptions as $icon => $label): ?>
                <option value="<?= htmlspecialchars($icon) ?>"><?= htmlspecialchars($label) ?> (<?= $icon ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label>وصف الشهادة</label>
          <textarea name="desc" id="editDesc"></textarea>
        </div>
        
        <div class="form-group">
          <label>الصورة الحالية</label>
          <div id="currentImageContainer"></div>
        </div>
        
        <div class="form-group">
          <label>صورة جديدة (اختياري - لاستبدال الصورة الحالية)</label>
          <input type="file" name="cert_image" accept=".jpg,.jpeg,.png,.gif,.webp">
        </div>
        
        <button type="submit" class="btn">حفظ التعديلات</button>
      </form>
    </div>
  </div>

  <script>
    function editCertificate(id, title, issuer, period, desc, icon, image) {
      document.getElementById('editCertId').value = id;
      document.getElementById('editTitle').value = title;
      document.getElementById('editIssuer').value = issuer;
      document.getElementById('editPeriod').value = period;
      document.getElementById('editDesc').value = desc;
      document.getElementById('editIcon').value = icon;
      
      // Show current image
      const imageContainer = document.getElementById('currentImageContainer');
      if (image) {
        imageContainer.innerHTML = '<img src="../uploads/' + image + '" class="current-image" alt="صورة الشهادة">';
      } else {
        imageContainer.innerHTML = '<span style="color: rgba(255,255,255,0.5);">لا توجد صورة</span>';
      }
      
      document.getElementById('editModal').classList.add('active');
    }
    
    function closeModal() {
      document.getElementById('editModal').classList.remove('active');
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('editModal');
      if (event.target === modal) {
        modal.classList.remove('active');
      }
    }
  </script>
</body>
</html>
