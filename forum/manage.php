<?php
require_once '../config/database.php';
require_once '../includes/instructor_sidebar.php';

requireLogin();
if (!hasRole('instructor')) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$error = '';
$success = '';

// Get instructor's courses
$stmt = $db->prepare("SELECT id, title FROM courses WHERE instructor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle create category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_category') {
    $course_id = intval($_POST['course_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    if ($course_id && $name) {
        $stmt = $db->prepare("INSERT INTO forum_categories (course_id, name, description) VALUES (?, ?, ?)");
        $stmt->execute([$course_id, $name, $description]);
        $success = 'Category created.';
    } else {
        $error = 'Please fill all required fields.';
    }
}

// Handle create topic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_topic') {
    $category_id = intval($_POST['category_id']);
    $title = trim($_POST['title']);
    $created_by = $_SESSION['user_id'];
    if ($category_id && $title) {
        $stmt = $db->prepare("INSERT INTO forum_topics (category_id, title, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$category_id, $title, $created_by]);
        $success = 'Topic created.';
    } else {
        $error = 'Please fill all required fields.';
    }
}

// Handle create post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_post') {
    $topic_id = intval($_POST['topic_id']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    if ($topic_id && $content) {
        $stmt = $db->prepare("INSERT INTO forum_posts (topic_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$topic_id, $user_id, $content]);
        $success = 'Post created.';
    } else {
        $error = 'Please fill all required fields.';
    }
}

// Get categories
$stmt = $db->prepare("SELECT fc.*, c.title AS course_title FROM forum_categories fc JOIN courses c ON fc.course_id = c.id WHERE c.instructor_id = ? ORDER BY fc.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get topics
$stmt = $db->prepare("SELECT ft.*, fc.name AS category_name FROM forum_topics ft JOIN forum_categories fc ON ft.category_id = fc.id WHERE fc.course_id IN (SELECT id FROM courses WHERE instructor_id = ?) ORDER BY ft.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get posts
$stmt = $db->prepare("SELECT fp.*, ft.title AS topic_title, u.username FROM forum_posts fp JOIN forum_topics ft ON fp.topic_id = ft.id JOIN users u ON fp.user_id = u.id WHERE ft.category_id IN (SELECT id FROM forum_categories WHERE course_id IN (SELECT id FROM courses WHERE instructor_id = ?)) ORDER BY fp.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/instructor_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/instructor_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-comments"></i> Forum Management</h1>
                </div>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header"><i class="fas fa-folder-plus"></i> Create Category</div>
                            <div class="card-body">
                                <form method="post">
                                    <input type="hidden" name="action" value="create_category">
                                    <div class="mb-3">
                                        <label for="course_id" class="form-label">Course</label>
                                        <select class="form-select" name="course_id" required>
                                            <option value="">Select course</option>
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Category Name</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" name="description"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Create</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header"><i class="fas fa-comment-dots"></i> Create Topic</div>
                            <div class="card-body">
                                <form method="post">
                                    <input type="hidden" name="action" value="create_topic">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category</label>
                                        <select class="form-select" name="category_id" required>
                                            <option value="">Select category</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?> (<?= htmlspecialchars($cat['course_title']) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Topic Title</label>
                                        <input type="text" class="form-control" name="title" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Create</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header"><i class="fas fa-reply"></i> Create Post</div>
                            <div class="card-body">
                                <form method="post">
                                    <input type="hidden" name="action" value="create_post">
                                    <div class="mb-3">
                                        <label for="topic_id" class="form-label">Topic</label>
                                        <select class="form-select" name="topic_id" required>
                                            <option value="">Select topic</option>
                                            <?php foreach ($topics as $topic): ?>
                                                <option value="<?= $topic['id'] ?>"><?= htmlspecialchars($topic['title']) ?> (<?= htmlspecialchars($topic['category_name']) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Content</label>
                                        <textarea class="form-control" name="content" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Post</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header"><i class="fas fa-list"></i> Categories</div>
                            <div class="card-body">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Course</th>
                                            <th>Created At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $cat): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($cat['name']) ?></td>
                                                <td><?= htmlspecialchars($cat['description']) ?></td>
                                                <td><?= htmlspecialchars($cat['course_title']) ?></td>
                                                <td><?= htmlspecialchars($cat['created_at']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header"><i class="fas fa-list"></i> Topics</div>
                            <div class="card-body">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Created By</th>
                                            <th>Created At</th>
                                            <th>Pinned</th>
                                            <th>Locked</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topics as $topic): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($topic['title']) ?></td>
                                                <td><?= htmlspecialchars($topic['category_name']) ?></td>
                                                <td><?= htmlspecialchars($topic['created_by']) ?></td>
                                                <td><?= htmlspecialchars($topic['created_at']) ?></td>
                                                <td><?= $topic['is_pinned'] ? 'Yes' : 'No' ?></td>
                                                <td><?= $topic['is_locked'] ? 'Yes' : 'No' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header"><i class="fas fa-list"></i> Posts</div>
                            <div class="card-body">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Topic</th>
                                            <th>User</th>
                                            <th>Content</th>
                                            <th>Created At</th>
                                            <th>Updated At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($posts as $post): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($post['topic_title']) ?></td>
                                                <td><?= htmlspecialchars($post['username']) ?></td>
                                                <td><?= htmlspecialchars($post['content']) ?></td>
                                                <td><?= htmlspecialchars($post['created_at']) ?></td>
                                                <td><?= htmlspecialchars($post['updated_at']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
