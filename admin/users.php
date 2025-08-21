<?php
// admin/users.php
session_start();
// Kiểm tra đăng nhập và quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';

// Kết nối database sử dụng class Database
$database = new Database();
$conn = $database->getConnection();

// Lấy danh sách người dùng
// Sử dụng PDO, gán $users cho layout
$stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$showAddForm = isset($_GET['add_user']);
$editUser = null;
if (isset($_GET['edit_id'])) {
    foreach ($users as $u) {
        if ($u['id'] == $_GET['edit_id']) {
            $editUser = $u;
            break;
        }
    }
}

// Xử lý cập nhật người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $params = [
        ':id' => $id,
        ':username' => $username,
        ':email' => $email,
        ':last_name' => $last_name,
        ':first_name' => $first_name,
        ':role' => $role
    ];
    $sql = "UPDATE users SET username = :username, email = :email, last_name = :last_name, first_name = :first_name, role = :role";
    if ($password) {
        $sql .= ", password = :password";
        $params[':password'] = password_hash($password, PASSWORD_BCRYPT);
    }
    $sql .= " WHERE id = :id";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute($params)) {
        header('Location: users.php');
        exit();
    } else {
        echo "<div class='alert alert-danger'>Update failed!</div>";
    }
}

// Xử lý thêm người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $params = [
        ':username' => $username,
        ':email' => $email,
        ':last_name' => $last_name,
        ':first_name' => $first_name,
        ':role' => $role,
        ':password' => password_hash($password, PASSWORD_BCRYPT)
    ];
    $sql = "INSERT INTO users (username, email, last_name, first_name, role, password) VALUES (:username, :email, :last_name, :first_name, :role, :password)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute($params)) {
        header('Location: users.php');
        exit();
    } else {
        echo "<div class='alert alert-danger'>Add user failed!</div>";
    }
}

// Xử lý xoá người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: users.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - University LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <style>
        .card-header.bg-success { background-color: #178a56 !important; }
        .card-header.bg-primary { background-color: #0066f5 !important; }
        .btn-success { background-color: #178a56 !important; border: none; }
        .btn-success:hover { background-color: #146c43 !important; }
        .btn-primary { background-color: #0066f5 !important; border: none; }
        .btn-primary:hover { background-color: #0052c2 !important; }
        .table th, .table td { vertical-align: middle !important; }
    </style>
</head>
<body class="bg-gray-100">
<?php include '../includes/admin_navbar.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-users"></i> User Management</h1>
                <a href="add_user.php" class="btn btn-success"><i class="fas fa-user-plus"></i> Add User</a>
            </div>
            <?php if ($editUser): ?>
            <div class="card mb-4">
                <div class="card-header bg-warning text-white"><i class="fas fa-edit"></i> Edit User</div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Username</label><input type="text" name="username" class="form-control" value="<?= htmlspecialchars($editUser['username']) ?>" required></div>
                            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editUser['email']) ?>" required></div>
                            <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($editUser['last_name']) ?>" required></div>
                            <div class="col-md-6"><label class="form-label">First Name</label><input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($editUser['first_name']) ?>" required></div>
                            <div class="col-md-6"><label class="form-label">New Password (if changing)</label><input type="password" name="password" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Role</label>
                                <select name="role" class="form-select">
                                    <option value="student" <?= $editUser['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                    <option value="instructor" <?= $editUser['role'] === 'instructor' ? 'selected' : '' ?>>Instructor</option>
                                    <option value="admin" <?= $editUser['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" name="edit_user" class="btn btn-primary">Save</button>
                            <a href="users.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-users"></i> User List
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Username</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Full Name</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Created At</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['last_name'] . ' ' . $user['first_name']) ?></td>
                                    <td>
                                        <?php
                                        $roleColor = 'bg-secondary';
                                        if ($user['role'] === 'instructor') $roleColor = 'bg-success';
                                        elseif ($user['role'] === 'student') $roleColor = 'bg-primary';
                                        ?>
                                        <span class="badge <?= $roleColor ?>"><?= ucfirst($user['role']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                                    <td>
                                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-primary me-1"><i class="fas fa-edit"></i> Edit</a>
                                        <form method="post" style="display:inline-block" onsubmit="return confirm('Delete this user?');">
                                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="delete_user" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>