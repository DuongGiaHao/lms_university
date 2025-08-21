<?php
require_once '../config/database.php';
require_once '../includes/instructor_sidebar.php';

requireLogin();
if (!hasRole('instructor')) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$error = '';
$success = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $course_id = intval($_POST['course_id']);
    $uploaded_by = $_SESSION['user_id'];
    $file = $_FILES['file'];

    if ($title && $course_id && $file['size'] > 0) {
        $target_dir = '../uploads/materials/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = basename($file['name']);
        $target_file = $target_dir . time() . '_' . $file_name;
        $file_type = $file['type'];
        $file_size = $file['size'];
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $stmt = $db->prepare("INSERT INTO course_materials (course_id, title, description, file_path, file_type, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$course_id, $title, $description, $target_file, $file_type, $file_size, $uploaded_by]);
            $success = 'Material uploaded successfully!';
        } else {
            $error = 'File upload failed.';
        }
    } else {
        $error = 'Please fill all required fields and select a file.';
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $db->prepare("SELECT file_path FROM course_materials WHERE id = ?");
    $stmt->execute([$id]);
    $material = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($material) {
        if (file_exists($material['file_path'])) {
            unlink($material['file_path']);
        }
        $stmt = $db->prepare("DELETE FROM course_materials WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Material deleted.';
    }
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    if ($title) {
        $stmt = $db->prepare("UPDATE course_materials SET title = ?, description = ? WHERE id = ?");
        $stmt->execute([$title, $description, $id]);
        $success = 'Material updated.';
    } else {
        $error = 'Title is required.';
    }
}

// Get instructor's courses
$stmt = $db->prepare("SELECT id, title FROM courses WHERE instructor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get materials
$stmt = $db->prepare("SELECT m.*, c.title AS course_title, u.username AS uploader FROM course_materials m JOIN courses c ON m.course_id = c.id JOIN users u ON m.uploaded_by = u.id WHERE c.instructor_id = ? ORDER BY m.upload_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Materials - Instructor</title>
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-file"></i> Course Materials</h1>
                </div>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-upload"></i> Upload New Material
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="upload">
                            <div class="mb-3">
                                <label for="course_id" class="form-label">Course</label>
                                <select class="form-select" name="course_id" id="course_id" required>
                                    <option value="">Select course</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" id="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="description"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="file" class="form-label">File</label>
                                <input type="file" class="form-control" name="file" id="file" required>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list"></i> Materials List
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Course</th>
                                    <th>File</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Uploader</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($materials as $material): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($material['title']) ?></td>
                                        <td><?= htmlspecialchars($material['description']) ?></td>
                                        <td><?= htmlspecialchars($material['course_title']) ?></td>
                                        <td><a href="<?= $material['file_path'] ?>" target="_blank">Download</a></td>
                                        <td><?= htmlspecialchars($material['file_type']) ?></td>
                                        <td><?= number_format($material['file_size']/1024, 2) ?> KB</td>
                                        <td><?= htmlspecialchars($material['uploader']) ?></td>
                                        <td><?= htmlspecialchars($material['upload_date']) ?></td>
                                        <td>
                                            <!-- Edit button triggers modal -->
                                            <a href="edit_materials.php?id=<?= $material['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <a href="?delete=<?= $material['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this material?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?= $material['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $material['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="post">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="id" value="<?= $material['id'] ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel<?= $material['id'] ?>">Edit Material</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="title" class="form-label">Title</label>
                                                            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($material['title']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="description" class="form-label">Description</label>
                                                            <textarea class="form-control" name="description"><?= htmlspecialchars($material['description']) ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
