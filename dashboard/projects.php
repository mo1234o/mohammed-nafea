<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/admin_config.php';

$projectsFile = __DIR__ . '/../data/projects.json';
$projects = [];
if (file_exists($projectsFile)) {
    $projects = json_decode(file_get_contents($projectsFile), true) ?: [];
}

$success = '';
$error = '';

// Handle add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_project'])) {
        $title = trim($_POST['title'] ?? '');
        $desc = trim($_POST['desc'] ?? '');
        $tag = trim($_POST['tag'] ?? '');
        $youtube = trim($_POST['youtube'] ?? '');
        $thumb = '';
        if (isset($_FILES['thumb']) && $_FILES['thumb']['error'] === UPLOAD_ERR_OK) {
            $up = $_FILES['thumb'];
            $ext = strtolower(pathinfo($up['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (in_array($ext, $allowed)) {
                $filename = uniqid('proj_', true) . '.' . $ext;
                $dest = __DIR__ . '/../uploads/' . $filename;
                if (move_uploaded_file($up['tmp_name'], $dest)) {
                    $thumb = $filename;
                } else {
                    $error = 'فشل رفع الصورة.';
                }
            } else {
                $error = 'امتداد الصورة غير مدعوم.';
            }
        }
        if (!$error && $title && $desc) {
            $projects[] = [
                'id' => uniqid(),
                'title' => $title,
                'desc' => $desc,
                'tag' => $tag,
                'youtube' => $youtube,
                'thumb' => $thumb
            ];
            file_put_contents($projectsFile, json_encode($projects, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $success = 'تمت إضافة المشروع.';
        } elseif (!$error) {
            $error = 'املأ العنوان والوصف.';
        }
    } elseif (isset($_POST['edit_project'])) {
        $id = $_POST['id'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $desc = trim($_POST['desc'] ?? '');
        $tag = trim($_POST['tag'] ?? '');
        $youtube = trim($_POST['youtube'] ?? '');
        $found = false;
        foreach ($projects as &$proj) {
            if ($proj['id'] === $id) {
                $found = true;
                $proj['title'] = $title;
                $proj['desc'] = $desc;
                $proj['tag'] = $tag;
                $proj['youtube'] = $youtube;
                // Handle new image upload
                if (isset($_FILES['thumb']) && $_FILES['thumb']['error'] === UPLOAD_ERR_OK) {
                    $up = $_FILES['thumb'];
                    $ext = strtolower(pathinfo($up['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg','jpeg','png','gif','webp'];
                    if (in_array($ext, $allowed)) {
                        $filename = uniqid('proj_', true) . '.' . $ext;
                        $dest = __DIR__ . '/../uploads/' . $filename;
                        if (move_uploaded_file($up['tmp_name'], $dest)) {
                            // Delete old image if exists
                            if (!empty($proj['thumb'])) {
                                $oldFile = __DIR__ . '/../uploads/' . $proj['thumb'];
                                if (file_exists($oldFile)) unlink($oldFile);
                            }
                            $proj['thumb'] = $filename;
                        } else {
                            $error = 'فشل رفع الصورة الجديدة.';
                        }
                    } else {
                        $error = 'امتداد الصورة غير مدعوم.';
                    }
                }
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
        foreach ($projects as $proj) {
            if ($proj['id'] === $id && !empty($proj['thumb'])) {
                $oldFile = __DIR__ . '/../uploads/' . $proj['thumb'];
                if (file_exists($oldFile)) unlink($oldFile);
                break;
            }
        }
        $projects = array_filter($projects, fn($p) => $p['id'] !== $id);
        file_put_contents($projectsFile, json_encode(array_values($projects), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $success = 'تم حذف المشروع.';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>المشاريع - Dashboard</title>
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
    .btn-danger {
      background: linear-gradient(135deg, #ff6060, #e04444);
      color: #fff;
      padding: 8px 16px;
      font-size: 0.85rem;
    }
    .btn-danger:hover {
      box-shadow: 0 12px 40px rgba(255,80,80,0.4);
    }
    .btn-edit {
      background: linear-gradient(135deg, #00C2C7, #00a0a5);
      color: #fff;
      padding: 8px 16px;
      font-size: 0.85rem;
      margin-right: 8px;
    }
    .btn-edit:hover {
      box-shadow: 0 12px 40px rgba(0,194,199,0.4);
    }
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.7);
      align-items: center;
      justify-content: center;
    }
    .modal-content {
      background: linear-gradient(135deg, #0A1D37, #071428);
      border: 1px solid rgba(0,194,199,0.3);
      border-radius: 16px;
      padding: 28px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.6);
    }
    .modal-header {
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 20px;
    }
    .modal-title {
      font-size: 1.3rem;
      color: #D4AF37;
      font-weight: 800;
    }
    .modal-close {
      background: none;
      border: none;
      color: rgba(255,255,255,0.6);
      font-size: 1.8rem;
      cursor: pointer;
      transition: color 0.3s;
    }
    .modal-close:hover {
      color: #ff6060;
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
    .project-list {
      display: grid; gap: 16px; }
    .project-card {
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 12px;
      padding: 16px;
      display: flex; gap: 16px; align-items: start;
    }
    .project-thumb {
      width: 80px; height: 80px;
      border-radius: 10px;
      object-fit: cover;
      background: rgba(255,255,255,0.05);
      display: flex; align-items: center; justify-content: center;
      color: rgba(255,255,255,0.4);
      font-size: 2rem;
    }
    .project-info {
      flex: 1;
    }
    .project-title {
      font-size: 1.1rem; font-weight: 700; color: #D4AF37; margin-bottom: 4px; }
    .project-desc {
      font-size: 0.9rem; color: rgba(255,255,255,0.7); line-height: 1.5; margin-bottom: 8px; }
    .project-tag {
      display: inline-block;
      background: rgba(0,194,199,0.1);
      border: 1px solid rgba(0,194,199,0.25);
      color: #00C2C7;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.5px;
    }
    .project-actions {
      display: flex; gap: 8px; }
  </style>
</head>
<body>
  <header class="header">
    <h1>Dashboard - المشاريع</h1>
    <div class="nav-links">
      <a href="index.php" class="nav-tab">الرئيسية</a>
      <a href="projects.php" class="nav-tab active">المشاريع</a>
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

    <section class="section">
      <h2 class="section-title">إضافة مشروع جديد</h2>
      <form method="POST" action="" enctype="multipart/form-data">
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
          <label>رابط فيديو يوتيوب (اختياري)</label>
          <input type="url" name="youtube" placeholder="https://www.youtube.com/watch?v=...">
        </div>
        <div class="form-group">
          <label>صورة المشروع (اختياري)</label>
          <input type="file" name="thumb" accept="image/*">
        </div>
        <button type="submit" name="add_project" class="btn">إضافة المشروع</button>
      </form>
    </section>

    <section class="section">
      <h2 class="section-title">المشاريع الحالية</h2>
      <div class="project-list">
        <?php foreach ($projects as $proj): ?>
          <div class="project-card">
            <div class="project-thumb">
              <?php if ($proj['thumb']): ?>
                <img src="../uploads/<?= htmlspecialchars($proj['thumb']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">
              <?php else: ?>
                🎬
              <?php endif; ?>
            </div>
            <div class="project-info">
              <div class="project-title"><?= htmlspecialchars($proj['title']) ?></div>
              <div class="project-desc"><?= htmlspecialchars($proj['desc']) ?></div>
              <?php if ($proj['tag']): ?>
                <span class="project-tag"><?= htmlspecialchars($proj['tag']) ?></span>
              <?php endif; ?>
            </div>
            <div class="project-actions">
              <form method="POST" action="" style="display:inline;">
                <input type="hidden" name="id" value="<?= htmlspecialchars($proj['id']) ?>">
                <button type="submit" name="delete_project" class="btn btn-danger" onclick="return confirm('متأكد من الحذف؟');">حذف</button>
              </form>
              <button type="button" class="btn btn-edit" onclick="openEditModal('<?= htmlspecialchars($proj['id']) ?>', '<?= htmlspecialchars($proj['title'], ENT_QUOTES) ?>', '<?= htmlspecialchars($proj['desc'], ENT_QUOTES) ?>', '<?= htmlspecialchars($proj['tag'], ENT_QUOTES) ?>', '<?= htmlspecialchars($proj['youtube'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($proj['thumb'] ?? '') ?>')">تعديل</button>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($projects)): ?>
          <p style="color:rgba(255,255,255,0.5);">لا توجد مشاريع حاليًا.</p>
        <?php endif; ?>
      </div>
    </section>
  </main>

<!-- Edit Modal -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 class="modal-title">تعديل المشروع</h2>
      <button class="modal-close" onclick="closeEditModal()">&times;</button>
    </div>
    <form method="POST" action="" enctype="multipart/form-data">
      <input type="hidden" name="id" id="editId">
      <div class="form-group">
        <label>عنوان المشروع *</label>
        <input type="text" name="title" id="editTitle" required>
      </div>
      <div class="form-group">
        <label>الوصف *</label>
        <textarea name="desc" id="editDesc" required></textarea>
      </div>
      <div class="form-group">
        <label>الوسم (اختياري)</label>
        <input type="text" name="tag" id="editTag" placeholder="مثال: موشن جرافيك">
      </div>
      <div class="form-group">
        <label>رابط فيديو يوتيوب (اختياري)</label>
        <input type="url" name="youtube" id="editYoutube" placeholder="https://www.youtube.com/watch?v=...">
      </div>
      <div class="form-group">
        <label>صورة المشروع (اختياري - اترك فارغاً للاحتفاظ بالصورة الحالية)</label>
        <input type="file" name="thumb" accept="image/*">
        <small id="currentImagePreview" style="color:rgba(255,255,255,0.5);"></small>
      </div>
      <button type="submit" name="edit_project" class="btn">حفظ التعديلات</button>
    </form>
  </div>
</div>

<script>
function openEditModal(id, title, desc, tag, youtube, thumb) {
  document.getElementById('editId').value = id;
  document.getElementById('editTitle').value = title;
  document.getElementById('editDesc').value = desc;
  document.getElementById('editTag').value = tag;
  document.getElementById('editYoutube').value = youtube;
  const preview = document.getElementById('currentImagePreview');
  if (thumb) {
    preview.innerHTML = 'الصورة الحالية: <img src="../uploads/' + thumb + '" width="60" height="60" style="object-fit:cover;border-radius:6px;vertical-align:middle;">';
  } else {
    preview.innerHTML = '';
  }
  document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() {
  document.getElementById('editModal').style.display = 'none';
}
window.onclick = function(event) {
  const modal = document.getElementById('editModal');
  if (event.target == modal) {
    modal.style.display = 'none';
  }
}
</script>

</body>
</html>
