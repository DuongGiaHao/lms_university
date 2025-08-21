<?php
require_once '../config/database.php';
$course_id = $_GET['id'] ?? 0;

$sql = "SELECT * FROM course_materials WHERE course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="space-y-4">
  <?php while ($row = $result->fetch_assoc()): ?>
    <div class="p-4 border rounded bg-white">
      <h3 class="text-lg font-bold"><?= htmlspecialchars($row['title']) ?></h3>
      <p class="text-sm text-gray-600"><?= htmlspecialchars($row['description']) ?></p>
      <a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="text-blue-500 underline mt-2 inline-block">View Material</a>
    </div>
  <?php endwhile; ?>
</div>