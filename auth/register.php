<?php
session_start();
require_once '../config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = trim($_POST['username']);
  $first_name = trim($_POST['first_name']);
  $last_name = trim($_POST['last_name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];
  $role = $_POST['role'];
  $profile_image = isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0 ? $_FILES['profile_image'] : null;

  if ($password !== $confirm_password) {
    $message = "❌ Passwords do not match.";
  } elseif (!$username || !$email || !$first_name || !$last_name || !$role) {
    $message = "❌ Please fill in all required fields.";
  } else {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $database = new Database();
    $db = $database->getConnection();
    $profile_path = null;
    if ($profile_image) {
      $target_dir = '../assets/img/';
      if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
      }
      $file_name = time() . '_' . basename($profile_image['name']);
      $target_file = $target_dir . $file_name;
      if (move_uploaded_file($profile_image['tmp_name'], $target_file)) {
        $profile_path = $target_file;
      }
    }
    try {
      $stmt = $db->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([$username, $email, $hashed, $first_name, $last_name, $role, $profile_path]);
      header('Location: login.php');
      exit();
    } catch (PDOException $e) {
      $message = "❌ Registration failed. Username or email may already be in use.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Create an Account</h2>

    <?php if ($message): ?>
      <p class="mb-4 text-sm text-red-600"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="text" name="username" placeholder="Username" required class="w-full px-3 py-2 border rounded">
      <input type="text" name="first_name" placeholder="First Name" required class="w-full px-3 py-2 border rounded">
      <input type="text" name="last_name" placeholder="Last Name" required class="w-full px-3 py-2 border rounded">
      <input type="email" name="email" placeholder="Email" required class="w-full px-3 py-2 border rounded">
      <input type="password" name="password" placeholder="Password" required class="w-full px-3 py-2 border rounded">
      <input type="password" name="confirm_password" placeholder="Confirm Password" required class="w-full px-3 py-2 border rounded">
      <select name="role" required class="w-full px-3 py-2 border rounded">
        <option value="student">Student</option>
        <option value="instructor">Instructor</option>
      </select>
      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Register</button>
    </form>

    <p class="mt-4 text-sm text-center">Already have an account? <a href="login.php" class="text-blue-500 underline">Login</a></p>
  </div>

<script>
// Optional: visually reset file input label if needed
</script>
</body>
</html>
