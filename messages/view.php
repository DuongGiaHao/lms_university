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
$message_id = $_GET['id'] ?? null;

if (!$message_id || !is_numeric($message_id)) {
    die('Invalid message ID.');
}

// Lấy thông tin tin nhắn (chỉ cho phép xem nếu là người nhận hoặc người gửi)
$stmt = $conn->prepare('SELECT m.*, s.username AS sender_username, s.first_name AS sender_first, s.last_name AS sender_last, s.role AS sender_role, r.username AS recipient_username, r.first_name AS recipient_first, r.last_name AS recipient_last, r.role AS recipient_role FROM messages m JOIN users s ON m.sender_id = s.id JOIN users r ON m.recipient_id = r.id WHERE m.id = ? AND (m.sender_id = ? OR m.recipient_id = ?)');
$stmt->execute([$message_id, $user_id, $user_id]);
$msg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$msg) {
    die('Message not found or access denied.');
}

// Đánh dấu đã đọc nếu là người nhận và chưa đọc
if ($msg['recipient_id'] == $user_id && !$msg['read_at']) {
    $update = $conn->prepare('UPDATE messages SET read_at = NOW() WHERE id = ?');
    $update->execute([$message_id]);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Message Details</title>
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
                    <h1 class="h2"><i class="fas fa-envelope"></i> Message Details</h1>
                </div>
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="fw-bold text-primary mb-1">Sender: [<?= htmlspecialchars($msg['sender_role']) ?>] <?= htmlspecialchars($msg['sender_last'] . ' ' . $msg['sender_first']) ?> (<?= htmlspecialchars($msg['sender_username']) ?>)</div>
                            <div class="fw-bold text-secondary mb-1">Recipient: [<?= htmlspecialchars($msg['recipient_role']) ?>] <?= htmlspecialchars($msg['recipient_last'] . ' ' . $msg['recipient_first']) ?> (<?= htmlspecialchars($msg['recipient_username']) ?>)</div>
                            <div class="text-muted mb-1">Sent At: <?= $msg['sent_at'] ?></div>
                            <div class="mb-1">Status: <?= $msg['read_at'] ? '<span class="text-success">Read</span>' : '<span class="text-secondary">Unread</span>' ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="h5 mb-2">Title: <?= htmlspecialchars($msg['subject']) ?></div>
                            <div class="bg-light p-3 rounded border text-dark" style="white-space: pre-line;"><?= nl2br(htmlspecialchars($msg['content'])) ?></div>
                        </div>
                        <button onclick="window.location.href='inbox.php'" class="btn btn-light border"><i class="fas fa-arrow-left"></i> Back to Inbox</button>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
