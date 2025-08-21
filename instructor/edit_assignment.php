<?php
session_start();
require_once '../config/database.php';
require_once '../includes/instructor_sidebar.php';

requireLogin();
if (!hasRole('instructor')) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';

// Get assignment info
$stmt = $db->prepare("SELECT * FROM assignments WHERE id = ?");
$stmt->execute([$id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$assignment) {
    echo '<div class="alert alert-danger">Assignment not found.</div>';
    exit();
}

// Get instructor's courses
$stmt = $db->prepare("SELECT id, title FROM courses WHERE instructor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = $_POST['due_date'];
    $max_points = $_POST['max_points'];
    $stmt = $db->prepare("UPDATE assignments SET course_id = ?, title = ?, description = ?, due_date = ?, max_points = ? WHERE id = ?");
    if ($stmt->execute([$course_id, $title, $description, $due_date, $max_points, $id])) {
        $message = '✅ Assignment updated.';
        // Refresh assignment info
        $stmt = $db->prepare("SELECT * FROM assignments WHERE id = ?");
        $stmt->execute([$id]);
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $message = '❌ Update failed.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Assignment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/instructor_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/instructor_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-edit"></i> Edit Assignment</h1>
                </div>
                <?php if ($message): ?>
                    <div class="alert alert-info"> <?= $message ?> </div>
                <?php endif; ?>
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-tasks"></i> Assignment Info</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="course_id" class="form-label">Course</label>
                                <select name="course_id" id="course_id" class="form-select" required>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= $course['id'] ?>" <?= $assignment['course_id'] == $course['id'] ? 'selected' : '' ?>><?= htmlspecialchars($course['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($assignment['title']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3"><?= htmlspecialchars($assignment['description']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="datetime-local" name="due_date" id="due_date" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($assignment['due_date'])) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="max_points" class="form-label">Max Points</label>
                                <input type="number" name="max_points" id="max_points" class="form-control" value="<?= $assignment['max_points'] ?>" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
