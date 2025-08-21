<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';
$database = new Database();
$conn = $database->getConnection();
$instructor_id = $_GET['id'] ?? null;
if (!$instructor_id || !is_numeric($instructor_id)) {
    header('Location: instructors.php');
    exit();
}
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'instructor'");
$stmt->execute([$instructor_id]);
$instructor = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$instructor) {
    header('Location: instructors.php');
    exit();
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $password = $_POST['password'];
    $params = [
        ':id' => $instructor_id,
        ':username' => $username,
        ':email' => $email,
        ':last_name' => $last_name,
        ':first_name' => $first_name
    ];
    $sql = "UPDATE users SET username = :username, email = :email, last_name = :last_name, first_name = :first_name";
    if ($password) {
        $sql .= ", password = :password";
        $params[':password'] = password_hash($password, PASSWORD_BCRYPT);
    }
    $sql .= " WHERE id = :id AND role = 'instructor'";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute($params)) {
        header('Location: instructors.php');
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Update failed!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Instructor - University LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<?php include '../includes/admin_navbar.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-edit"></i> Edit Instructor</h1>
            </div>
            <div class="card mb-4">
                <div class="card-header bg-warning text-white"><i class="fas fa-edit"></i> Edit Instructor</div>
                <div class="card-body">
                    <?php if ($message) echo $message; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($instructor['username']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($instructor['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($instructor['last_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($instructor['first_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password (if changing)</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="instructors.php" class="btn btn-secondary">Cancel</a>
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
