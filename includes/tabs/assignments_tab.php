<?php
require_once '../config/database.php';
session_start();
$course_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role'] ?? '';

$sql = "SELECT * FROM assignments WHERE course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="space-y-6">
  <?php while ($row = $result->fetch_assoc()): ?>
    <div class="p-4 border rounded bg-white">
      <h3 class="text-lg font-semibold"><?= htmlspecialchars($row['title']) ?></h3>
      <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
      <p class="text-sm text-gray-500">Due: <?= $row['due_date'] ?></p>

      <?php if ($role == 'student'): ?>
        <form action="../student/submit_assignment.php" method="POST" enctype="multipart/form-data" class="mt-2">
          <input type="hidden" name="assignment_id" value="<?= $row['id'] ?>">
          <input type="file" name="submission_file" required class="border px-3 py-2 rounded mb-2 w-full">
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Submit</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endwhile; ?>
</div>