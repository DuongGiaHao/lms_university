<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  header("Location: ../auth/login.php");
  exit();
}

$student_id = $_SESSION['user_id'];
$assignment_id = $_GET['assignment_id'] ?? null;
$message = '';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (empty($assignment_id) || !is_numeric($assignment_id)) {
    $message = "❌ Invalid assignment.";
  } else if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/assignments/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }
    $fileTmpPath = $_FILES['file_upload']['tmp_name'];
    $fileName = basename($_FILES['file_upload']['name']);
    $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $fileName);
    $destPath = $uploadDir . $fileName;

    if (move_uploaded_file($fileTmpPath, $destPath)) {
      $file_url = $destPath;
      // Check if already submitted
      $check = $db->prepare("SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
      $check->execute([$assignment_id, $student_id]);
      $res = $check->fetchAll(PDO::FETCH_ASSOC);

      if (count($res) > 0) {
        $message = "❗ You have already submitted this assignment.";
      } else {
        $stmt = $db->prepare("INSERT INTO submissions (assignment_id, student_id, file_url) VALUES (?, ?, ?)");
        if ($stmt->execute([$assignment_id, $student_id, $file_url])) {
          $message = "<span class='text-green-600'>✅ Submission successful!</span><br>";
          $message .= "<b>Uploaded file:</b> <a href='" . htmlspecialchars(str_replace('..','',$file_url)) . "' target='_blank'>" . htmlspecialchars(basename($file_url)) . "</a><br>";
          // Show file content (text only)
          $ext = strtolower(pathinfo($file_url, PATHINFO_EXTENSION));
          $text_exts = ['txt','csv','md','log','json','xml','html','css','js','php','py','java','c','cpp','h','sql'];
          if (in_array($ext, $text_exts)) {
            $realPath = realpath($file_url);
            if ($realPath && is_readable($realPath)) {
              $fileContent = file_get_contents($realPath);
              $message .= "<div class='mt-4'><b>File content preview:</b><pre style='max-height:300px;overflow:auto;background:#f3f3f3;padding:10px;border-radius:4px;'>" . htmlspecialchars(mb_substr($fileContent,0,5000)) . "</pre></div>";
            }
          } else {
            $message .= "<div class='mt-4 text-gray-500'><i>File preview not available for this file type.</i></div>";
          }
        } else {
          $message = "❌ Submission failed.";
        }
      }
    } else {
      $message = "❌ File upload failed.";
    }
  } else {
    $message = "❌ No file uploaded or upload error.";
  }
}

// Get assignment info
$stmt = $db->prepare("SELECT title FROM assignments WHERE id = ?");
$stmt->execute([$assignment_id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Submit Assignment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php include '../includes/student_navbar.php'; ?>
  <div class="container-fluid">
    <div class="row justify-content-center">
      <?php include '../includes/student_sidebar.php'; ?>
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 d-flex justify-content-center align-items-center" style="min-height:80vh;">
        <div class="col-md-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h1 class="h4 mb-4"><i class="fas fa-upload"></i> Submit: 
                <?php if ($assignment && isset($assignment['title'])): ?>
                  <?= htmlspecialchars($assignment['title']) ?>
                <?php else: ?>
                  (Assignment not found)
                <?php endif; ?>
              </h1>
              <?php if ($message): ?>
                <div class="alert alert-info mb-4"><?= $message ?></div>
              <?php endif; ?>
              <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                  <label for="file_upload" class="form-label">Upload Assignment File</label>
                  <input type="file" name="file_upload" id="file_upload" required class="form-control">
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane"></i> Submit Assignment</button>
              </form>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>