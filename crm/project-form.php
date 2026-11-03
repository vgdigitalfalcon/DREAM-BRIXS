<?php
$pageTitle = isset($_GET['id']) ? 'Edit Project' : 'Add Project';
require_once 'includes/header.php';

$id = $_GET['id'] ?? null;
$project = null;
$images = [];
$features = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch();
    if (!$project) { header('Location: projects.php'); exit; }

    $images = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order, id");
    $images->execute([$id]);
    $images = $images->fetchAll();

    $features = $pdo->prepare("SELECT feature_text FROM project_features WHERE project_id = ? ORDER BY sort_order");
    $features->execute([$id]);
    $features = $features->fetchAll(PDO::FETCH_COLUMN);
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$extra = $project ? json_decode($project['extra_data'] ?? '{}', true) : [];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $slug = generateSlug($title);
    // Ensure unique slug
    $check = $pdo->prepare("SELECT id FROM projects WHERE slug = ? AND id != ?");
    $check->execute([$slug, $id ?? 0]);
    if ($check->fetch()) $slug .= '-' . time();

    $data = [
        'category_id' => $_POST['category_id'],
        'title' => $title,
        'slug' => $slug,
        'location' => $_POST['location'] ?? '',
        'year' => $_POST['year'] ?: null,
        'status' => $_POST['status'] ?? 'Completed',
        'short_description' => $_POST['short_description'] ?? '',
        'detailed_description' => $_POST['detailed_description'] ?? '',
        'built_up_area' => $_POST['built_up_area'] ?? '',
        'plot_size' => $_POST['plot_size'] ?? '',
        'configuration' => $_POST['configuration'] ?? '',
        'duration' => $_POST['duration'] ?? '',
        'materials' => $_POST['materials'] ?? '',
        'testimonial' => $_POST['testimonial'] ?? '',
        'seo_title' => $_POST['seo_title'] ?? $title,
        'seo_description' => $_POST['seo_description'] ?? '',
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'extra_data' => json_encode([
            'bhk' => $_POST['bhk'] ?? '',
            'floors' => $_POST['floors'] ?? '',
            'vastu' => $_POST['vastu'] ?? '',
            'units' => $_POST['units'] ?? '',
            'scope' => $_POST['scope'] ?? '',
            'property_type' => $_POST['property_type'] ?? '',
            'design_scope' => $_POST['design_scope'] ?? ''
        ])
    ];

    if ($id) {
        $sql = "UPDATE projects SET category_id=?, title=?, slug=?, location=?, year=?, status=?, short_description=?, detailed_description=?, built_up_area=?, plot_size=?, configuration=?, duration=?, materials=?, testimonial=?, seo_title=?, seo_description=?, is_featured=?, extra_data=? WHERE id=?";
        $pdo->prepare($sql)->execute(array_merge(array_values($data), [$id]));
        $projectId = $id;
        $message = 'Project updated successfully';
    } else {
        $sql = "INSERT INTO projects (category_id, title, slug, location, year, status, short_description, detailed_description, built_up_area, plot_size, configuration, duration, materials, testimonial, seo_title, seo_description, is_featured, extra_data) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $pdo->prepare($sql)->execute(array_values($data));
        $projectId = $pdo->lastInsertId();
        $message = 'Project created successfully';
    }

    // Features
    $pdo->prepare("DELETE FROM project_features WHERE project_id = ?")->execute([$projectId]);
    if (!empty($_POST['features'])) {
        $featStmt = $pdo->prepare("INSERT INTO project_features (project_id, feature_text, sort_order) VALUES (?, ?, ?)");
        foreach ($_POST['features'] as $i => $f) {
            if (trim($f)) $featStmt->execute([$projectId, trim($f), $i]);
        }
    }

    // New images
    if (!empty($_FILES['new_images']['name'][0])) {
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
        foreach ($_FILES['new_images']['tmp_name'] as $i => $tmp) {
            if ($_FILES['new_images']['error'][$i] === 0) {
                $ext = pathinfo($_FILES['new_images']['name'][$i], PATHINFO_EXTENSION);
                $filename = uniqid('proj_') . '.' . $ext;
                $path = UPLOAD_DIR . $filename;
                if (move_uploaded_file($tmp, $path)) {
                    $pdo->prepare("INSERT INTO project_images (project_id, image_path, sort_order) VALUES (?, ?, ?)")
                        ->execute([$projectId, $filename, $i]);
                }
            }
        }
    }

    // Delete selected images
    if (!empty($_POST['delete_images'])) {
        foreach ($_POST['delete_images'] as $imgId) {
            $img = $pdo->prepare("SELECT image_path FROM project_images WHERE id = ? AND project_id = ?");
            $img->execute([$imgId, $projectId]);
            if ($row = $img->fetch()) {
                @unlink(UPLOAD_DIR . $row['image_path']);
                $pdo->prepare("DELETE FROM project_images WHERE id = ?")->execute([$imgId]);
            }
        }
    }

    header("Location: project-form.php?id=$projectId&saved=1");
    exit;
}
?>
<?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Saved successfully</div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($project['category_id']??'')==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Project Title *</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($project['title']??'') ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($project['location']??'') ?>" placeholder="e.g. Anna Nagar">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" class="form-control" value="<?= $project['year']??'' ?>" min="2000" max="2030">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="Completed" <?= ($project['status']??'')=='Completed'?'selected':'' ?>>Completed</option>
                                <option value="Ongoing" <?= ($project['status']??'')=='Ongoing'?'selected':'' ?>>Ongoing</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <textarea name="short_description" class="form-control" rows="2"><?= htmlspecialchars($project['short_description']??'') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Detailed Description</label>
                        <textarea name="detailed_description" class="form-control" rows="5"><?= htmlspecialchars($project['detailed_description']??'') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Category specific -->
            <div class="card mb-4 house-villa-fields cat-fields" style="display:none;">
                <div class="card-header">House / Villa Details</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3"><label>BHK</label><input type="text" name="bhk" class="form-control" value="<?= htmlspecialchars($extra['bhk']??'') ?>"></div>
                        <div class="col-md-4 mb-3"><label>Floors</label><input type="text" name="floors" class="form-control" value="<?= htmlspecialchars($extra['floors']??'') ?>" placeholder="G+1, G+2..."></div>
                        <div class="col-md-4 mb-3"><label>Vastu Compliant</label>
                            <select name="vastu" class="form-select">
                                <option value="">-</option>
                                <option value="Yes" <?= ($extra['vastu']??'')=='Yes'?'selected':'' ?>>Yes</option>
                                <option value="No" <?= ($extra['vastu']??'')=='No'?'selected':'' ?>>No</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 apt-fields cat-fields" style="display:none;">
                <div class="card-header">Apartment / Residential Details</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3"><label>Number of Units</label><input type="text" name="units" class="form-control" value="<?= htmlspecialchars($extra['units']??'') ?>"></div>
                        <div class="col-md-8 mb-3"><label>Configuration</label><input type="text" name="configuration" class="form-control" value="<?= htmlspecialchars($project['configuration']??'') ?>" placeholder="Stilt+4 (8 units • 3BHK)"></div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 interior-fields cat-fields" style="display:none;">
                <div class="card-header">Interior Details</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Scope</label><input type="text" name="scope" class="form-control" value="<?= htmlspecialchars($extra['scope']??'') ?>" placeholder="Kitchen + Wardrobes"></div>
                        <div class="col-md-6 mb-3"><label>Property Type</label><input type="text" name="property_type" class="form-control" value="<?= htmlspecialchars($extra['property_type']??'') ?>"></div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 arch-fields cat-fields" style="display:none;">
                <div class="card-header">Architectural Design Details</div>
                <div class="card-body">
                    <div class="mb-3"><label>Design Scope</label><input type="text" name="design_scope" class="form-control" value="<?= htmlspecialchars($extra['design_scope']??'') ?>"></div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">Specifications & Features</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Built-up Area</label><input type="text" name="built_up_area" class="form-control" value="<?= htmlspecialchars($project['built_up_area']??'') ?>"></div>
                        <div class="col-md-6 mb-3"><label>Plot Size</label><input type="text" name="plot_size" class="form-control" value="<?= htmlspecialchars($project['plot_size']??'') ?>"></div>
                    </div>
                    <div class="mb-3"><label>Duration</label><input type="text" name="duration" class="form-control" value="<?= htmlspecialchars($project['duration']??'') ?>" placeholder="12 months"></div>
                    <div class="mb-3"><label>Materials / Specifications</label><textarea name="materials" class="form-control" rows="3"><?= htmlspecialchars($project['materials']??'') ?></textarea></div>
                    <div class="mb-3">
                        <label>Key Features (one per line)</label>
                        <div id="features-list">
                            <?php foreach ($features as $f): ?>
                            <div class="input-group mb-2"><input type="text" name="features[]" class="form-control" value="<?= htmlspecialchars($f) ?>"><button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button></div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addFeature()">+ Add Feature</button>
                    </div>
                    <div class="mb-3"><label>Client Testimonial (optional)</label><textarea name="testimonial" class="form-control" rows="2"><?= htmlspecialchars($project['testimonial']??'') ?></textarea></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">Images</div>
                <div class="card-body">
                    <div id="image-previews" class="d-flex flex-wrap mb-3">
                        <?php foreach ($images as $img): ?>
                        <div class="image-preview-box me-2 mb-2">
                            <img src="<?= UPLOAD_URL . $img['image_path'] ?>" alt="">
                            <button type="button" class="remove-img" onclick="markDelete(<?= $img['id'] ?>, this)">×</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="file" id="new-images" name="new_images[]" multiple accept="image/*" class="d-none">
                    <button type="button" id="add-image-btn" class="btn btn-outline-primary w-100">+ Add Images</button>
                    <div id="delete-inputs"></div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">SEO</div>
                <div class="card-body">
                    <div class="mb-3"><label>SEO Title</label><input type="text" name="seo_title" class="form-control" value="<?= htmlspecialchars($project['seo_title']??'') ?>"></div>
                    <div class="mb-3"><label>Meta Description</label><textarea name="seo_description" class="form-control" rows="3"><?= htmlspecialchars($project['seo_description']??'') ?></textarea></div>
                    <div class="form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="featured" <?= ($project['is_featured']??0)?'checked':'' ?>>
                        <label class="form-check-label" for="featured">Featured Project</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-custom w-100 py-2">Save Project</button>
        </div>
    </div>
</form>

<script>
function addFeature() {
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = '<input type="text" name="features[]" class="form-control" placeholder="Feature"><button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button>';
    document.getElementById('features-list').appendChild(div);
}
function markDelete(id, btn) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'delete_images[]';
    input.value = id;
    document.getElementById('delete-inputs').appendChild(input);
    btn.closest('.image-preview-box').remove();
}
</script>
<?php require_once 'includes/footer.php'; ?>
