<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

// Data files
$skillsClientsFile = __DIR__ . '/../data/skills_clients.json';
$themeFile = __DIR__ . '/../data/theme.json';

// Load skills/clients data
$skillsClients = [
    'adobe_creative_suite' => [
        'title' => 'Adobe Creative Suite',
        'background' => 'linear-gradient(180deg, #0f172a 0%, #1f2937 100%)',
        'items' => []
    ],
    'trusted_clients' => [
        'title' => 'Trusted Clients',
        'background' => 'linear-gradient(180deg, #111827 0%, #0f172a 100%)',
        'items' => []
    ]
];
if (file_exists($skillsClientsFile)) {
    $skillsClients = json_decode(file_get_contents($skillsClientsFile), true) ?: $skillsClients;
}

// Load theme
$theme = [];
if (file_exists($themeFile)) {
    $theme = json_decode(file_get_contents($themeFile), true) ?: [];
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save backgrounds
    if (isset($_POST['save_backgrounds'])) {
        $skillsClients['adobe_creative_suite']['background'] = trim($_POST['adobe_background'] ?? '');
        $skillsClients['trusted_clients']['background'] = trim($_POST['clients_background'] ?? '');
        file_put_contents($skillsClientsFile, json_encode($skillsClients, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $success = 'تم حفظ الخلفيات بنجاح.';
    }
    
    // Add Adobe item
    elseif (isset($_POST['add_adobe_item'])) {
        $program = trim($_POST['program_name'] ?? '');
        $color = trim($_POST['program_color'] ?? '#9999FF');
        
        if ($program) {
            $newItem = [
                'program' => $program,
                'image' => '',
                'color' => $color
            ];
            
            // Handle image upload
            if (isset($_FILES['program_image']) && $_FILES['program_image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                $ext = strtolower(pathinfo($_FILES['program_image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $filename = 'adobe_' . uniqid() . '.' . $ext;
                    $uploadDir = __DIR__ . '/../uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    if (move_uploaded_file($_FILES['program_image']['tmp_name'], $uploadDir . $filename)) {
                        $newItem['image'] = $filename;
                    }
                }
            }
            
            $skillsClients['adobe_creative_suite']['items'][] = $newItem;
            file_put_contents($skillsClientsFile, json_encode($skillsClients, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $success = 'تم إضافة البرنامج بنجاح.';
        } else {
            $error = 'أدخل اسم البرنامج.';
        }
    }
    
    // Edit Adobe item
    elseif (isset($_POST['edit_adobe_item'])) {
        $index = intval($_POST['item_index'] ?? -1);
        $program = trim($_POST['program_name'] ?? '');
        $color = trim($_POST['program_color'] ?? '#9999FF');
        
        if ($index >= 0 && isset($skillsClients['adobe_creative_suite']['items'][$index]) && $program) {
            $skillsClients['adobe_creative_suite']['items'][$index]['program'] = $program;
            $skillsClients['adobe_creative_suite']['items'][$index]['color'] = $color;
            
            // Handle image upload
            if (isset($_FILES['program_image']) && $_FILES['program_image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                $ext = strtolower(pathinfo($_FILES['program_image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    // Delete old image
                    $oldImage = $skillsClients['adobe_creative_suite']['items'][$index]['image'] ?? '';
                    if ($oldImage && file_exists(__DIR__ . '/../uploads/' . $oldImage)) {
                        unlink(__DIR__ . '/../uploads/' . $oldImage);
                    }
                    
                    $filename = 'adobe_' . uniqid() . '.' . $ext;
                    $uploadDir = __DIR__ . '/../uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    if (move_uploaded_file($_FILES['program_image']['tmp_name'], $uploadDir . $filename)) {
                        $skillsClients['adobe_creative_suite']['items'][$index]['image'] = $filename;
                    }
                }
            }
            
            file_put_contents($skillsClientsFile, json_encode($skillsClients, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $success = 'تم تعديل البرنامج بنجاح.';
        } else {
            $error = 'خطأ في التعديل.';
        }
    }
    
    // Delete Adobe item
    elseif (isset($_POST['delete_adobe_item'])) {
        $index = intval($_POST['item_index'] ?? -1);
        if ($index >= 0 && isset($skillsClients['adobe_creative_suite']['items'][$index])) {
            // Delete image
            $image = $skillsClients['adobe_creative_suite']['items'][$index]['image'] ?? '';
            if ($image && file_exists(__DIR__ . '/../uploads/' . $image)) {
                unlink(__DIR__ . '/../uploads/' . $image);
            }
            
            array_splice($skillsClients['adobe_creative_suite']['items'], $index, 1);
            file_put_contents($skillsClientsFile, json_encode($skillsClients, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $success = 'تم حذف البرنامج بنجاح.';
        }
    }
    
    // Add Client item
    elseif (isset($_POST['add_client_item'])) {
        $name = trim($_POST['client_name'] ?? '');
        
        if ($name) {
            $newItem = [
                'name' => $name,
                'logo' => ''
            ];
            
            // Handle image upload
            if (isset($_FILES['client_logo']) && $_FILES['client_logo']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                $ext = strtolower(pathinfo($_FILES['client_logo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $filename = 'client_' . uniqid() . '.' . $ext;
                    $uploadDir = __DIR__ . '/../uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    if (move_uploaded_file($_FILES['client_logo']['tmp_name'], $uploadDir . $filename)) {
                        $newItem['logo'] = $filename;
                    }
                }
            }
            
            $skillsClients['trusted_clients']['items'][] = $newItem;
            file_put_contents($skillsClientsFile, json_encode($skillsClients, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $success = 'تم إضافة العميل بنجاح.';
        } else {
            $error = 'أدخل اسم العميل.';
        }
    }
    
    // Edit Client item
    elseif (isset($_POST['edit_client_item'])) {
        $index = intval($_POST['item_index'] ?? -1);
        $name = trim($_POST['client_name'] ?? '');
        
        if ($index >= 0 && isset($skillsClients['trusted_clients']['items'][$index]) && $name) {
            $skillsClients['trusted_clients']['items'][$index]['name'] = $name;
            
            // Handle image upload
            if (isset($_FILES['client_logo']) && $_FILES['client_logo']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                $ext = strtolower(pathinfo($_FILES['client_logo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    // Delete old logo
                    $oldLogo = $skillsClients['trusted_clients']['items'][$index]['logo'] ?? '';
                    if ($oldLogo && file_exists(__DIR__ . '/../uploads/' . $oldLogo)) {
                        unlink(__DIR__ . '/../uploads/' . $oldLogo);
                    }
                    
                    $filename = 'client_' . uniqid() . '.' . $ext;
                    $uploadDir = __DIR__ . '/../uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    if (move_uploaded_file($_FILES['client_logo']['tmp_name'], $uploadDir . $filename)) {
                        $skillsClients['trusted_clients']['items'][$index]['logo'] = $filename;
                    }
                }
            }
            
            file_put_contents($skillsClientsFile, json_encode($skillsClients, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $success = 'تم تعديل العميل بنجاح.';
        } else {
            $error = 'خطأ في التعديل.';
        }
    }
    
    // Delete Client item
    elseif (isset($_POST['delete_client_item'])) {
        $index = intval($_POST['item_index'] ?? -1);
        if ($index >= 0 && isset($skillsClients['trusted_clients']['items'][$index])) {
            // Delete logo
            $logo = $skillsClients['trusted_clients']['items'][$index]['logo'] ?? '';
            if ($logo && file_exists(__DIR__ . '/../uploads/' . $logo)) {
                unlink(__DIR__ . '/../uploads/' . $logo);
            }
            
            array_splice($skillsClients['trusted_clients']['items'], $index, 1);
            file_put_contents($skillsClientsFile, json_encode($skillsClients, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $success = 'تم حذف العميل بنجاح.';
        }
    }
}

$activeTab = $_GET['tab'] ?? 'backgrounds';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>الخلفيات والعناصر - Dashboard</title>
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
      max-width: 1100px;
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
    .tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 24px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      padding-bottom: 10px;
    }
    .tab {
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      color: rgba(255,255,255,0.7);
      padding: 12px 24px;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
      cursor: pointer;
    }
    .tab:hover, .tab.active {
      background: rgba(0,194,199,0.2);
      border-color: var(--teal);
      color: var(--teal);
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
    .form-group input[type="color"],
    .form-group select {
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
    .form-group input[type="text"]:focus,
    .form-group select:focus {
      outline: none;
      border-color: #00C2C7;
      box-shadow: 0 0 0 3px rgba(0,194,199,0.1);
    }
    .form-group input[type="file"] {
      padding: 10px;
      background: rgba(255,255,255,0.05);
      border: 2px dashed rgba(255,255,255,0.2);
      border-radius: 10px;
      color: rgba(255,255,255,0.7);
      cursor: pointer;
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
    .items-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 16px;
      margin-top: 20px;
    }
    .item-card {
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 12px;
      padding: 16px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .item-card img {
      width: 100%;
      height: 100px;
      object-fit: contain;
      background: rgba(255,255,255,0.05);
      border-radius: 8px;
      padding: 10px;
    }
    .item-card .item-name {
      font-weight: 700;
      color: var(--gold);
      text-align: center;
    }
    .item-card .actions {
      display: flex;
      gap: 8px;
      justify-content: center;
    }
    .color-preview {
      width: 30px;
      height: 30px;
      border-radius: 6px;
      display: inline-block;
      vertical-align: middle;
      margin-right: 10px;
      border: 2px solid rgba(255,255,255,0.2);
    }
    .background-preview {
      width: 100%;
      height: 80px;
      border-radius: 10px;
      margin-top: 10px;
      border: 2px solid rgba(255,255,255,0.2);
    }
    .empty-state {
      text-align: center;
      padding: 40px;
      color: rgba(255,255,255,0.5);
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
      max-width: 500px;
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
  </style>
</head>
<body>
  <header class="header">
    <h1>Dashboard - الخلفيات والعناصر</h1>
    <div class="nav-links">
      <a href="index.php" class="nav-tab">الرئيسية</a>
      <a href="projects.php" class="nav-tab">المشاريع</a>
      <a href="theme.php" class="nav-tab">الألوان</a>
      <a href="skills_clients.php" class="nav-tab active">الخلفيات</a>
      <a href="certificates.php" class="nav-tab">الشهادات</a>
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

    <div class="tabs">
      <a href="?tab=backgrounds" class="tab <?= $activeTab === 'backgrounds' ? 'active' : '' ?>">الخلفيات</a>
      <a href="?tab=adobe" class="tab <?= $activeTab === 'adobe' ? 'active' : '' ?>">Adobe Creative Suite</a>
      <a href="?tab=clients" class="tab <?= $activeTab === 'clients' ? 'active' : '' ?>">Trusted Clients</a>
    </div>

    <!-- Backgrounds Tab -->
    <?php if ($activeTab === 'backgrounds'): ?>
    <section class="section">
      <h2 class="section-title">تعديل خلفيات الأقسام</h2>
      <form method="POST" action="">
        <input type="hidden" name="save_backgrounds" value="1">
        
        <div class="form-group">
          <label>خلفية Adobe Creative Suite (CSS Gradient)</label>
          <input type="text" name="adobe_background" value="<?= htmlspecialchars($skillsClients['adobe_creative_suite']['background'] ?? '') ?>" placeholder="linear-gradient(180deg, #0f172a 0%, #1f2937 100%)">
          <div class="background-preview" style="background: <?= htmlspecialchars($skillsClients['adobe_creative_suite']['background'] ?? '#0f172a') ?>"></div>
        </div>
        
        <div class="form-group">
          <label>خلفية Trusted Clients (CSS Gradient)</label>
          <input type="text" name="clients_background" value="<?= htmlspecialchars($skillsClients['trusted_clients']['background'] ?? '') ?>" placeholder="linear-gradient(180deg, #111827 0%, #0f172a 100%)">
          <div class="background-preview" style="background: <?= htmlspecialchars($skillsClients['trusted_clients']['background'] ?? '#111827') ?>"></div>
        </div>
        
        <button type="submit" class="btn">حفظ الخلفيات</button>
      </form>
    </section>
    <?php endif; ?>

    <!-- Adobe Creative Suite Tab -->
    <?php if ($activeTab === 'adobe'): ?>
    <section class="section">
      <h2 class="section-title">إدارة برامج Adobe</h2>
      
      <form method="POST" action="" enctype="multipart/form-data" style="margin-bottom: 30px; padding: 20px; background: rgba(255,255,255,0.03); border-radius: 12px;">
        <input type="hidden" name="add_adobe_item" value="1">
        <div class="form-group">
          <label>اسم البرنامج</label>
          <input type="text" name="program_name" placeholder="مثال: After Effects" required>
        </div>
        <div class="form-group">
          <label>لون البرنامج (HEX)</label>
          <input type="color" name="program_color" value="#9999FF" style="width: 60px; height: 40px;">
        </div>
        <div class="form-group">
          <label>صورة البرنامج (PNG/SVG)</label>
          <input type="file" name="program_image" accept=".jpg,.jpeg,.png,.gif,.webp,.svg">
        </div>
        <button type="submit" class="btn btn-small">إضافة برنامج</button>
      </form>

      <h3 style="color: var(--white); margin-bottom: 16px;">البرامج المضافة:</h3>
      <?php if (!empty($skillsClients['adobe_creative_suite']['items'])): ?>
      <div class="items-grid">
        <?php foreach ($skillsClients['adobe_creative_suite']['items'] as $index => $item): ?>
        <div class="item-card">
          <?php if ($item['image']): ?>
            <img src="../uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['program']) ?>">
          <?php else: ?>
            <div style="width: 100%; height: 100px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.05); border-radius: 8px;">
              <span style="color: rgba(255,255,255,0.4);">لا توجد صورة</span>
            </div>
          <?php endif; ?>
          <div class="item-name">
            <span class="color-preview" style="background: <?= htmlspecialchars($item['color'] ?? '#9999FF') ?>"></span>
            <?= htmlspecialchars($item['program']) ?>
          </div>
          <div class="actions">
            <button type="button" class="btn btn-small" onclick="editAdobeItem(<?= $index ?>, '<?= htmlspecialchars($item['program']) ?>', '<?= htmlspecialchars($item['color'] ?? '#9999FF') ?>')">تعديل</button>
            <form method="POST" action="" style="display: inline;">
              <input type="hidden" name="delete_adobe_item" value="1">
              <input type="hidden" name="item_index" value="<?= $index ?>">
              <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="empty-state">لا توجد برامج مضافة بعد</div>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <!-- Trusted Clients Tab -->
    <?php if ($activeTab === 'clients'): ?>
    <section class="section">
      <h2 class="section-title">إدارة عملاء Trusted Clients</h2>
      
      <form method="POST" action="" enctype="multipart/form-data" style="margin-bottom: 30px; padding: 20px; background: rgba(255,255,255,0.03); border-radius: 12px;">
        <input type="hidden" name="add_client_item" value="1">
        <div class="form-group">
          <label>اسم العميل</label>
          <input type="text" name="client_name" placeholder="مثال: Google" required>
        </div>
        <div class="form-group">
          <label>شعار العميل (Logo)</label>
          <input type="file" name="client_logo" accept=".jpg,.jpeg,.png,.gif,.webp,.svg">
        </div>
        <button type="submit" class="btn btn-small">إضافة عميل</button>
      </form>

      <h3 style="color: var(--white); margin-bottom: 16px;">العملاء المضافون:</h3>
      <?php if (!empty($skillsClients['trusted_clients']['items'])): ?>
      <div class="items-grid">
        <?php foreach ($skillsClients['trusted_clients']['items'] as $index => $item): ?>
        <div class="item-card">
          <?php if ($item['logo']): ?>
            <img src="../uploads/<?= htmlspecialchars($item['logo']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
          <?php else: ?>
            <div style="width: 100%; height: 100px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.05); border-radius: 8px;">
              <span style="color: rgba(255,255,255,0.4);">لا يوجد شعار</span>
            </div>
          <?php endif; ?>
          <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
          <div class="actions">
            <button type="button" class="btn btn-small" onclick="editClientItem(<?= $index ?>, '<?= htmlspecialchars($item['name']) ?>')">تعديل</button>
            <form method="POST" action="" style="display: inline;">
              <input type="hidden" name="delete_client_item" value="1">
              <input type="hidden" name="item_index" value="<?= $index ?>">
              <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="empty-state">لا يوجد عملاء مضافون بعد</div>
      <?php endif; ?>
    </section>
    <?php endif; ?>
  </main>

  <!-- Edit Adobe Modal -->
  <div id="editAdobeModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">تعديل برنامج Adobe</h3>
        <button class="modal-close" onclick="closeModal('editAdobeModal')">&times;</button>
      </div>
      <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="edit_adobe_item" value="1">
        <input type="hidden" name="item_index" id="editAdobeIndex">
        <div class="form-group">
          <label>اسم البرنامج</label>
          <input type="text" name="program_name" id="editAdobeName" required>
        </div>
        <div class="form-group">
          <label>لون البرنامج</label>
          <input type="color" name="program_color" id="editAdobeColor" style="width: 60px; height: 40px;">
        </div>
        <div class="form-group">
          <label>صورة جديدة (اختياري)</label>
          <input type="file" name="program_image" accept=".jpg,.jpeg,.png,.gif,.webp,.svg">
        </div>
        <button type="submit" class="btn">حفظ التعديلات</button>
      </form>
    </div>
  </div>

  <!-- Edit Client Modal -->
  <div id="editClientModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">تعديل عميل</h3>
        <button class="modal-close" onclick="closeModal('editClientModal')">&times;</button>
      </div>
      <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="edit_client_item" value="1">
        <input type="hidden" name="item_index" id="editClientIndex">
        <div class="form-group">
          <label>اسم العميل</label>
          <input type="text" name="client_name" id="editClientName" required>
        </div>
        <div class="form-group">
          <label>شعار جديد (اختياري)</label>
          <input type="file" name="client_logo" accept=".jpg,.jpeg,.png,.gif,.webp,.svg">
        </div>
        <button type="submit" class="btn">حفظ التعديلات</button>
      </form>
    </div>
  </div>

  <script>
    function editAdobeItem(index, name, color) {
      document.getElementById('editAdobeIndex').value = index;
      document.getElementById('editAdobeName').value = name;
      document.getElementById('editAdobeColor').value = color;
      document.getElementById('editAdobeModal').classList.add('active');
    }
    
    function editClientItem(index, name) {
      document.getElementById('editClientIndex').value = index;
      document.getElementById('editClientName').value = name;
      document.getElementById('editClientModal').classList.add('active');
    }
    
    function closeModal(modalId) {
      document.getElementById(modalId).classList.remove('active');
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
      }
    }
  </script>
</body>
</html>
