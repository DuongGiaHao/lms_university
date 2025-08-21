<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Lấy danh sách category cho dropdown
$categories = [];
$stmt = $conn->prepare('SELECT fc.id, fc.name, c.title AS course_title FROM forum_categories fc JOIN courses c ON fc.course_id = c.id ORDER BY fc.created_at DESC');
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
    $is_locked = isset($_POST['is_locked']) ? 1 : 0;
    $user_id = $_SESSION['user_id'];

    if (!$category_id || !$title) {
        $message = '<span class="text-red-600">Please fill in all fields.</span>';
    } else {
        $stmt = $conn->prepare('INSERT INTO forum_topics (category_id, title, created_by, is_pinned, is_locked) VALUES (?, ?, ?, ?, ?)');
        if ($stmt->execute([$category_id, $title, $user_id, $is_pinned, $is_locked])) {
            $message = '<span class="text-green-600">Topic created successfully!</span>';
        } else {
            $message = '<span class="text-red-600">Error creating topic.</span>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Create New Topic - Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include '../includes/student_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/student_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-comments"></i> Create New Topic</h1>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-info mb-3"><?= $message ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Select Category</label>
                                        <select name="category_id" id="category_id" required class="form-select">
                                            <option value="">-- Select Category --</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>">[<?= htmlspecialchars($cat['course_title']) ?>] <?= htmlspecialchars($cat['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Topic Title</label>
                                        <input type="text" name="title" id="title" required class="form-control" maxlength="200">
                                    </div>
                                    <div class="mb-3 d-flex gap-3">
                                        <div class="form-check">
                                            <input type="checkbox" name="is_pinned" id="is_pinned" class="form-check-input">
                                            <label for="is_pinned" class="form-check-label">Pin Topic</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="is_locked" id="is_locked" class="form-check-input">
                                            <label for="is_locked" class="form-check-label">Lock Topic</label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus"></i> Create Topic</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
