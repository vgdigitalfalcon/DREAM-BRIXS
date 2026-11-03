<?php
$pageTitle = 'Dashboard';
require_once 'includes/header.php';

// Stats
$total = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$completed = $pdo->query("SELECT COUNT(*) FROM projects WHERE status='Completed'")->fetchColumn();
$ongoing = $pdo->query("SELECT COUNT(*) FROM projects WHERE status='Ongoing'")->fetchColumn();
$featured = $pdo->query("SELECT COUNT(*) FROM projects WHERE is_featured=1")->fetchColumn();

// Month-by-month (by year of project)
$monthly = $pdo->query("
    SELECT year, MONTH(created_at) as m, COUNT(*) as cnt 
    FROM projects 
    GROUP BY year, MONTH(created_at) 
    ORDER BY year DESC, m DESC
")->fetchAll();
?>
<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="card card-stat p-3"><h3 class="mb-0"><?= $total ?></h3><small class="text-muted">Total Projects</small></div></div>
    <div class="col-md-3"><div class="card card-stat p-3"><h3 class="mb-0 text-success"><?= $completed ?></h3><small class="text-muted">Completed</small></div></div>
    <div class="col-md-3"><div class="card card-stat p-3"><h3 class="mb-0 text-warning"><?= $ongoing ?></h3><small class="text-muted">Ongoing</small></div></div>
    <div class="col-md-3"><div class="card card-stat p-3"><h3 class="mb-0"><?= $featured ?></h3><small class="text-muted">Featured</small></div></div>
</div>

<div class="card">
    <div class="card-header bg-white fw-bold">Month-by-Month Overview</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Year</th><th>Month</th><th>Projects Added</th></tr></thead>
                <tbody>
                    <?php foreach ($monthly as $row): ?>
                    <tr>
                        <td><?= $row['year'] ?></td>
                        <td><?= date('F', mktime(0,0,0,$row['m'],1)) ?></td>
                        <td><?= $row['cnt'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($monthly)): ?><tr><td colspan="3" class="text-center text-muted">No projects yet</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
