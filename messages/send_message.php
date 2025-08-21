<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $receiver_id = $_POST['receiver_id'];
  $course_id = $_POST['course_id'];
  $content = trim($_POST['content']);

  if (!empty($content)) {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, course_id, content) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $user_id, $receiver_id, $course_id, $content);

    if ($stmt->execute()) {
      $message = "✅ Message sent!";
    } else {
      $message = "❌ Failed to send message.";
    }
  } else {
    $message = "❌ Message content cannot be empty.";
  }
}

// Get list of users (except self)
$users = $conn->query("SELECT user_id, full_name FROM users WHERE user_id != $user_id");

// Get list of courses the user is enrolled in or instructs
$courses = $conn->query("
  SELECT DISTINCT c.course_id, c.title 
  FROM courses c
  LEFT JOIN enrollments e ON c.course_id = e.course_id
  WHERE e.user_id = $user_id OR c.instructor_id = $user_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Send Message</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-xl font-bold mb-4">📩 Send Message / Discuss</h1>

    <?php if ($message): ?>
      <p class="mb-4 text-green-600"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <label class="block text-sm">To:</label>
      <select name="receiver_id" required class="w-full px-3 py-2 border rounded">
        <option value="">-- Select a user --</option>
        <?php while ($row = $users->fetch_assoc()): ?>
          <option value="<?= $row['user_id'] ?>"><?= htmlspecialchars($row['full_name']) ?></option>
        <?php endwhile; ?>
      </select>

      <label class="block text-sm">Course:</label>
      <select name="course_id" required class="w-full px-3 py-2 border rounded">
        <option value="">-- Select a course --</option>
        <?php while ($course = $courses->fetch_assoc()): ?>
          <option value="<?= $course['course_id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
        <?php endwhile; ?>
      </select>

      <textarea name="content" rows="4" placeholder="Write your message..." required class="w-full px-3 py-2 border rounded"></textarea>

      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Send</button>
    </form>
  </div>
</body>
</html>