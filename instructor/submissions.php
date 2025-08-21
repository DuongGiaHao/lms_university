<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
  header("Location: ../auth/login.php");
  exit();
}

$instructor_id = $_SESSION['user_id'];
$message = '';

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $submission_id = $_POST['submission_id'];
  $grade = $_POST['grade'];

  $stmt = $conn->prepare("UPDATE submissions SET grade = ? WHERE submission_id = ?");
  $stmt->bind_param("di", $grade, $submission_id);
  $stmt->execute();
  $message = "✅ Grade updated!";
}

// Fetch submissions for instructor's courses
$sql = "
  SELECT s.submission_id, s.file_url, s.submitted_at, s.grade,
         u.full_name AS student_name,
         a.title AS assignment_title,
         c.title AS course_title
  FROM submissions s
  JOIN users u ON s.student_id = u.user_id
  JOIN assignments a ON s.assignment_id = a.assignment_id
  JOIN courses c ON a.course_id = c.course_id
  WHERE c.instructor_id = ?
  ORDER BY s.submitted_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Submissions</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
  <div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">📤 Student Submissions</h1>

    <?php if ($message): ?>
      <p class="mb-4 text-green-600"><?= $message ?></p>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
      <div class="space-y-4">
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="bg-white p-4 shadow rounded">
            <h2 class="text-lg font-semibold"><?= htmlspecialchars($row['assignment_title']) ?> – <?= htmlspecialchars($row['course_title']) ?></h2>
            <p class="text-sm text-gray-600">Student: <?= htmlspecialchars($row['student_name']) ?></p>
            <p class="text-sm">Submitted: <?= $row['submitted_at'] ?></p>
            <p class="text-sm"><a href="<?= htmlspecialchars($row['file_url']) ?>" target="_blank" class="text-blue-500 underline">📎 View File</a></p>

            <form method="POST" class="mt-2 flex items-center gap-2">
              <input type="hidden" name="submission_id" value="<?= $row['submission_id'] ?>">
              <input type="number" step="0.1" name="grade" placeholder="Grade" value="<?= $row['grade'] ?>" class="px-2 py-1 border rounded w-24">
              <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded">Save Grade</button>
            </form>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <p class="text-gray-600">No submissions found.</p>
    <?php endif; ?>
  </div>
</body>
</html>