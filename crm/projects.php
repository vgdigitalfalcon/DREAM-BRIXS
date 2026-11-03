<?php
$pageTitle = 'Projects';
require_once 'includes/header.php';

$search = $_GET['search'] ?? '';
$cat = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "SELECT p.*, c.name as category_name FROM projects p JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND p.title LIKE ?"; $params[] = "%$search%"; }
if ($cat) { $sql .= " AND p.category_id = ?"; $params[] = $cat; }
if ($status) { $sql .= " AND p.status = ?"; $params[] = $status; }
$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">All Projects</h5>
    <a href="project-form.php" class="btn btn-primary-custom"><i class="fas fa-plus me-1"></i> Add Project</a>
</div>

<form class="row g-2 mb-4">
    <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Search title..." value="<?= htmlspecialchars($search) ?>"></div>
    <div class="col-md-3">
        <select name="category" class="form-select">
            <option value="">All Categories</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $cat==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="status" class="form-select">
            <option value="">All Status</option>
            <option value="Completed" <?= $status=='Completed'?'selected':'' ?>>Completed</option>
            <option value="Ongoing" <?= $status=='Ongoing'?'selected':'' ?>>Ongoing</option>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filter</button></div>
</form>

<div class="table-responsive">
    <table class="table table-hover bg-white rounded">
        <thead class="table-light">
            <tr><th>Title</th><th>Category</th><th>Location</th><th>Year</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['title']) ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($p['category_name']) ?></span></td>
                <td><?= htmlspecialchars($p['location']) ?></td>
                <td><?= $p['year'] ?></td>
                <td><span class="badge bg-<?= $p['status']=='Completed'?'success':'warning' ?>"><?= $p['status'] ?></span></td>
                <td>
                    <a href="project-form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    <a href="project-delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this project?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($projects)): ?><tr><td colspan="6" class="text-center text-muted py-4">No projects found</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once 'includes/footer.php'; ?>
