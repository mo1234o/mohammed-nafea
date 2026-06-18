<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/admin_config.php';

$sectionsFile = __DIR__ . '/../data/sections.json';
$defaultSections = [
    'hero' => ['visible'=>true,'order'=>1],
    'about' => ['visible'=>true,'order'=>2],
    'skills' => ['visible'=>true,'order'=>3],
    'experience' => ['visible'=>true,'order'=>4],
    'projects' => ['visible'=>true,'order'=>5],
    'certs' => ['visible'=>true,'order'=>6],
    'contact' => ['visible'=>true,'order'=>7],
];
$sections = $defaultSections;
if (file_exists($sectionsFile)) {
    $sections = json_decode(file_get_contents($sectionsFile), true) ?: $defaultSections;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_sections'])) {
    $newSections = [];
    foreach ($sections as $key => $val) {
        $newSections[$key] = [
            'visible' => isset($_POST[$key . '_visible']),
            'order' => (int)($_POST[$key . '_order'] ?? $val['order']),
        ];
    }
    uasort($newSections, fn($a,$b) => $a['order'] <=> $b['order']);
    file_put_contents($sectionsFile, json_encode($newSections, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    $sections = $newSections;
    $success = 'تم حفظ إعدادات الأقسام.';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>الأقسام - Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: linear-gradient(135deg, #0A1D37, #071428);
      color: #fff;
      font-family: 'Cairo', sans-serif;
      min-height: 100vh;
    }
    .header {
      background: rgba(255,255,255,0.05);
      border-bottom: 1px solid rgba(0,194,199,0.25);
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
      max-width: 960px;
      margin: 0 auto;
    }
    .section {
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(0,194,199,0.2);
      border-radius: 16px;
      padding: 24px;
      margin-bottom: 24px;
    }
    .section-title {
      font-size: 1.2rem;
      color: #00C2C7;
      font-weight: 700;
      margin-bottom: 20px;
      letter-spacing: 1px;
    }
    .section-item {
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 12px;
      padding: 16px;
      margin-bottom: 16px;
      display: flex; align-items: center; gap: 16px;
      cursor: move;
      transition: all 0.3s;
    }
    .section-item.sortable-ghost {
      opacity: 0.4;
      transform: scale(1.02);
    }
    .section-item.sortable-drag {
      opacity: 0.9;
      transform: scale(1.05);
      box-shadow: 0 10px 40px rgba(0,194,199,0.3);
    }
    .drag-handle {
      color: rgba(255,255,255,0.4);
      font-size: 1.2rem;
      cursor: grab;
      padding: 4px;
    }
    .drag-handle:active {
      cursor: grabbing;
    }
    .section-name {
      flex: 1;
      font-size: 1.1rem;
      font-weight: 700;
      color: #D4AF37;
    }
    .section-order {
      width: 80px;
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.15);
      border-radius: 8px;
      padding: 8px;
      color: #fff;
      font-size: 0.95rem;
      text-align: center;
    }
    .section-visible {
      display: flex; align-items: center; gap: 8px; }
    .section-visible input[type="checkbox"] {
      width: 22px; height: 22px; cursor: pointer; }
    .btn {
      background: linear-gradient(135deg, #D4AF37, #b8942e);
      color: #0A1D37;
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
      box-shadow: 0 12px 40px rgba(212,175,55,0.4);
    }
    .msg {
      padding: 14px;
      border-radius: 10px;
      margin-bottom: 20px;
      text-align: center;
      font-weight: 600;
    }
    .msg.success {
      background: rgba(0,194,199,0.1);
      border: 1px solid #00C2C7;
      color: #00C2C7;
    }
    .msg.error {
      background: rgba(255,80,80,0.1);
      border: 1px solid rgba(255,80,80,0.4);
      color: #ff6060;
    }
  </style>
</head>
<body>
  <header class="header">
    <h1>Dashboard - الأقسام</h1>
    <div class="nav-links">
      <a href="index.php" class="nav-tab">الرئيسية</a>
      <a href="projects.php" class="nav-tab">المشاريع</a>
      <a href="theme.php" class="nav-tab">الألوان</a>
      <a href="skills_clients.php" class="nav-tab">الخلفيات</a>
      <a href="certificates.php" class="nav-tab">الشهادات</a>
      <a href="sections.php" class="nav-tab active">الأقسام</a>
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

    <section class="section">
      <h2 class="section-title">إظهار/إخفاء وترتيب الأقسام</h2>
      <form method="POST" action="" id="sectionsForm">
        <input type="hidden" name="save_sections" value="1">
        <div id="sortable-list">
          <?php foreach ($sections as $key => $val): ?>
            <div class="section-item" data-section="<?= htmlspecialchars($key) ?>">
              <div class="drag-handle">⋮⋮</div>
              <div class="section-name"><?= htmlspecialchars(ucfirst($key)) ?></div>
              <div class="section-visible">
                <input type="checkbox" name="<?= htmlspecialchars($key) ?>_visible" <?= $val['visible'] ? 'checked' : '' ?>>
                <label for="<?= htmlspecialchars($key) ?>_visible">إظهار</label>
              </div>
              <input type="number" name="<?= htmlspecialchars($key) ?>_order" value="<?= $val['order'] ?>" min="1" max="9" class="section-order" placeholder="ترتيب">
            </div>
          <?php endforeach; ?>
        </div>
        <button type="submit" class="btn">حفظ الإعدادات</button>
      </form>
    </section>
  </main>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const sortableList = document.getElementById('sortable-list');
  new Sortable(sortableList, {
    animation: 150,
    ghostClass: 'sortable-ghost',
    dragClass: 'sortable-drag',
    handle: '.drag-handle',
    onEnd: function(evt) {
      // Update order numbers based on new position
      const items = sortableList.querySelectorAll('.section-item');
      items.forEach((item, index) => {
        const orderInput = item.querySelector('.section-order');
        if (orderInput) {
          orderInput.value = index + 1;
        }
      });
    }
  });
});
</script>

</body>
</html>
