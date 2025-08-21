<?php
require_once '../config/database.php';
requireLogin();
if (!hasRole('admin')) {
    header('Location: ../auth/login.php');
    exit();
}
$database = new Database();
$conn = $database->getConnection();
// Thêm mới instructor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_instructor'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $password = $_POST['password'];
    $params = [
        ':username' => $username,
        ':email' => $email,
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':password' => password_hash($password, PASSWORD_BCRYPT),
        ':role' => 'instructor'
    ];
    $sql = "INSERT INTO users (username, email, first_name, last_name, password, role) VALUES (:username, :email, :first_name, :last_name, :password, :role)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    header('Location: instructors.php'); exit();
}
// Sửa instructor
$editInstructor = null;
if (isset($_GET['edit_id'])) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'instructor'");
    $stmt->execute([$_GET['edit_id']]);
    $editInstructor = $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_instructor'])) {
    $id = $_POST['id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $password = $_POST['password'];
    $params = [
        ':id' => $id,
        ':username' => $username,
        ':email' => $email,
        ':first_name' => $first_name,
        ':last_name' => $last_name
    ];
    $sql = "UPDATE users SET username = :username, email = :email, first_name = :first_name, last_name = :last_name";
    if ($password) {
        $sql .= ", password = :password";
        $params[':password'] = password_hash($password, PASSWORD_BCRYPT);
    }
    $sql .= " WHERE id = :id AND role = 'instructor'";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    header('Location: instructors.php'); exit();
}
// Xoá instructor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_instructor'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'instructor'");
    $stmt->execute([$id]);
    header('Location: instructors.php'); exit();
}
// Lấy danh sách instructor
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'instructor' ORDER BY created_at DESC");
$stmt->execute();
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Management - University LMS</title>
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
<body>
    <?php include '../includes/admin_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-chalkboard-teacher"></i> Instructor Management</h1>
                    <a href="add_instructor.php" class="btn btn-success"><i class="fas fa-user-plus"></i> Add Instructor</a>
                </div>
                <?php if (isset($_GET['add_instructor'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white"><i class="fas fa-user-plus"></i> Add Instructor</div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label">First Name</label><input type="text" name="first_name" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" name="add_instructor" class="btn btn-success">Add</button>
                                <a href="instructors.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($editInstructor): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white"><i class="fas fa-edit"></i> Edit Instructor</div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="id" value="<?= $editInstructor['id'] ?>">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Username</label><input type="text" name="username" class="form-control" value="<?= htmlspecialchars($editInstructor['username']) ?>" required></div>
                                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editInstructor['email']) ?>" required></div>
                                <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($editInstructor['last_name']) ?>" required></div>
                                <div class="col-md-6"><label class="form-label">First Name</label><input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($editInstructor['first_name']) ?>" required></div>
                                <div class="col-md-6"><label class="form-label">New Password (if changing)</label><input type="password" name="password" class="form-control"></div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" name="edit_instructor" class="btn btn-primary">Save</button>
                                <a href="instructors.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-chalkboard-teacher"></i> Instructor List
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
                                        <th scope="col">Created At</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($instructors && count($instructors) > 0): ?>
                                    <?php foreach($instructors as $row): ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= htmlspecialchars($row['username']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td><?= htmlspecialchars($row['last_name'] . ' ' . $row['first_name']) ?></td>
                                            <td><?= $row['created_at'] ?></td>
                                            <td>
                                                <a href="edit_instructor.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary me-1"><i class="fas fa-edit"></i> Edit</a>
                                                <form method="post" style="display:inline-block" onsubmit="return confirm('Delete this instructor?');">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <button type="submit" name="delete_instructor" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center">No instructors found.</td></tr>
                                <?php endif; ?>
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
