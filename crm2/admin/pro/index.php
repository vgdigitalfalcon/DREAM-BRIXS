<?php
require_once __DIR__.'/../../includes/bootstrap.php';require_login();
$q=trim($_GET['q']??'');$status=$_GET['status']??'';$category=(int)($_GET['category']??0);$sql="SELECT p.*,c.name category_name,(SELECT COUNT(*) FROM page_views v WHERE v.project_id=p.id) total_views,(SELECT COUNT(*) FROM page_views v WHERE v.project_id=p.id AND v.viewed_at>=DATE_FORMAT(CURDATE(),'%Y-%m-01')) month_views FROM projects p JOIN project_categories c ON c.id=p.category_id WHERE 1=1";$args=[];if($q!==''){$sql.=' AND (p.title LIKE ? OR p.location LIKE ? OR p.city LIKE ?)';array_push($args,"%$q%","%$q%","%$q%");}if(in_array($status,['draft','published','archived'],true)){$sql.=' AND p.status=?';$args[]=$status;}if($category){$sql.=' AND p.category_id=?';$args[]=$category;}$sql.=' ORDER BY p.updated_at DESC';$s=db()->prepare($sql);$s->execute($args);$projects=$s->fetchAll();$cats=db()->query('SELECT id,name FROM project_categories ORDER BY name')->fetchAll();$pageTitle='Projects';require __DIR__.'/../../includes/admin_header.php';
?>
<link rel="stylesheet" href="projects-admin.css">

<div class="crm-page">
  <div class="page-header">
    <div>
      <h1>Projects</h1>
      <p class="subtitle">Manage all projects, status, views and publishing.</p>
    </div>
    <a href="<?=base_url('admin/projects/add.php')?>" class="btn">+ Add New Project</a>
  </div>

  <form method="get" class="filter-bar">
    <div class="field">
      <label>Search</label>
      <input name="q" value="<?=e($q)?>" placeholder="Title or location">
    </div>
    <div class="field">
      <label>Category</label>
      <select name="category">
        <option value="0">All categories</option>
        <?php foreach($cats as $c):?>
          <option value="<?=$c['id']?>" <?=$category===$c['id']?'selected':''?>><?=e($c['name'])?></option>
        <?php endforeach;?>
      </select>
    </div>
    <div class="field">
      <label>Status</label>
      <select name="status">
        <option value="">All statuses</option>
        <?php foreach(['draft','published','archived'] as $v):?>
          <option value="<?=$v?>" <?=$status===$v?'selected':''?>><?=ucfirst($v)?></option>
        <?php endforeach;?>
      </select>
    </div>
    <button type="submit">Filter</button>
  </form>

  <table class="data-table">
    <thead>
      <tr>
        <th>Project</th>
        <th>Category</th>
        <th>Status</th>
        <th>Total views</th>
        <th>This month</th>
        <th>Featured</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($projects as $p):?>
      <tr>
        <td data-label="Project"><strong><?=e($p['title'])?></strong></td>
        <td data-label="Category"><?=e($p['category_name'])?></td>
        <td data-label="Status"><span class="badge badge-<?=e($p['status'])?>"><?=e($p['status'])?></span></td>
        <td data-label="Views"><?= (int)$p['total_views']?></td>
        <td data-label="This month"><?= (int)$p['month_views']?></td>
        <td data-label="Featured"><span class="badge badge-<?= $p['is_featured']?'yes':'no'?>"><?= $p['is_featured']?'Yes':'No'?></span></td>
        <td data-label="Actions">
          <div class="actions">
            <a href="<?=base_url('admin/projects/edit.php?id='.$p['id'])?>">Edit</a>
            <a href="<?=base_url('admin/projects/revisions.php?id='.$p['id'])?>">History</a>
            <a href="<?=base_url('admin/projects/seo-check.php?id='.$p['id'])?>">SEO</a>
            <form method="post" action="<?=base_url('admin/projects/archive.php')?>" onsubmit="return confirm('Archive this project?')">
              <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
              <input type="hidden" name="id" value="<?=$p['id']?>">
              <button type="submit" class="btn-danger">Archive</button>
            </form>
            <a href="<?=base_url('project.php?slug='.urlencode($p['slug']))?>" target="_blank">Preview</a>
          </div>
        </td>
      </tr>
      <?php endforeach;?>
    </tbody>
  </table>
</div>
<?php require __DIR__.'/../../includes/admin_footer.php';
