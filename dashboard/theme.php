<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/admin_config.php';

$themeFile = __DIR__ . '/../data/theme.json';
$theme = [
    '--dark' => '#0A1D37',
    '--teal' => '#00C2C7',
    '--white' => '#FFFFFF',
    '--gold' => '#D4AF37'
];
if (file_exists($themeFile)) {
    $theme = json_decode(file_get_contents($themeFile), true) ?: $theme;
}

// Handle AJAX save request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_save'])) {
    header('Content-Type: application/json');
    $newTheme = [];
    foreach ($_POST as $k => $v) {
        if (strpos($k, '--') === 0) {
            $newTheme[$k] = trim($v);
        }
    }
    if (file_put_contents($themeFile, json_encode($newTheme, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'message' => 'تم حفظ الألوان بنجاح!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل في الحفظ!']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>الألوان - Dashboard</title>
  <style>
    :root {
      --dark: <?= htmlspecialchars($theme['--dark'] ?? '#0A1D37') ?>;
      --teal: <?= htmlspecialchars($theme['--teal'] ?? '#00C2C7') ?>;
      --white: <?= htmlspecialchars($theme['--white'] ?? '#FFFFFF') ?>;
      --gold: <?= htmlspecialchars($theme['--gold'] ?? '#D4AF37') ?>;
      /* Derived colors */
      --dark2: #071428;
      --teal-dim: rgba(0,194,199,0.15);
      --gold-dim: rgba(212,175,55,0.12);
      /* Accent gradient */
      --accent-1: var(--teal);
      --accent-2: var(--gold);
      --accent-gradient: linear-gradient(90deg, var(--accent-1) 0%, var(--accent-2) 100%);
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
      max-width: 960px;
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
      margin-bottom: 18px; display: flex; align-items: center; gap: 12px; }
    .form-group label {
      display: block;
      font-size: 0.85rem;
      color: rgba(255,255,255,0.75);
      min-width: 140px;
      letter-spacing: 0.5px;
    }
    .form-group input[type="color"] {
      width: 60px; height: 40px; border: none; border-radius: 8px; cursor: pointer; }
    .form-group input[type="text"] {
      flex: 1;
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.15);
      border-radius: 10px;
      padding: 12px 14px;
      color: #fff;
      font-size: 0.95rem;
      font-family: 'Cairo', sans-serif;
      transition: all 0.3s;
    }
    .form-group input[type="text"]:focus {
      outline: none;
      border-color: #00C2C7;
      box-shadow: 0 0 0 3px rgba(0,194,199,0.1);
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
      margin-left: 8px;
    }
    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 40px var(--gold-dim);
    }
    .btn-preview {
      background: linear-gradient(135deg, var(--teal), #00a0a5);
      color: var(--white);
    }
    .btn-preview:hover {
      box-shadow: 0 12px 40px var(--teal-dim);
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
    .msg.info {
      background: rgba(212,175,55,0.1);
      border: 1px solid rgba(212,175,55,0.4);
      color: #D4AF37;
    }
    .preview-container {
      margin-top: 30px;
      border: 2px solid var(--teal);
      border-radius: 12px;
      overflow: hidden;
    }
    .preview-container iframe {
      width: 100%;
      height: 400px;
      border: none;
    }
    .two-column {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
    }
    @media (max-width: 1024px) {
      .two-column { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <header class="header">
    <h1>Dashboard - الألوان</h1>
    <div class="nav-links">
      <a href="index.php" class="nav-tab">الرئيسية</a>
      <a href="projects.php" class="nav-tab">المشاريع</a>
      <a href="theme.php" class="nav-tab active">الألوان</a>
      <a href="skills_clients.php" class="nav-tab">الخلفيات</a>
      <a href="certificates.php" class="nav-tab">الشهادات</a>
      <a href="sections.php" class="nav-tab">الأقسام</a>
    </div>
    <div class="logout"><a href="logout.php">تسجيل خروج</a></div>
  </header>
  <main class="main">
    <div id="ajaxMessage" class="msg" style="display: none;"></div>

    <div class="two-column">
      <section class="section">
        <h2 class="section-title">تعديل ألوان الموقع (CSS Variables)</h2>
        <p style="color: rgba(255,255,255,0.7); margin-bottom: 15px;">غير اللون والمعاينة بتتحدث فوراً. اضغط "حفظ" عشان تثبت التغييرات.</p>
        <div id="themeForm">
          <?php foreach ($theme as $key => $val): ?>
            <div class="form-group">
              <label><?= htmlspecialchars($key) ?></label>
              <input type="color" id="color_<?= htmlspecialchars($key) ?>" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($val) ?>" onchange="updatePreview()">
              <input type="text" id="text_<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($val) ?>" placeholder="#000000" onchange="updatePreviewFromText(this, 'color_<?= htmlspecialchars($key) ?>')">
            </div>
          <?php endforeach; ?>
          <button type="button" class="btn" onclick="saveTheme()">حفظ الألوان</button>
          <button type="button" class="btn btn-preview" onclick="resetToDefaults()">استعادة الألوان الافتراضية</button>
        </div>
      </section>
      
      <section class="section">
        <h2 class="section-title">معاينة مباشرة للموقع (index.php)</h2>
        <p style="color: rgba(255,255,255,0.7); margin-bottom: 15px;">تغيير الألوان يظهر فوراً في المعاينة أسفل:</p>
        <div class="preview-container">
          <iframe id="livePreview" src="../index.php?preview=1&t=<?= time() ?>"></iframe>
        </div>
      </section>
    </div>
  </main>

<script>
// Default colors (4 colors only)
const defaultColors = {
  '--dark': '#0A1D37',
  '--teal': '#00C2C7',
  '--white': '#FFFFFF',
  '--gold': '#D4AF37'
};

// Live preview update by sending colors to iframe
function updatePreview() {
  const form = document.getElementById('themeForm');
  const colorInputs = form.querySelectorAll('input[type="color"]');
  
  // Sync text inputs with color inputs
  colorInputs.forEach(colorInput => {
    const textInput = document.getElementById('text_' + colorInput.name);
    if (textInput) textInput.value = colorInput.value;
  });
  
  // Send colors to iframe
  const iframe = document.getElementById('livePreview');
  const themeData = {};
  colorInputs.forEach(input => {
    themeData[input.name] = input.value;
  });
  
  // Post message to iframe with theme data
  if (iframe && iframe.contentWindow) {
    iframe.contentWindow.postMessage({type: 'themeUpdate', theme: themeData}, '*');
  }
  
  // Also update dashboard preview
  const root = document.documentElement;
  for (const [key, value] of Object.entries(themeData)) {
    root.style.setProperty(key, value);
  }
}

// Update from text input and sync to color input
function updatePreviewFromText(textInput, colorId) {
  const colorInput = document.getElementById(colorId);
  let value = textInput.value;
  
  // Ensure value starts with # for color input
  if (value && !value.startsWith('#')) {
    value = '#' + value;
  }
  
  if (colorInput && /^#[0-9A-Fa-f]{6}$/.test(value)) {
    colorInput.value = value;
    updatePreview();
  }
}

// Reset to default colors
function resetToDefaults() {
  for (const [key, value] of Object.entries(defaultColors)) {
    const colorInput = document.getElementById('color_' + key);
    const textInput = document.getElementById('text_' + key);
    if (colorInput) colorInput.value = value;
    if (textInput) textInput.value = value;
  }
  updatePreview();
  showMessage('تم استعادة الألوان الافتراضية. اضغط "حفظ" عشان تثبت التغييرات.', 'info');
}

// Show message
function showMessage(message, type) {
  const msgDiv = document.getElementById('ajaxMessage');
  msgDiv.textContent = message;
  msgDiv.className = 'msg ' + (type === 'success' ? 'success' : type === 'error' ? 'error' : '');
  msgDiv.style.display = 'block';
  
  setTimeout(() => {
    msgDiv.style.display = 'none';
  }, 5000);
}

// Save theme via AJAX
function saveTheme() {
  const form = document.getElementById('themeForm');
  const colorInputs = form.querySelectorAll('input[type="color"]');
  
  const formData = new FormData();
  formData.append('ajax_save', '1');
  
  colorInputs.forEach(input => {
    formData.append(input.name, input.value);
  });
  
  fetch('', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showMessage(data.message, 'success');
    } else {
      showMessage(data.message || 'فشل في الحفظ!', 'error');
    }
  })
  .catch(error => {
    showMessage('فشل في الاتصال بالخادم!', 'error');
  });
}

// Listen for messages from parent (for cross-origin, may not work - fallback to refresh)
window.addEventListener('message', function(e) {
  if (e.data && e.data.type === 'themeUpdate') {
    const theme = e.data.theme;
    const root = document.documentElement;
    for (const [key, value] of Object.entries(theme)) {
      root.style.setProperty(key, value);
    }
  }
});

// Initialize preview on page load
window.onload = function() {
  updatePreview();
};
</script>

</body>
</html>
