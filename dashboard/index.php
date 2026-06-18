<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/admin_config.php';
$config = require __DIR__ . '/admin_config.php';

$success = '';
$error = '';

// Handle content save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_content'])) {
    $content = [];
    $fields = ['hero_tag','hero_name','hero_quote','about_title','about_subtitle','about_stats','skills_title','skills_intro','experience_title','experience_items','skills_items','skills_images','certificates_items','projects_title','contact_subtitle'];
    foreach ($fields as $f) {
        $content[$f] = trim($_POST[$f] ?? '');
    }
    file_put_contents(__DIR__ . '/../data/content.json', json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    $success = 'تم حفظ المحتوى بنجاح.';
}

// Load current content
$contentFile = __DIR__ . '/../data/content.json';
$content = [];
if (file_exists($contentFile)) {
    $content = json_decode(file_get_contents($contentFile), true) ?: [];
}

// Load projects
$projectsFile = __DIR__ . '/../data/projects.json';
$projects = [];
if (file_exists($projectsFile)) {
    $projects = json_decode(file_get_contents($projectsFile), true) ?: [];
}

// Load sections
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

// Load theme
$themeFile = __DIR__ . '/../data/theme.json';
$theme = [
    '--dark' => '#0A1D37',
    '--teal' => '#00C2C7',
    '--white' => '#FFFFFF',
    '--gold' => '#D4AF37',
    '--gold-light' => '#f0d060',
    '--dark2' => '#071428',
    '--teal-dim' => 'rgba(0,194,199,0.15)',
    '--gold-dim' => 'rgba(212,175,55,0.12)',
];
if (file_exists($themeFile)) {
    $theme = json_decode(file_get_contents($themeFile), true) ?: $theme;
}

// Handle projects POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_project'])) {
        $title = trim($_POST['title'] ?? '');
        $desc = trim($_POST['desc'] ?? '');
        $tag = trim($_POST['tag'] ?? '');
        $error = '';
        
        if ($title && $desc) {
            $newProj = [
                'id' => 'proj_' . uniqid(),
                'title' => $title,
                'desc' => $desc,
                'tag' => $tag,
                'thumb' => ''
            ];
            
            // Handle image upload
            if (isset($_FILES['thumb']) && $_FILES['thumb']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($_FILES['thumb']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $filename = 'proj_' . uniqid() . '.' . $ext;
                    $uploadDir = __DIR__ . '/../uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    if (move_uploaded_file($_FILES['thumb']['tmp_name'], $uploadDir . $filename)) {
                        $newProj['thumb'] = $filename;
                    }
                }
            }
            
            $projects[] = $newProj;
            file_put_contents($projectsFile, json_encode($projects, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $success = 'تم إضافة المشروع.';
        } else {
            $error = 'املأ العنوان والوصف.';
        }
    } elseif (isset($_POST['edit_project'])) {
        $id = $_POST['id'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $desc = trim($_POST['desc'] ?? '');
        $tag = trim($_POST['tag'] ?? '');
        $found = false;
        foreach ($projects as &$proj) {
            if ($proj['id'] === $id) {
                $found = true;
                $proj['title'] = $title;
                $proj['desc'] = $desc;
                $proj['tag'] = $tag;
                break;
            }
        }
        if (!$error && $found && $title && $desc) {
            file_put_contents($projectsFile, json_encode($projects, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $success = 'تم تعديل المشروع.';
        } elseif (!$found) {
            $error = 'المشروع مش موجود.';
        } elseif (!$error) {
            $error = 'املأ العنوان والوصف.';
        }
    } elseif (isset($_POST['delete_project'])) {
        $id = $_POST['id'] ?? '';
        foreach ($projects as $i => $proj) {
            if ($proj['id'] === $id) {
                // Delete image if exists
                if ($proj['thumb']) {
                    $imgPath = __DIR__ . '/../uploads/' . $proj['thumb'];
                    if (file_exists($imgPath)) unlink($imgPath);
                }
                unset($projects[$i]);
                $projects = array_values($projects);
                file_put_contents($projectsFile, json_encode($projects, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                $success = 'تم حذف المشروع.';
                break;
            }
        }
    } elseif (isset($_POST['save_theme'])) {
        $newTheme = [];
        foreach (array_keys($theme) as $k) {
            $newTheme[$k] = trim($_POST[$k] ?? '');
        }
        file_put_contents($themeFile, json_encode($newTheme, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $theme = $newTheme;
        $success = 'تم حفظ الألوان.';
    } elseif (isset($_POST['preview_theme'])) {
        $previewTheme = [];
        foreach (array_keys($theme) as $k) {
            $previewTheme[$k] = trim($_POST[$k] ?? '');
        }
        $theme = $previewTheme;
    } elseif (isset($_POST['save_sections'])) {
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
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Mohammed Nafea</title>
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
    .form-group {
      margin-bottom: 18px; }
    .form-group label {
      display: block;
      font-size: 0.85rem;
      color: rgba(255,255,255,0.75);
      margin-bottom: 6px;
      letter-spacing: 0.5px;
    }
    .form-group input,
    .form-group textarea,
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
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      outline: none;
      border-color: #00C2C7;
      box-shadow: 0 0 0 3px rgba(0,194,199,0.1);
    }
    .form-group textarea {
      min-height: 100px;
      resize: vertical;
    }
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
    .nav-tabs {
      display: flex;
      gap: 12px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .nav-tab {
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.15);
      color: rgba(255,255,255,0.7);
      padding: 10px 20px;
      border-radius: 20px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
    }
    .nav-tab:hover,
    .nav-tab.active {
      background: rgba(0,194,199,0.15);
      border-color: #00C2C7;
      color: #00C2C7;
    }
    .section-selector {
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(0,194,199,0.2);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 24px;
    }
    .section-selector label {
      display: block;
      font-size: 1.1rem;
      color: #00C2C7;
      font-weight: 700;
      margin-bottom: 10px;
    }
    .section-selector select {
      background: rgba(255,255,255,0.1);
      border: 1px solid rgba(255,255,255,0.2);
      color: #fff;
      padding: 12px 16px;
      border-radius: 8px;
      font-size: 1rem;
      width: 250px;
      cursor: pointer;
    }
    .section-selector select:focus {
      outline: none;
      border-color: #00C2C7;
      box-shadow: 0 0 0 3px rgba(0,194,199,0.1);
    }
    .mini-projects-list {
      display: grid; gap: 16px; margin-bottom: 24px; }
    .mini-project-item {
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 12px;
      padding: 16px;
      display: flex; gap: 16px; align-items: start;
    }
    .mini-project-info {
      flex: 1;
    }
    .mini-project-info strong {
      color: #D4AF37;
      font-size: 1.1rem;
      display: block;
      margin-bottom: 8px;
    }
    .mini-project-info p {
      color: rgba(255,255,255,0.8);
      margin-bottom: 8px;
    }
    .mini-project-tag {
      background: rgba(0,194,199,0.15);
      color: #00C2C7;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
    }
    .mini-project-actions {
      display: flex; gap: 8px; flex-direction: column; }
    .btn-edit {
      background: linear-gradient(135deg, #00C2C7, #00a0a5);
      color: #fff;
      padding: 6px 12px;
      font-size: 0.85rem;
    }
    .btn-edit:hover {
      box-shadow: 0 12px 40px rgba(0,194,199,0.4);
    }
    .btn-danger {
      background: linear-gradient(135deg, #ff6060, #e04444);
      color: #fff;
      padding: 6px 12px;
      font-size: 0.85rem;
    }
    .btn-preview {
      background: linear-gradient(135deg, #00C2C7, #00a0a5);
      color: #fff;
      margin-right: 8px;
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
    .section-name {
      flex: 1;
      font-size: 1.1rem;
      font-weight: 700;
      color: #D4AF37;
    }
    .section-order {
      width: 80px;
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.2);
      color: #fff;
      padding: 8px;
      border-radius: 6px;
      text-align: center;
    }
    .section-visible {
      display: flex; align-items: center; gap: 8px; }
    .section-visible input[type="checkbox"] {
      width: 18px; height: 18px; }
    .section-visible label {
      color: rgba(255,255,255,0.8); font-size: 0.9rem; }
    
    /* Hide scrollbar on mobile devices */
    @media (max-width: 768px) {
      ::-webkit-scrollbar {
        display: none;
      }
      
      html {
        -ms-overflow-style: none;
        scrollbar-width: none;
      }
      
      body {
        overflow-x: hidden;
      }
    }

    /* Disable text selection and copying globally */
    * {
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
      -webkit-touch-callout: none;
      -webkit-tap-highlight-color: transparent;
    }

    /* Allow text selection for inputs and textareas in forms */
    input, textarea, select {
      -webkit-user-select: text;
      -moz-user-select: text;
      -ms-user-select: text;
      user-select: text;
    }

    /* Disable right-click context menu */
    body {
      -webkit-touch-callout: none;
      -webkit-user-select: none;
      -khtml-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
    }
  </style>
</head>
<body>
  <header class="header">
    <h1>Dashboard - Mohammed Nafea</h1>
    <div class="nav-links">
      <a href="index.php" class="nav-tab active">الرئيسية</a>
      <a href="projects.php" class="nav-tab">المشاريع</a>
      <a href="theme.php" class="nav-tab">الألوان</a>
      <a href="skills_clients.php" class="nav-tab">الخلفيات</a>
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

    <div class="section-selector">
      <label for="sectionSelect">اختر القسم للتعديل:</label>
      <select id="sectionSelect" onchange="showSection(this.value)">
        <option value="content">المحتوى</option>
        <option value="projects">المشاريع</option>
        <option value="theme">الألوان</option>
        <option value="sections">الأقسام</option>
      </select>
    </div>

    <!-- CONTENT SECTION -->
    <section id="content-section" class="section">
      <h2 class="section-title">تعديل المحتوى</h2>
      <form method="POST" action="">
        <input type="hidden" name="save_content" value="1">
        <div class="form-group">
          <label>Hero Tag (مثال: Motion Graphics & 2D Animation)</label>
          <input type="text" name="hero_tag" value="<?= htmlspecialchars($content['hero_tag'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Hero Name (مثال: Mohammed Nafea)</label>
          <input type="text" name="hero_name" value="<?= htmlspecialchars($content['hero_name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Hero Quote (الاقتباس)</label>
          <input type="text" name="hero_quote" value="<?= htmlspecialchars($content['hero_quote'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>About Title (عنوان قسم عني)</label>
          <input type="text" name="about_title" value="<?= htmlspecialchars($content['about_title'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>About Subtitle (نص قسم عني)</label>
          <textarea name="about_subtitle"><?= htmlspecialchars($content['about_subtitle'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>About Stats (إحصائيات قسم عني - JSON)</label>
          <textarea name="about_stats" rows="4" placeholder='[{"num":"3+","label":"سنوات خبرة"},{"num":"10+","label":"لوجو أنيميشن"}]'><?= htmlspecialchars($content['about_stats'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Skills Title (عنوان المهارات)</label>
          <input type="text" name="skills_title" value="<?= htmlspecialchars($content['skills_title'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Skills Intro (مقدمة المهارات)</label>
          <textarea name="skills_intro"><?= htmlspecialchars($content['skills_intro'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Experience Title (عنوان الخبرة)</label>
          <input type="text" name="experience_title" value="<?= htmlspecialchars($content['experience_title'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Experience Items (خبرات العمل - JSON)</label>
          <textarea name="experience_items" rows="6" placeholder='[{"company":"IBAN Company","role":"Motion Graphics Designer","period":"ديسمبر 2024 – حتى الآن · Remote","bullets":["تسليم رسوم متحركة ثنائية الأبعاد مخصصة للعلامات التجارية في قطاع السيارات","تحويل المفاهيم البصرية إلى رسوم سلسة للتواصل الاجتماعي والترويج"]}]'><?= htmlspecialchars($content['experience_items'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Skills Items (المهارات - JSON)</label>
          <textarea name="skills_items" rows="6" placeholder='[{"group":"Adobe Suite","skills":["After Effects","Premiere Pro","Illustrator","Photoshop"]},{"group":"Design & Animation","skills":["Motion Graphics","2D Animation","Visual Storytelling"]}]'><?= htmlspecialchars($content['skills_items'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Skills Images (Adobe Program Images - JSON)</label>
          <textarea name="skills_images" rows="4" placeholder='[{"program":"After Effects","image":"after-effects.jpg"},{"program":"Premiere Pro","image":"premiere-pro.jpg"},{"program":"Illustrator","image":"illustrator.jpg"},{"program":"Photoshop","image":"photoshop.jpg"},{"program":"Character Animator","image":"character-animator.jpg"},{"program":"Audition","image":"audition.jpg"}]'><?= htmlspecialchars($content['skills_images'] ?? '') ?></textarea>
          <small>Upload images to uploads/ folder and enter filenames here</small>
        </div>
        <div class="form-group">
          <label>Certificates Items (الشهادات - JSON)</label>
          <textarea name="certificates_items" rows="6" placeholder='[{"title":"After Effects CC: Ultimate Motion Graphics Masterclass","issuer":"Udemy (23.5 hours)","period":"03/2023 – 05/2023"},{"title":"Adobe Illustrator Mega Course – Beginner to Advanced","issuer":"Udemy (18.5 hours)","period":"01/2023 – 03/2023"}]'><?= htmlspecialchars($content['certificates_items'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Projects Title (عنوان المشاريع)</label>
          <input type="text" name="projects_title" value="<?= htmlspecialchars($content['projects_title'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Contact Subtitle (نص قسم التواصل)</label>
          <textarea name="contact_subtitle"><?= htmlspecialchars($content['contact_subtitle'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn">حفظ المحتوى</button>
      </form>
    </section>

    <!-- PROJECTS SECTION -->
    <section id="projects-section" class="section" style="display:none;">
      <h2 class="section-title">إدارة المشاريع</h2>
      <div class="projects-mini-manager">
        <h3>المشاريع الحالية</h3>
        <div class="mini-projects-list">
          <?php foreach ($projects as $proj): ?>
            <div class="mini-project-item">
              <div class="mini-project-info">
                <strong><?= htmlspecialchars($proj['title']) ?></strong>
                <p><?= htmlspecialchars($proj['desc']) ?></p>
                <?php if ($proj['tag']): ?>
                  <span class="mini-project-tag"><?= htmlspecialchars($proj['tag']) ?></span>
                <?php endif; ?>
              </div>
              <div class="mini-project-actions">
                <button type="button" class="btn btn-edit" onclick="editProject('<?= htmlspecialchars($proj['id']) ?>', '<?= htmlspecialchars($proj['title'], ENT_QUOTES) ?>', '<?= htmlspecialchars($proj['desc'], ENT_QUOTES) ?>', '<?= htmlspecialchars($proj['tag'], ENT_QUOTES) ?>')">تعديل</button>
                <button type="button" class="btn btn-danger" onclick="deleteProject('<?= htmlspecialchars($proj['id']) ?>')">حذف</button>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if (empty($projects)): ?>
            <p style="color:rgba(255,255,255,0.5);">لا توجد مشاريع حاليًا.</p>
          <?php endif; ?>
        </div>
        
        <h3>إضافة مشروع جديد</h3>
        <form method="POST" action="" enctype="multipart/form-data">
          <input type="hidden" name="add_project" value="1">
          <div class="form-group">
            <label>عنوان المشروع *</label>
            <input type="text" name="title" required>
          </div>
          <div class="form-group">
            <label>الوصف *</label>
            <textarea name="desc" required></textarea>
          </div>
          <div class="form-group">
            <label>الوسم (اختياري)</label>
            <input type="text" name="tag" placeholder="مثال: موشن جرافيك">
          </div>
          <div class="form-group">
            <label>صورة المشروع (اختياري)</label>
            <input type="file" name="thumb" accept="image/*">
          </div>
          <button type="submit" class="btn">إضافة المشروع</button>
        </form>
      </div>
    </section>

    <!-- THEME SECTION -->
    <section id="theme-section" class="section" style="display:none;">
      <h2 class="section-title">تعديل الألوان</h2>
      <form method="POST" action="" id="themeForm">
        <input type="hidden" name="save_theme" value="1">
        <?php foreach ($theme as $key => $val): ?>
          <div class="form-group">
            <label><?= htmlspecialchars($key) ?></label>
            <input type="color" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($val) ?>" onchange="livePreview()">
            <input type="text" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($val) ?>" placeholder="#000000" onchange="livePreview()">
          </div>
        <?php endforeach; ?>
        <button type="submit" class="btn">حفظ الألوان</button>
        <button type="button" class="btn btn-preview" onclick="document.getElementById('themeForm').querySelector('[name=preview_theme]').value='1'; document.getElementById('themeForm').submit();">معاينة فورية</button>
        <input type="hidden" name="preview_theme" value="">
      </form>
    </section>

    <!-- SECTIONS SECTION -->
    <section id="sections-section" class="section" style="display:none;">
      <h2 class="section-title">إدارة الأقسام</h2>
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

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
function showSection(section) {
  // Hide all sections
  document.getElementById('content-section').style.display = 'none';
  document.getElementById('projects-section').style.display = 'none';
  document.getElementById('theme-section').style.display = 'none';
  document.getElementById('sections-section').style.display = 'none';
  
  // Show selected section
  document.getElementById(section + '-section').style.display = 'block';
}

function editProject(id, title, desc, tag) {
  const newTitle = prompt('عنوان المشروع:', title);
  if (newTitle === null) return;
  
  const newDesc = prompt('وصف المشروع:', desc);
  if (newDesc === null) return;
  
  const newTag = prompt('وسم المشروع:', tag);
  if (newTag === null) return;
  
  // Create form to submit edit
  const form = document.createElement('form');
  form.method = 'POST';
  form.innerHTML = `
    <input type="hidden" name="edit_project" value="1">
    <input type="hidden" name="id" value="${id}">
    <input type="hidden" name="title" value="${newTitle}">
    <input type="hidden" name="desc" value="${newDesc}">
    <input type="hidden" name="tag" value="${newTag}">
  `;
  document.body.appendChild(form);
  form.submit();
}

function deleteProject(id) {
  if (!confirm('هل أنت متأكد من حذف هذا المشروع؟')) return;
  
  // Create form to submit delete
  const form = document.createElement('form');
  form.method = 'POST';
  form.innerHTML = `
    <input type="hidden" name="delete_project" value="1">
    <input type="hidden" name="id" value="${id}">
  `;
  document.body.appendChild(form);
  form.submit();
}

function livePreview() {
  const form = document.getElementById('themeForm');
  const inputs = form.querySelectorAll('input[type="color"], input[type="text"]');
  const root = document.documentElement;
  inputs.forEach(input => {
    if (input.name && input.name.startsWith('--')) {
      root.style.setProperty(input.name, input.value);
    }
  });
}

// ── DISABLE COPY AND PASTE ──
// Disable right-click context menu
document.addEventListener('contextmenu', function(e) {
  e.preventDefault();
  return false;
});

// Disable text selection shortcuts
document.addEventListener('keydown', function(e) {
  // Disable Ctrl+A (select all)
  if (e.ctrlKey && e.key === 'a') {
    e.preventDefault();
    return false;
  }
  
  // Disable Ctrl+C (copy)
  if (e.ctrlKey && e.key === 'c') {
    e.preventDefault();
    return false;
  }
  
  // Disable Ctrl+X (cut)
  if (e.ctrlKey && e.key === 'x') {
    e.preventDefault();
    return false;
  }
  
  // Disable Ctrl+V (paste) - only outside forms
  if (e.ctrlKey && e.key === 'v') {
    const target = e.target;
    if (!target.matches('input, textarea')) {
      e.preventDefault();
      return false;
    }
  }
  
  // Disable F12 (developer tools)
  if (e.key === 'F12') {
    e.preventDefault();
    return false;
  }
  
  // Disable Ctrl+Shift+I (developer tools)
  if (e.ctrlKey && e.shiftKey && e.key === 'I') {
    e.preventDefault();
    return false;
  }
  
  // Disable Ctrl+Shift+J (console)
  if (e.ctrlKey && e.shiftKey && e.key === 'J') {
    e.preventDefault();
    return false;
  }
  
  // Disable Ctrl+U (view source)
  if (e.ctrlKey && e.key === 'u') {
    e.preventDefault();
    return false;
  }
});

// Disable drag events
document.addEventListener('dragstart', function(e) {
  e.preventDefault();
  return false;
});

// Disable select start
document.addEventListener('selectstart', function(e) {
  const target = e.target;
  if (!target.matches('input, textarea')) {
    e.preventDefault();
    return false;
  }
});

// Allow text selection only in specific elements
document.addEventListener('mousedown', function(e) {
  const allowedElements = 'input, textarea, select';
  if (!e.target.matches(allowedElements) && !e.target.closest(allowedElements)) {
    e.preventDefault();
  }
});

// Initialize sortable for sections
document.addEventListener('DOMContentLoaded', function() {
  const sortableList = document.getElementById('sortable-list');
  if (sortableList) {
    new Sortable(sortableList, {
      animation: 150,
      ghostClass: 'sortable-ghost',
      dragClass: 'sortable-drag',
      handle: '.drag-handle',
      onEnd: function(evt) {
        const items = sortableList.querySelectorAll('.section-item');
        items.forEach((item, index) => {
          const orderInput = item.querySelector('.section-order');
          if (orderInput) {
            orderInput.value = index + 1;
          }
        });
      }
    });
  }
});
</script>

</body>
</html>
