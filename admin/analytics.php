<?php
require_once '../config/database.php';
requireLogin();

if (!hasRole('admin')) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Lấy danh sách analytics
$query = "SELECT a.*, u.first_name, u.last_name FROM admin_analytics a LEFT JOIN users u ON a.generated_by = u.id ORDER BY a.generated_at DESC LIMIT 100";
$stmt = $db->prepare($query);
$stmt->execute();
$analytics = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Analytics - University LMS</title>
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
                        <i class="fas fa-chart-line"></i> Admin Analytics
                    </h1>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h4><i class="fas fa-chart-bar"></i> Analytics Viewer</h4>
                                <p class="mb-0">View analytics, statistics, and aggregated data for administrators.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-list"></i> Analytics List
                                </h6>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Analytics Type</th>
                                            <th scope="col">Title</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Created By</th>
                                            <th scope="col">Created At</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($analytics as $item): ?>
                                            <tr>
                                                <td><?php echo $item['id']; ?></td>
                                                <td><?php echo htmlspecialchars($item['analytics_type']); ?></td>
                                                <td><?php echo htmlspecialchars($item['analytics_title']); ?></td>
                                                <td><?php echo htmlspecialchars($item['analytics_description']); ?></td>
                                                <td><?php echo isset($item['first_name']) ? htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) : '<span class="text-muted">System</span>'; ?></td>
                                                <td><?php echo formatDate($item['generated_at']); ?></td>
                                                <td>
                                                    <?php
                                                        $status = $item['status'];
                                                        $badge = [
                                                            'pending' => 'bg-warning',
                                                            'completed' => 'bg-success',
                                                            'failed' => 'bg-danger'
                                                        ];
                                                    ?>
                                                    <span class="badge <?php echo $badge[$status]; ?> text-uppercase"> <?php echo ucfirst($status); ?> </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($item['analytics_data'])): ?>
                                                        <button class="btn btn-sm btn-info" type="button" data-bs-toggle="collapse" data-bs-target="#analyticsData<?php echo $item['id']; ?>">Xem</button>
                                                        <div class="collapse mt-2" id="analyticsData<?php echo $item['id']; ?>">
                                                            <pre class="bg-light p-2 border rounded small"><?php echo htmlspecialchars($item['analytics_data']); ?></pre>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">No data available</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php if (count($analytics) == 0): ?>
                                    <div class="alert alert-info">No analytics available.</div>
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
