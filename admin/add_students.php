<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';
$database = new Database();
$conn = $database->getConnection();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $password = $_POST['password'];
    $params = [
        ':username' => $username,
        ':email' => $email,
        ':last_name' => $last_name,
        ':first_name' => $first_name,
        ':role' => 'student',
        ':password' => password_hash($password, PASSWORD_BCRYPT)
    ];
    $sql = "INSERT INTO users (username, email, last_name, first_name, role, password) VALUES (:username, :email, :last_name, :first_name, :role, :password)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute($params)) {
        header('Location: students.php');
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Add student failed!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - University LMS</title>
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
                <h1 class="h2"><i class="fas fa-user-plus"></i> Add Student</h1>
            </div>
            <div class="card mb-4">
                <div class="card-header bg-success text-white"><i class="fas fa-user-plus"></i> Add Student</div>
                <div class="card-body">
                    <?php if ($message) echo $message; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">Add</button>
                            <a href="students.php" class="btn btn-secondary">Cancel</a>
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
