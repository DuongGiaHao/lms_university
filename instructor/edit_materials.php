<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';
$database = new Database();
$conn = $database->getConnection();
$material_id = $_GET['id'] ?? null;
if (!$material_id || !is_numeric($material_id)) {
    header('Location: materials.php');
    exit();
}
$stmt = $conn->prepare("SELECT * FROM course_materials WHERE id = ?");
$stmt->execute([$material_id]);
$material = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$material) {
    header('Location: materials.php');
    exit();
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $course_id = intval($_POST['course_id']);
    $params = [
        ':id' => $material_id,
        ':title' => $title,
        ':description' => $description,
        ':course_id' => $course_id
    ];
    $sql = "UPDATE course_materials SET title = :title, description = :description, course_id = :course_id WHERE id = :id";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute($params)) {
        header('Location: materials.php');
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Update failed!</div>";
    }
}
// Get courses for dropdown
$stmt = $conn->prepare("SELECT id, title FROM courses WHERE instructor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Material - University LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<?php include '../includes/instructor_navbar.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include '../includes/instructor_sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-edit"></i> Edit Material</h1>
            </div>
            <div class="card mb-4">
                <div class="card-header bg-warning text-white"><i class="fas fa-edit"></i> Edit Material</div>
                <div class="card-body">
                    <?php if ($message) echo $message; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($material['title']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" required><?= htmlspecialchars($material['description']) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course</label>
                            <select name="course_id" class="form-select" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>" <?= ($course['id'] == $material['course_id']) ? 'selected' : '' ?>><?= htmlspecialchars($course['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="materials.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
