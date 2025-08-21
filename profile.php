<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();
// Lấy thông tin user
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// Lấy log gần nhất của user
$log_stmt = $db->prepare("SELECT * FROM system_log WHERE user_id = :id ORDER BY created_at DESC LIMIT 10");
$log_stmt->execute(['id' => $user_id]);
$logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_profile'])) {
    $new_email = trim($_POST['email']);
    $new_first_name = trim($_POST['first_name']);
    $new_last_name = trim($_POST['last_name']);
    $new_password = $_POST['password'];
    $params = [
        ':email' => $new_email,
        ':first_name' => $new_first_name,
        ':last_name' => $new_last_name,
        ':id' => $user_id
    ];
    $sql = "UPDATE users SET email = :email, first_name = :first_name, last_name = :last_name";
    if ($new_password) {
        $sql .= ", password = :password";
        $params[':password'] = password_hash($new_password, PASSWORD_BCRYPT);
    }
    $sql .= " WHERE id = :id";
    $edit_stmt = $db->prepare($sql);
    if ($edit_stmt->execute($params)) {
        $success_msg = "<div class='alert alert-success'>Update successful!</div>";
        // Refresh user info
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $success_msg = "<div class='alert alert-danger'>Update failed!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body d-flex align-items-center">
                        <img src="<?= $user['profile_image'] ? htmlspecialchars($user['profile_image']) : 'assets/img/default_avatar.png' ?>" alt="Avatar" class="rounded-circle me-4" style="width:100px;height:100px;object-fit:cover;">
                        <div>
                            <h3 class="mb-0"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                            <p class="mb-1"><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                            <p class="mb-1"><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
                            <p class="mb-1"><strong>Joined:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
                            <p class="mb-1"><strong>Last Updated:</strong> <?= htmlspecialchars($user['updated_at']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_msg)) echo $success_msg; ?>
                        <button id="editBtn" class="btn btn-primary mb-3" type="button" onclick="document.getElementById('editForm').style.display='block'; this.style.display='none';">Edit</button>
                        <form id="editForm" method="post" class="row g-3" style="display:none;">
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">New Password (leave blank to keep current)</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <div class="col-12">
                                <button type="submit" name="edit_profile" class="btn btn-success">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list"></i> Recent Activity
                    </div>
                    <div class="card-body">
                        <?php if (count($logs) === 0): ?>
                            <div class="alert alert-info">No recent activity found.</div>
                        <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($logs as $log): ?>
                                <li class="list-group-item">
                                    <span class="badge bg-<?= $log['log_level'] === 'error' ? 'danger' : ($log['log_level'] === 'warning' ? 'warning' : ($log['log_level'] === 'critical' ? 'dark' : 'info')) ?> me-2"><?= htmlspecialchars($log['log_level']) ?></span>
                                    <?= htmlspecialchars($log['log_message']) ?>
                                    <span class="text-muted float-end"><?= htmlspecialchars($log['created_at']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Ẩn form edit khi load, chỉ hiện khi nhấn nút Edit
    window.onload = function() {
      document.getElementById('editForm').style.display = 'none';
    };
    </script>
</body>
</html>
