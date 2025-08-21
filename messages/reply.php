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

// Lấy thông tin tin nhắn gốc
$stmt = $conn->prepare('SELECT m.*, s.username AS sender_username, s.first_name AS sender_first, s.last_name AS sender_last, s.role AS sender_role, r.username AS recipient_username, r.first_name AS recipient_first, r.last_name AS recipient_last, r.role AS recipient_role FROM messages m JOIN users s ON m.sender_id = s.id JOIN users r ON m.recipient_id = r.id WHERE m.id = ? AND (m.sender_id = ? OR m.recipient_id = ?)');
$stmt->execute([$message_id, $user_id, $user_id]);
$msg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$msg) {
    die('Message not found or access denied.');
}

// Xác định người nhận cho reply (người gửi của tin nhắn gốc, trừ bản thân)
$reply_to_id = ($msg['sender_id'] == $user_id) ? $msg['recipient_id'] : $msg['sender_id'];
$reply_to_name = ($msg['sender_id'] == $user_id) ? ($msg['recipient_last'] . ' ' . $msg['recipient_first']) : ($msg['sender_last'] . ' ' . $msg['sender_first']);
$reply_to_username = ($msg['sender_id'] == $user_id) ? $msg['recipient_username'] : $msg['sender_username'];
$reply_to_role = ($msg['sender_id'] == $user_id) ? $msg['recipient_role'] : $msg['sender_role'];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if (!$content) {
        $message = '<span class="text-red-600">Please enter a reply message.</span>';
    } else {
        $reply_subject = $subject ? $subject : (strpos($msg['subject'], 'Re:') === 0 ? $msg['subject'] : 'Re: ' . $msg['subject']);
        $stmt = $conn->prepare('INSERT INTO messages (sender_id, recipient_id, subject, content) VALUES (?, ?, ?, ?)');
        if ($stmt->execute([$user_id, $reply_to_id, $reply_subject, $content])) {
            $message = '<span class="text-green-600">Reply sent successfully!</span>';
        } else {
            $message = '<span class="text-red-600">Error sending reply.</span>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Reply Message</title>
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
                    <h1 class="h2"><i class="fas fa-reply"></i> Reply Message</h1>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="mb-3 text-secondary">
                                    <b>To:</b> [<?= htmlspecialchars($reply_to_role) ?>] <?= htmlspecialchars($reply_to_name) ?> (<?= htmlspecialchars($reply_to_username) ?>)
                                </div>
                                <?php if ($message): ?>
                                    <div class="alert alert-info mb-3"><?= $message ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="subject" class="form-label">Title</label>
                                        <input type="text" name="subject" id="subject" class="form-control" maxlength="200" value="<?= htmlspecialchars(strpos($msg['subject'], 'Re:') === 0 ? $msg['subject'] : 'Re: ' . $msg['subject']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Reply Content</label>
                                        <textarea name="content" id="content" required class="form-control" rows="5"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane"></i> Send Reply</button>
                                </form>
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
