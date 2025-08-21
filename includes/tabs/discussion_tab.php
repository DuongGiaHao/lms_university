<?php
require_once '../config/database.php';
session_start();
$course_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $content = trim($_POST['content']);
  if ($content !== '') {
    $stmt = $conn->prepare("INSERT INTO forum_posts (topic_id, user_id, content) VALUES (?, ?, ?)");
    $topic_id = 1; // Assuming a default topic for each course
    $stmt->bind_param("iis", $topic_id, $user_id, $content);
    $stmt->execute();
  }
}

$sql = "SELECT forum_posts.*, users.first_name, users.last_name FROM forum_posts 
        JOIN users ON forum_posts.user_id = users.id
        ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<div class="space-y-4">
  <form method="POST" class="bg-white p-4 rounded border">
    <textarea name="content" class="w-full border rounded p-2 mb-2" placeholder="Enter your comment..." required></textarea>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Post</button>
  </form>

  <?php while ($row = $result->fetch_assoc()): ?>
    <div class="bg-white p-4 rounded border">
      <p class="font-semibold"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></p>
      <p class="text-gray-700"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
      <p class="text-xs text-gray-500"><?= $row['created_at'] ?></p>
    </div>
  <?php endwhile; ?>
</div>