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
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
// Lấy danh sách các assignment của instructor
$assignments = $db->prepare("SELECT a.id, a.title FROM assignments a WHERE a.created_by = ? ORDER BY a.title ASC");
$assignments->execute([$user_id]);
$assignmentList = $assignments->fetchAll(PDO::FETCH_ASSOC);
// Lấy submissions chưa chấm điểm
$submissions = $db->prepare("SELECT s.id AS submission_id, s.assignment_id, a.title AS assignment_title, s.student_id, u.first_name, u.last_name, s.file_url, s.submitted_at, s.grade, s.feedback FROM submissions s JOIN assignments a ON s.assignment_id = a.id JOIN users u ON s.student_id = u.id WHERE a.created_by = ? ORDER BY s.submitted_at DESC");
$submissions->execute([$user_id]);
$submissionList = $submissions->fetchAll(PDO::FETCH_ASSOC);
// Xử lý chấm điểm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id'])) {
    $submission_id = intval($_POST['submission_id']);
    $grade = floatval($_POST['grade']);
    $feedback = trim($_POST['feedback']);
    // Lấy thông tin submission
    $stmt = $db->prepare("SELECT assignment_id, student_id FROM submissions WHERE id = ?");
    $stmt->execute([$submission_id]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($submission) {
        // Lưu vào bảng grading
        $insert = $db->prepare("INSERT INTO grading (assignment_id, student_id, grade, feedback, graded_by) VALUES (?, ?, ?, ?, ?)");
        $insert->execute([
            $submission['assignment_id'],
            $submission['student_id'],
            $grade,
            $feedback,
            $user_id
        ]);
        // Cập nhật điểm và nhận xét vào bảng submissions
        $update = $db->prepare("UPDATE submissions SET grade = ?, feedback = ? WHERE id = ?");
        $update->execute([$grade, $feedback, $submission_id]);
        $success = 'Grading submitted successfully!';
        // Refresh submissions
        header('Location: grading.php');
        exit();
    } else {
        $error = 'Submission not found.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading - Instructor</title>
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
                    <h1 class="h2"><i class="fas fa-marker"></i> Grading</h1>
                </div>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger">Error: <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-tasks"></i> Submissions to Grade
                    </div>
                    <div class="card-body">
                        <?php if (count($submissionList) === 0): ?>
                            <div class="alert alert-info">No submissions to grade.</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Assignment</th>
                                        <th>Student</th>
                                        <th>File</th>
                                        <th>Submitted At</th>
                                        <th>Grade</th>
                                        <th>Feedback</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($submissionList as $sub): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($sub['assignment_title']) ?></td>
                                        <td><?= htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']) ?></td>
                                        <td><a href="<?= htmlspecialchars($sub['file_url']) ?>" target="_blank">Download</a></td>
                                        <td><?= htmlspecialchars($sub['submitted_at']) ?></td>
                                        <td><?= is_null($sub['grade']) ? '-' : htmlspecialchars($sub['grade']) ?></td>
                                        <td><?= is_null($sub['feedback']) ? '-' : htmlspecialchars($sub['feedback']) ?></td>
                                        <td>
                                            <?php if (is_null($sub['grade'])): ?>
                                            <form method="post" class="d-flex flex-column gap-2">
                                                <input type="hidden" name="submission_id" value="<?= $sub['submission_id'] ?>">
                                                <input type="number" name="grade" class="form-control mb-1" placeholder="Grade" min="0" max="100" step="0.01" required>
                                                <textarea name="feedback" class="form-control mb-1" placeholder="Feedback"></textarea>
                                                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Submit</button>
                                            </form>
                                            <?php else: ?>
                                                <span class="text-success"><i class="fas fa-check-circle"></i> Graded</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
