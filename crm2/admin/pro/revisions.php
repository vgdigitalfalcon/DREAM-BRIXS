<?php
require_once __DIR__.'/../../includes/bootstrap.php';require_login();$id=(int)($_GET['id']??0);$q=db()->prepare('SELECT title FROM projects WHERE id=?');$q->execute([$id]);$title=$q->fetchColumn();if(!$title){http_response_code(404);exit('Project not found');}$q=db()->prepare('SELECT r.*,u.name user_name FROM project_revisions r LEFT JOIN users u ON u.id=r.user_id WHERE r.project_id=? ORDER BY r.created_at DESC');$q->execute([$id]);$rows=$q->fetchAll();$pageTitle='Project Revision History';require __DIR__.'/../../includes/admin_header.php';
?>
<link rel="stylesheet" href="projects-admin.css">

<div class="crm-page">
  <div class="page-header">
    <div>
      <h1>Revision History</h1>
      <p class="subtitle">Project: <?=e($title)?></p>
    </div>
    <a href="<?=base_url('admin/projects/edit.php?id='.$id)?>" class="btn btn-secondary">← Back to Project</a>
  </div>

  <table class="data-table">
    <thead>
      <tr>
        <th>Revision</th>
        <th>Saved by</th>
        <th>Date</th>
        <th>Snapshot</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rows as $r):?>
      <tr>
        <td data-label="Revision"><strong>#<?=$r['id']?></strong></td>
        <td data-label="Saved by"><?=e($r['user_name']??'System')?></td>
        <td data-label="Date"><?=e($r['created_at'])?></td>
        <td data-label="Snapshot">
          <details>
            <summary>View saved data</summary>
            <pre><?=e(json_encode(json_decode($r['revision_data'],true),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))?></pre>
          </details>
        </td>
      </tr>
      <?php endforeach;?>
    </tbody>
  </table>
</div>
<?php require __DIR__.'/../../includes/admin_footer.php';
