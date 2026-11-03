<?php
require_once __DIR__.'/../../includes/bootstrap.php';require_login();$id=(int)($_GET['id']??0);$q=db()->prepare('SELECT p.*,s.seo_title,s.meta_description,s.focus_keyword FROM projects p LEFT JOIN project_seo s ON s.project_id=p.id WHERE p.id=?');$q->execute([$id]);$p=$q->fetch();if(!$p){http_response_code(404);exit('Project not found');}$checks=seo_checklist($p);$pageTitle='SEO Checklist';require __DIR__.'/../../includes/admin_header.php';
?>
<link rel="stylesheet" href="projects-admin.css">

<div class="crm-page">
  <div class="page-header">
    <div>
      <h1>SEO Checklist</h1>
      <p class="subtitle">Project: <?=e($p['title'])?></p>
    </div>
    <a href="<?=base_url('admin/projects/edit.php?id='.$id)?>" class="btn btn-secondary">← Back to Project</a>
  </div>

  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.75rem;">
      <h2 style="margin:0;">Checklist Results</h2>
      <span class="score-pill"><?=count(array_filter($checks))?> / <?=count($checks)?> passed</span>
    </div>
    <ul class="checklist">
      <?php foreach($checks as $label=>$ok):?>
      <li>
        <span class="<?= $ok?'check-pass':'check-todo'?>"><?= $ok?'✓ PASS':'○ TODO'?></span>
        <span><?=e($label)?></span>
      </li>
      <?php endforeach;?>
    </ul>
  </div>
</div>
<?php require __DIR__.'/../../includes/admin_footer.php';
