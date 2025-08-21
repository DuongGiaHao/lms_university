<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Lấy danh sách tin nhắn đã gửi (chưa bị xóa bởi người gửi)
$stmt = $conn->prepare('SELECT m.*, u.username AS recipient_username, u.first_name, u.last_name, u.role FROM messages m JOIN users u ON m.recipient_id = u.id WHERE m.sender_id = ? AND m.is_deleted_by_sender = 0 ORDER BY m.sent_at DESC');
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Messages Sent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include '../includes/student_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/student_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-paper-plane"></i> Sent Messages</h1>
                    <a href="compose.php" class="btn btn-primary"><i class="fas fa-edit"></i> Compose New Message</a>
                </div>
                <div class="card shadow mb-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Recipient</th>
                                        <th>Subject</th>
                                        <th>Content</th>
                                        <th>Sent At</th>
                                        <th>Read</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($messages && count($messages) > 0): ?>
                                        <?php foreach ($messages as $msg): ?>
                                            <tr>
                                                <td>[<?= htmlspecialchars($msg['role']) ?>] <?= htmlspecialchars($msg['last_name'] . ' ' . $msg['first_name']) ?> (<?= htmlspecialchars($msg['recipient_username']) ?>)</td>
                                                <td><?= htmlspecialchars($msg['subject']) ?></td>
                                                <td class="text-muted small"><?= nl2br(htmlspecialchars(mb_substr($msg['content'],0,100))) ?><?= mb_strlen($msg['content']) > 100 ? '...' : '' ?></td>
                                                <td class="text-secondary small"><?= $msg['sent_at'] ?></td>
                                                <td class="text-center">
                                                    <?php if ($msg['read_at']): ?>
                                                        <span class="badge bg-success">Read</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Unread</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center py-4 text-muted">No sent messages.</td></tr>
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
