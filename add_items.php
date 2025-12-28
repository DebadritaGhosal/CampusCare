<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die('Database connection not initialized. Check includes/db_connect.php');
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $price = isset($_POST['price']) ? filter_var($_POST['price'], FILTER_VALIDATE_FLOAT) : null;
    $category = trim($_POST['category'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $condition = trim($_POST['condition'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Handle image upload
    $image_path = 'default_item.png';
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadsDir = __DIR__ . '/uploads';
        if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'item_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $target = $uploadsDir . '/' . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_path = 'uploads/' . $filename;
        }
    }

    if ($title === '' || $price === false || $price === null) {
        $message = 'Please provide a valid title and price.';
        $message_type = 'error';
    } else {
        try {
            // INSERT into marketplace (ensure DB has location, `condition`, category columns — see SQL below)
            $stmt = $pdo->prepare(
                "INSERT INTO marketplace (user_id, title, description, price, image, location, `condition`, category, status, posted_date)
                 VALUES (:user_id, :title, :description, :price, :image, :location, :condition, :category, 'active', NOW())"
            );
            $stmt->execute([
                ':user_id' => (int)$_SESSION['user_id'],
                ':title' => $title,
                ':description' => $description,
                ':price' => $price,
                ':image' => $image_path,
                ':location' => $location,
                ':condition' => $condition,
                ':category' => $category
            ]);

            $message = 'Item added successfully.';
            $message_type = 'success';
            header('Location: student_dashboard.php?tab=tab1');
            exit;
        } catch (PDOException $e) {
            error_log('Add item error: ' . $e->getMessage());
            // show DB error in dev; remove details in production
            $message = 'Database error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add Item | CampusCare</title>
<link rel="stylesheet" href="style.css" />
<style>
        /* Styles for add_items.php — matches marketplace look */
    
    *{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI',system-ui,-apple-system,Segoe,-apple-system,Roboto,Helvetica,Arial;}
    body{background:#f5f5f5;color:#222;line-height:1.5;padding:28px;}
    
    .container{
      max-width:920px;
      margin:32px auto;
      padding:22px;
      background:#fff;
      border-radius:12px;
      box-shadow:0 8px 24px rgba(3,7,18,0.06);
    }

body.dark .container {
  background-color: #2c2c2c;
}
label{
    font-weight:800;
}
body.dark .container label {
  color: #fff;
  display: block;
  text-align: left;
  margin-bottom: 5px;
  font-size: 14px;
}
body.dark .container input,
body.dark .container textarea,
body.dark .container select {
  background-color: #444;
  color: #fff;
  border: 1px solid #666;
}

    
    
    /* Header */
    .container h1 {
  font-size: 26px;
  margin-bottom: 10px;
}
    /* Form layout */
    form{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;align-items:start;}
    form label{display:flex;flex-direction:column;font-size:13px;color:#334;padding:6px;}
    form input[type="text"],
    form input[type="number"],
    form input[type="file"],
    form textarea,
    form select{
      margin-top:8px;
      padding:10px 12px;
      border:1px solid #e6e6e6;
      border-radius:10px;
      background:#fff;
      font-size:14px;
      color:#111;
      transition:box-shadow .15s,transform .12s;
    }
    form textarea{min-height:120px;resize:vertical;}
    form input:focus, form textarea:focus, form select:focus{
      outline:none;
      box-shadow:0 6px 18px rgba(3,7,18,0.06);
      transform:translateY(-1px);
      border-color:#c7f0df;
    }
    
    /* Full-width row items */
    .full-row{grid-column:1 / -1;}
    
    /* Submit */
    button[type="submit"]{
      grid-column:1 / -1;
      justify-self:start;
      background:#059668;
      color:#fff;
      border:none;
      padding:10px 16px;
      border-radius:10px;
      font-weight:600;
      cursor:pointer;
      box-shadow:0 8px 18px rgba(5,150,104,0.14);
      transition:transform .12s,box-shadow .12s;
    }
    button[type="submit"]:hover{transform:translateY(-3px);box-shadow:0 12px 28px rgba(5,150,104,0.16);}
    
    /* Message */
    #message{margin-top:14px;font-size:14px;}
    #message.success{color:#065f46;}
    #message.error{color:#b91c1c;}
    
    /* Responsive */
    @media (max-width:720px){
      form{grid-template-columns:1fr;}
      .container{padding:16px;margin:18px;}
    }
    #message { margin-top: 12px; font-size: 14px; }
    #message.success { color: green; }
    #message.error { color: red; }

    /* place heading fixed at top center and add space for it */
.page-top-heading {
  position: fixed;
  top: 12px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 120;
  font-size: 26px;
  text-align: center;
  background: transparent;
  padding: 6px 12px;
  pointer-events: none;
}

/* push container content down so it doesn't sit behind the fixed heading */
.container {
  padding-top: 76px; /* adjust if heading height changes */
}

/* responsive tweak */
@media (max-width:720px) {
  .page-top-heading { font-size: 20px; top:10px; }
  .container { padding-top: 64px; }
}
</style>
</head>
<body>
  <div class="theme-toggle">
    <button id="toggleTheme">☾</button>
  </div>

  <!-- move or replace the existing H1 with this -->
  <h1 class="page-top-heading">Add Listing</h1>

  <div class="container" style="max-width:900px;margin:36px auto;padding:20px;">
    <form method="post" enctype="multipart/form-data" style="display:grid;gap:10px;">
        <label>Title<input type="text" name="title" required></label>
        <label>Price (numeric)<input type="number" step="0.01" name="price" required></label>
        <label>Category
            <select name="category">
                <option value="">Select</option>
                <option value="books">Books</option>
                <option value="electronics">Electronics</option>
                <option value="furniture">Furniture</option>
                <option value="lab">Lab Requirements</option>
            </select>
        </label>
        <label>Location<input type="text" name="location"></label>
        <label>Condition<input type="text" name="condition" placeholder="e.g. Good, Like new"></label>
        <label class="full-row">Description<textarea name="description"></textarea></label>
        <label>Image<input type="file" name="image" accept="image/*"></label>
        <button type="submit">Add Item</button>
    </form>

    <?php if ($message): ?>
        <div id="message" class="<?php echo htmlspecialchars($message_type); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
  </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const toggleBtn = document.getElementById('toggleTheme');
  if (!toggleBtn) return;

  // Restore saved theme
  const saved = localStorage.getItem('campuscare_theme');
  if (saved === 'dark') {
    document.body.classList.add('dark');
    toggleBtn.textContent = '☀︎';
  } else {
    document.body.classList.remove('dark');
    toggleBtn.textContent = '☾';
  }

  // Toggle handler (single declaration)
  toggleBtn.addEventListener('click', () => {
    const isDark = document.body.classList.toggle('dark');
    toggleBtn.textContent = isDark ? '☀︎' : '☾';
    localStorage.setItem('campuscare_theme', isDark ? 'dark' : 'light');
  });
});
</script>
</body>
</html>