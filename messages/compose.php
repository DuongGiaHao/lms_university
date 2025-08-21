<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Lấy danh sách người nhận (tất cả user trừ bản thân)
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT id, username, first_name, last_name, role FROM users WHERE id != ? ORDER BY role, last_name, first_name');
$stmt->execute([$user_id]);
$recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_id = $_POST['recipient_id'] ?? '';
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if (!$recipient_id || !$content) {
        $message = '<span class="text-red-600">Please fill in all fields.</span>';
    } else {
        $stmt = $conn->prepare('INSERT INTO messages (sender_id, recipient_id, subject, content) VALUES (?, ?, ?, ?)');
        if ($stmt->execute([$user_id, $recipient_id, $subject, $content])) {
            $message = '<span class="text-green-600">Message sent successfully!</span>';
        } else {
            $message = '<span class="text-red-600">Error sending message.</span>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Create New Message</title>
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
                    <h1 class="h2"><i class="fas fa-edit"></i> Create New Message</h1>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-info mb-3"><?= $message ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="recipient_id" class="form-label">Recipient</label>
                                        <select name="recipient_id" id="recipient_id" required class="form-select">
                                            <option value="">-- Select Recipient --</option>
                                            <?php foreach ($recipients as $r): ?>
                                                <option value="<?= $r['id'] ?>">[<?= htmlspecialchars($r['role']) ?>] <?= htmlspecialchars($r['last_name'] . ' ' . $r['first_name']) ?> (<?= htmlspecialchars($r['username']) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="subject" class="form-label">Subject</label>
                                        <input type="text" name="subject" id="subject" class="form-control" maxlength="200">
                                    </div>
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Content</label>
                                        <textarea name="content" id="content" required class="form-control" rows="5"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane"></i> Send Message</button>
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
