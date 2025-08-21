<?php
require_once '../config/database.php';
requireLogin();

if (!hasRole('admin')) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Lấy danh sách log hệ thống
$query = "SELECT l.*, u.first_name, u.last_name FROM system_log l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 100";
$stmt = $db->prepare($query);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - University LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/admin_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-file-alt"></i> System Logs
                    </h1>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h4><i class="fas fa-server"></i> System Log Viewer</h4>
                                <p class="mb-0">View system logs, including user activities, errors, and warnings.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-list"></i> Log Entries
                                </h6>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Level</th>
                                            <th scope="col">Message</th>
                                            <th scope="col">Data</th>
                                            <th scope="col">User</th>
                                            <th scope="col">Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td><?php echo $log['id']; ?></td>
                                                <td>
                                                    <?php
                                                        $level = $log['log_level'];
                                                        $icon = [
                                                            'info' => 'fa-info-circle text-info',
                                                            'warning' => 'fa-exclamation-triangle text-warning',
                                                            'error' => 'fa-times-circle text-danger',
                                                            'critical' => 'fa-bomb text-danger fw-bold'
                                                        ];
                                                    ?>
                                                    <i class="fas <?php echo $icon[$level]; ?>"></i>
                                                    <span class="text-capitalize fw-bold <?php echo 'text-' . ($level == 'info' ? 'info' : ($level == 'warning' ? 'warning' : 'danger')); ?>">
                                                        <?php echo ucfirst($level); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($log['log_message']); ?></td>
                                                <td>
                                                    <?php if (!empty($log['log_data'])): ?>
                                                        <span class="text-muted small"> <?php echo htmlspecialchars($log['log_data']); ?> </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo isset($log['first_name']) ? htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) : '<span class="text-muted">System</span>'; ?>
                                                </td>
                                                <td><?php echo formatDate($log['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php if (count($logs) == 0): ?>
                                    <div class="alert alert-info">No system logs found.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
