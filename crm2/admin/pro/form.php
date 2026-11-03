<?php
require_once __DIR__.'/../../includes/bootstrap.php';require_login();
$id=(int)($_GET['id']??0);$isEdit=$id>0;$project=[];$values=[];$selectedServices=[];$related=[];$seo=[];$images=[];
if($isEdit){$q=db()->prepare('SELECT p.*,s.seo_title,s.meta_description,s.focus_keyword,s.secondary_keywords,s.canonical_url,s.og_title,s.og_description,s.og_image,s.schema_type FROM projects p LEFT JOIN project_seo s ON s.project_id=p.id WHERE p.id=?');$q->execute([$id]);$project=$q->fetch();if(!$project){http_response_code(404);exit('Project not found');}$q=db()->prepare('SELECT field_id,value_json,visibility FROM project_field_values WHERE project_id=?');$q->execute([$id]);foreach($q->fetchAll() as $v)$values[$v['field_id']]=['value'=>json_decode($v['value_json'],true),'visibility'=>$v['visibility']];$q=db()->prepare('SELECT service_id FROM project_services WHERE project_id=?');$q->execute([$id]);$selectedServices=array_map('intval',array_column($q->fetchAll(),'service_id'));$q=db()->prepare('SELECT related_project_id FROM related_projects WHERE project_id=?');$q->execute([$id]);$related=array_map('intval',array_column($q->fetchAll(),'related_project_id'));$q=db()->prepare('SELECT * FROM project_images WHERE project_id=? ORDER BY sort_order,id');$q->execute([$id]);$images=$q->fetchAll();$q=db()->prepare('SELECT * FROM project_faqs WHERE project_id=? ORDER BY sort_order');$q->execute([$id]);$faqs=$q->fetchAll();}
$cats=db()->query('SELECT * FROM project_categories WHERE is_active=1 ORDER BY name')->fetchAll();$services=db()->query('SELECT * FROM services WHERE is_active=1 ORDER BY name')->fetchAll();$allProjects=db()->prepare('SELECT id,title FROM projects WHERE id<>? ORDER BY title');$allProjects->execute([$id]);$allProjects=$allProjects->fetchAll();$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$categoryId=(int)($_POST['category_id']??0);$title=trim($_POST['title']??'');$requestedSlug=trim($_POST['slug']??'');$autoSlug=!empty($_POST['auto_slug']);$slug=unique_slug($autoSlug||$requestedSlug===''?$title:$requestedSlug,$id);$status=$_POST['status']??'draft';if(!in_array($status,['draft','published','archived'],true))$status='draft';
 if(!$title)$errors[]='Project title is required.';if(!$categoryId)$errors[]='Project category is required.';
 $allowedText=['project_type','location','city','state','country','short_description','overview','design_concept','highlights','construction_details','materials','special_features','h1_heading'];$data=[];foreach($allowedText as $k)$data[$k]=trim($_POST[$k]??'');$year=trim($_POST['completion_year']??'');$data['completion_year']=$year!==''?(int)$year:null;$data['is_featured']=isset($_POST['is_featured'])?1:0;$data['display_order']=(int)($_POST['display_order']??0);$data['robots_index']=isset($_POST['robots_index'])?1:0;
 $defs=field_definitions($categoryId);$fieldValues=[];foreach($defs as $f){$raw=$_POST['field_'.$f['id']]??null;if($f['input_type']==='boolean')$val=isset($_POST['field_'.$f['id']])?1:0;elseif($f['input_type']==='multiselect')$val=is_array($raw)?array_values(array_filter(array_map('trim',$raw))):[];else $val=is_string($raw)?trim($raw):'';$fieldValues[$f['id']]=['value'=>$val,'visibility'=>in_array($_POST['visibility_'.$f['id']]??'public',['public','private','optional'],true)?$_POST['visibility_'.$f['id']]:'private'];}
 if(!$errors){$pdo=db();$pdo->beginTransaction();try{$user=current_user();$cols=['category_id','title','slug'];$vals=[$categoryId,$title,$slug];foreach($allowedText as $k){$cols[]=$k;$vals[]=$data[$k];}$cols=array_merge($cols,['completion_year','is_featured','display_order','robots_index','status','updated_by']);$vals=array_merge($vals,[$data['completion_year'],$data['is_featured'],$data['display_order'],$data['robots_index'],$status,$user['id']]);if($isEdit){$set=implode(',',array_map(fn($c)=>"$c=?",$cols));$q=$pdo->prepare("UPDATE projects SET $set WHERE id=?");$q->execute([...$vals,$id]);}else{$cols[]='created_by';$vals[]=$user['id'];$marks=implode(',',array_fill(0,count($cols),'?'));$q=$pdo->prepare('INSERT INTO projects('.implode(',',$cols).") VALUES($marks)");$q->execute($vals);$id=(int)$pdo->lastInsertId();}
 $pdo->prepare('DELETE FROM project_field_values WHERE project_id=?')->execute([$id]);$ins=$pdo->prepare('INSERT INTO project_field_values(project_id,field_id,value_json,visibility) VALUES(?,?,?,?)');foreach($fieldValues as $fid=>$v){if($v['value']!==''&&$v['value']!==[])$ins->execute([$id,$fid,json_encode($v['value']),$v['visibility']]);}
 $pdo->prepare('DELETE FROM project_services WHERE project_id=?')->execute([$id]);$ins=$pdo->prepare('INSERT INTO project_services(project_id,service_id) VALUES(?,?)');foreach(array_unique(array_map('intval',$_POST['services']??[])) as $sid)$ins->execute([$id,$sid]);
 $sections=['overview'=>$data['overview'],'design_concept'=>$data['design_concept'],'highlights'=>$data['highlights'],'construction_details'=>$data['construction_details'],'materials'=>$data['materials'],'special_features'=>$data['special_features']];$pdo->prepare('DELETE FROM project_contents WHERE project_id=?')->execute([$id]);$ins=$pdo->prepare('INSERT INTO project_contents(project_id,section_key,heading,body,is_public,sort_order) VALUES(?,?,?,?,?,?)');$n=0;foreach($sections as $key=>$body){if($body!==''){$ins->execute([$id,$key,ucwords(str_replace('_',' ',$key)),$body,isset($_POST['content_public'][$key])?1:0,$n++]);}}
 $seoFields=['seo_title','meta_description','focus_keyword','secondary_keywords','canonical_url','og_title','og_description','og_image'];$sv=[];foreach($seoFields as $k)$sv[$k]=trim($_POST[$k]??'');$schemaType=in_array($_POST['schema_type']??'WebPage',['WebPage','Article'],true)?$_POST['schema_type']:'WebPage';$pdo->prepare('INSERT INTO project_seo(project_id,seo_title,meta_description,focus_keyword,secondary_keywords,canonical_url,og_title,og_description,og_image,schema_type) VALUES(?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE seo_title=VALUES(seo_title),meta_description=VALUES(meta_description),focus_keyword=VALUES(focus_keyword),secondary_keywords=VALUES(secondary_keywords),canonical_url=VALUES(canonical_url),og_title=VALUES(og_title),og_description=VALUES(og_description),og_image=VALUES(og_image),schema_type=VALUES(schema_type)')->execute([$id,...array_values($sv),$schemaType]);
 $pdo->prepare('DELETE FROM related_projects WHERE project_id=?')->execute([$id]);$ins=$pdo->prepare('INSERT IGNORE INTO related_projects(project_id,related_project_id,sort_order) VALUES(?,?,?)');$order=0;foreach(array_unique(array_map('intval',$_POST['related']??[])) as $rid){if($rid!==$id)$ins->execute([$id,$rid,$order++]);}
 $pdo->prepare('DELETE FROM project_faqs WHERE project_id=?')->execute([$id]);$faqIns=$pdo->prepare('INSERT INTO project_faqs(project_id,question,answer,is_public,sort_order) VALUES(?,?,?,?,?)');foreach(($_POST['faq_question']??[]) as $fi=>$fq){$fq=trim($fq);$fa=trim($_POST['faq_answer'][$fi]??'');if($fq!==''&&$fa!=='')$faqIns->execute([$id,$fq,$fa,isset($_POST['faq_public'][$fi])?1:0,(int)$fi]);}
 // Validate and store image uploads; files are only exposed publicly when approved.
 $uploadDir=dirname(__DIR__,2).'/uploads/projects/';
 if(!is_dir($uploadDir) && !mkdir($uploadDir,0755,true)) throw new RuntimeException('Cannot create project upload directory.');
 $saveImage=function(string $field) use ($uploadDir,$id,$pdo): ?string {
   if(empty($_FILES[$field]['tmp_name']) || !is_uploaded_file($_FILES[$field]['tmp_name'])) return null;
   if($_FILES[$field]['error']!==UPLOAD_ERR_OK) throw new RuntimeException('Image upload failed for '.$field.'.');
   if($_FILES[$field]['size']>8*1024*1024) throw new RuntimeException('Each image must be 8 MB or smaller.');
   $info=getimagesize($_FILES[$field]['tmp_name']); if(!$info) throw new RuntimeException('Invalid image file.');
   $mime=$info['mime']??'';$ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;if(!$ext)throw new RuntimeException('Only JPG, PNG and WebP images are allowed.');
   $filename=$id.'-'.bin2hex(random_bytes(12)).'.'.$ext;
   if(!move_uploaded_file($_FILES[$field]['tmp_name'],$uploadDir.$filename)) throw new RuntimeException('Could not save uploaded image.');
   return 'uploads/projects/'.$filename;
 };
 $cover=$saveImage('cover_image');$hero=$saveImage('hero_image');
 if($cover!==null)$pdo->prepare('UPDATE projects SET cover_image=? WHERE id=?')->execute([$cover,$id]);
 if($hero!==null)$pdo->prepare('UPDATE projects SET hero_image=? WHERE id=?')->execute([$hero,$id]);
 $pdo->prepare('UPDATE projects SET cover_image_public=?,cover_image_approved=?,hero_image_public=?,hero_image_approved=? WHERE id=?')->execute([isset($_POST['cover_image_public'])?1:0,isset($_POST['cover_image_approved'])?1:0,isset($_POST['hero_image_public'])?1:0,isset($_POST['hero_image_approved'])?1:0,$id]);
 if(!empty($_FILES['gallery_images']['name'][0])){
   foreach($_FILES['gallery_images']['tmp_name'] as $i=>$tmp){if(!$tmp || !is_uploaded_file($tmp))continue;$err=$_FILES['gallery_images']['error'][$i]??UPLOAD_ERR_NO_FILE;if($err!==UPLOAD_ERR_OK)throw new RuntimeException('A gallery image failed to upload.');if(($_FILES['gallery_images']['size'][$i]??0)>8*1024*1024)throw new RuntimeException('Each image must be 8 MB or smaller.');$info=getimagesize($tmp);$mime=$info['mime']??'';$ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;if(!$ext)throw new RuntimeException('Gallery accepts JPG, PNG and WebP only.');$filename=$id.'-'.bin2hex(random_bytes(12)).'.'.$ext;if(!move_uploaded_file($tmp,$uploadDir.$filename))throw new RuntimeException('Could not save gallery image.');$pdo->prepare('INSERT INTO project_images(project_id,file_path,is_public,is_approved,sort_order) VALUES(?,?,0,0,?)')->execute([$id,'uploads/projects/'.$filename,$i]);}
 }
 foreach(($_POST['image_ids']??[]) as $imageId){$imageId=(int)$imageId;if(!empty($_POST['image_delete'][$imageId])){$q=$pdo->prepare('SELECT file_path FROM project_images WHERE id=? AND project_id=?');$q->execute([$imageId,$id]);$old=$q->fetchColumn();if($old){$full=dirname(__DIR__,2).'/'.$old;if(is_file($full))@unlink($full);$pdo->prepare('DELETE FROM project_images WHERE id=? AND project_id=?')->execute([$imageId,$id]);}continue;}$pdo->prepare('UPDATE project_images SET is_public=?,is_approved=?,alt_text=? WHERE id=? AND project_id=?')->execute([isset($_POST['image_public'][$imageId])?1:0,isset($_POST['image_approved'][$imageId])?1:0,trim($_POST['image_alt'][$imageId]??''),$imageId,$id]);}
 // Store a revision snapshot after every successful save.
 $snap=$pdo->prepare('SELECT * FROM projects WHERE id=?');$snap->execute([$id]);$snapshot=['project'=>$snap->fetch(),'fields'=>$fieldValues,'seo'=>$sv,'services'=>$_POST['services']??[],'related'=>$_POST['related']??[]];$pdo->prepare('INSERT INTO project_revisions(project_id,user_id,revision_data) VALUES(?,?,?)')->execute([$id,$user['id'],json_encode($snapshot,JSON_UNESCAPED_UNICODE)]);
 log_activity($id,$isEdit?'project_updated':'project_created','Saved project '.$title);$pdo->commit();flash('success','Project saved successfully.');redirect('admin/projects/edit.php?id='.$id);
 }catch(Throwable $ex){$pdo->rollBack();$errors[]='Save failed: '.$ex->getMessage();}}
}
if($isEdit){$q=db()->prepare('SELECT * FROM project_field_values WHERE project_id=?');$q->execute([$id]);foreach($q->fetchAll() as $v)$values[$v['field_id']]=['value'=>json_decode($v['value_json'],true),'visibility'=>$v['visibility']];$q=db()->prepare('SELECT * FROM project_images WHERE project_id=? ORDER BY sort_order,id');$q->execute([$id]);$images=$q->fetchAll();}
$categoryId=(int)($_POST['category_id']??$project['category_id']??($cats[0]['id']??0));$defs=$categoryId?field_definitions($categoryId):[];$pageTitle=$isEdit?'Edit Project':'Add Project';require __DIR__.'/../../includes/admin_header.php';

?>
<link rel="stylesheet" href="projects-admin.css">

<div class="crm-page">
  <div class="page-header">
    <div>
      <h1><?= $isEdit ? 'Edit Project' : 'Add New Project' ?></h1>
      <p class="subtitle"><?= $isEdit ? 'Update project details, content, media and SEO.' : 'Create a new project.' ?></p>
    </div>
    <a href="<?=base_url('admin/projects/index.php')?>" class="btn btn-secondary">← All Projects</a>
  </div>

  <?php if($errors): foreach($errors as $err): ?>
    <p class="notice error"><?=e($err)?></p>
  <?php endforeach; endif; ?>

  <form method="post" enctype="multipart/form-data" class="crm-form">
    <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">

    <div class="form-main">
      <fieldset>
        <legend>Basic Information</legend>
        <p><label>Project title <input id="title" name="title" required value="<?=e($_POST['title']??$project['title']??'')?>"></label></p>
        <p><label>Category <select id="category_id" name="category_id" required><?php foreach($cats as $c):?><option value="<?=$c['id']?>" <?=$categoryId===$c['id']?'selected':''?>><?=e($c['name'])?></option><?php endforeach;?></select></label> <small>Changing category reloads its field template after save. Save first if you need to preserve current fields.</small></p>
        <p><label>URL slug <input id="slug" name="slug" value="<?=e($_POST['slug']??$project['slug']??'')?>"></label> <label class="inline"><input type="checkbox" name="auto_slug" id="auto_slug" value="1" <?=(!$isEdit || !empty($_POST['auto_slug']))?'checked':'' ?>> Regenerate automatically from title</label> <button type="button" id="generate_slug" class="btn-sm">Generate slug</button></p>
        <div class="fields-2col">
          <p><label>Project type <input name="project_type" value="<?=e($_POST['project_type']??$project['project_type']??'')?>"></label></p>
          <p><label>Completion year <input type="number" name="completion_year" min="1900" max="2100" value="<?=e((string)($_POST['completion_year']??$project['completion_year']??''))?>"></label></p>
          <p><label>Location <input name="location" value="<?=e($_POST['location']??$project['location']??'')?>"></label></p>
          <p><label>City <input name="city" value="<?=e($_POST['city']??$project['city']??'')?>"></label></p>
          <p><label>State <input name="state" value="<?=e($_POST['state']??$project['state']??'')?>"></label></p>
          <p><label>Country <input name="country" value="<?=e($_POST['country']??$project['country']??'India')?>"></label></p>
        </div>
        <p><label>Short description <textarea name="short_description"><?=e($_POST['short_description']??$project['short_description']??'')?></textarea></label></p>
        <p><label class="inline"><input type="checkbox" name="is_featured" value="1" <?=!empty($_POST['is_featured'])||!empty($project['is_featured'])?'checked':''?>> Featured project</label></p>
        <p><label>Display order <input type="number" name="display_order" value="<?=e((string)($_POST['display_order']??$project['display_order']??0))?>"></label></p>
      </fieldset>

      <fieldset>
        <legend>Services</legend>
        <div class="service-list">
          <?php foreach($services as $s):?>
            <label class="inline"><input type="checkbox" name="services[]" value="<?=$s['id']?>" <?=in_array((int)$s['id'],$selectedServices,true)?'checked':''?>> <?=e($s['name'])?></label>
          <?php endforeach;?>
        </div>
      </fieldset>

      <fieldset id="specifications">
        <legend>Category Specifications</legend>
        <p style="color:var(--text-muted);font-size:0.9rem;margin-top:0;">Each specification has independent public visibility. Private fields are never sent to the public project page.</p>
        <?php foreach($defs as $f):$v=$values[$f['id']]['value']??null;$vis=$values[$f['id']]['visibility']??'private';$opts=json_decode($f['options_json']??'[]',true)?:[];$name='field_'.$f['id'];?>
        <div class="spec-row">
          <label><?=e($f['label'])?>
            <?php if($f['input_type']==='boolean'):?>
              <input type="checkbox" name="<?=$name?>" value="1" <?=!empty($v)?'checked':''?>>
            <?php elseif($f['input_type']==='select'):?>
              <select name="<?=$name?>"><option value="">Select</option><?php foreach($opts as $o):?><option value="<?=e($o)?>" <?=$v===$o?'selected':''?>><?=e($o)?></option><?php endforeach;?></select>
            <?php elseif($f['input_type']==='multiselect'):?>
              <select name="<?=$name?>[]" multiple><?php foreach($opts as $o):?><option value="<?=e($o)?>" <?=is_array($v)&&in_array($o,$v,true)?'selected':''?>><?=e($o)?></option><?php endforeach;?></select>
            <?php elseif($f['input_type']==='textarea'):?>
              <textarea name="<?=$name?>"><?=e(is_string($v)?$v:'')?></textarea>
            <?php else:?>
              <input type="<?=in_array($f['input_type'],['number','year'],true)?'number':'text'?>" name="<?=$name?>" value="<?=e(is_scalar($v)?(string)$v:'')?>">
            <?php endif;?>
          </label>
          <?=e($f['unit']??'')?>
          <label style="margin-top:0.5rem;">Visibility
            <select name="visibility_<?=$f['id']?>">
              <option value="public" <?=$vis==='public'?'selected':''?>>Public</option>
              <option value="optional" <?=$vis==='optional'?'selected':''?>>Optional public</option>
              <option value="private" <?=$vis==='private'?'selected':''?>>Private</option>
            </select>
          </label>
        </div>
        <?php endforeach;?>
      </fieldset>

      <fieldset>
        <legend>Project Page Content</legend>
        <?php foreach(['overview'=>'Project Overview','design_concept'=>'Design Concept','highlights'=>'Project Highlights','construction_details'=>'Construction Details','materials'=>'Materials & Finishes','special_features'=>'Special Features'] as $key=>$label):?>
        <p>
          <label><?=$label?><textarea name="<?=$key?>" rows="4"><?=e($_POST[$key]??$project[$key]??'')?></textarea></label>
          <label class="inline"><input type="checkbox" name="content_public[<?=$key?>]" value="1" <?=($_SERVER['REQUEST_METHOD']==='POST'?isset($_POST['content_public'][$key]):($contentVisibility[$key]??(($project[$key]??'')!==''?1:0)))?'checked':''?>> Show publicly</label>
        </p>
        <?php endforeach;?>
        <p><label>H1 heading <input name="h1_heading" value="<?=e($_POST['h1_heading']??$project['h1_heading']??'')?>"></label></p>
      </fieldset>

      <fieldset>
        <legend>Media & Approval</legend>
        <p><label>Cover image <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"></label> Current: <?=e($project['cover_image']??'None')?> <label class="inline"><input type="checkbox" name="cover_image_public" value="1" <?=!empty($project['cover_image_public'])?'checked':''?>> Public</label> <label class="inline"><input type="checkbox" name="cover_image_approved" value="1" <?=!empty($project['cover_image_approved'])?'checked':''?>> Approved</label></p>
        <p><label>Hero image <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp"></label> Current: <?=e($project['hero_image']??'None')?> <label class="inline"><input type="checkbox" name="hero_image_public" value="1" <?=!empty($project['hero_image_public'])?'checked':''?>> Public</label> <label class="inline"><input type="checkbox" name="hero_image_approved" value="1" <?=!empty($project['hero_image_approved'])?'checked':''?>> Approved</label></p>
        <p><label>Additional project images <input type="file" name="gallery_images[]" multiple accept="image/jpeg,image/png,image/webp"></label></p>
        <?php if($images):?>
          <h3 style="margin:1rem 0 0.5rem;font-size:1rem;">Existing images</h3>
          <?php foreach($images as $im):?>
          <div class="media-item">
            <img src="<?=base_url('media.php?image='.$im['id'])?>" alt="">
            <div style="flex:1;">
              <input type="hidden" name="image_ids[]" value="<?=$im['id']?>">
              <div style="font-size:0.85rem;word-break:break-all;"><?=e($im['file_path'])?></div>
              <label class="inline"><input type="checkbox" name="image_public[<?=$im['id']?>]" value="1" <?=$im['is_public']?'checked':''?>> Public</label>
              <label class="inline"><input type="checkbox" name="image_approved[<?=$im['id']?>]" value="1" <?=$im['is_approved']?'checked':''?>> Approved</label>
              <label>Alt <input name="image_alt[<?=$im['id']?>]" value="<?=e($im['alt_text']??'')?>"></label>
              <label class="inline"><input type="checkbox" name="image_delete[<?=$im['id']?>]" value="1"> Delete</label>
            </div>
          </div>
          <?php endforeach;?>
        <?php endif;?>
      </fieldset>

      <fieldset>
        <legend>Project FAQs</legend>
        <?php foreach(($faqs??[]) as $i=>$faq):?>
        <div class="faq-item">
          <p><label>Question <input name="faq_question[<?=$i?>]" value="<?=e($faq['question'])?>"></label></p>
          <p><label>Answer <textarea name="faq_answer[<?=$i?>]"><?=e($faq['answer'])?></textarea></label></p>
          <label class="inline"><input type="checkbox" name="faq_public[<?=$i?>]" value="1" <?=$faq['is_public']?'checked':''?>> Public</label>
        </div>
        <?php endforeach;?>
        <div class="faq-item">
          <p><label>New question <input name="faq_question[new]" placeholder="Question"></label></p>
          <p><label>Answer <textarea name="faq_answer[new]" placeholder="Answer"></textarea></label></p>
          <label class="inline"><input type="checkbox" name="faq_public[new]" value="1" checked> Public</label>
        </div>
      </fieldset>
    </div>

    <div class="form-sidebar">
      <fieldset>
        <legend>Publishing</legend>
        <p><label>Status <select name="status"><option value="draft" <?=($project['status']??'draft')==='draft'?'selected':''?>>Draft</option><option value="published" <?=($project['status']??'')==='published'?'selected':''?>>Published</option><option value="archived" <?=($project['status']??'')==='archived'?'selected':''?>>Archived</option></select></label></p>
        <p style="margin-top:1.25rem;"><button type="submit" class="btn" style="width:100%;justify-content:center;">Save Project</button></p>
        <p style="text-align:center;margin:0.6rem 0 0;"><a href="<?=base_url('admin/projects/index.php')?>">Cancel</a><?php if($isEdit):?> · <a target="_blank" href="<?=base_url('project.php?slug='.urlencode($project['slug']).'&preview=1')?>">Preview</a><?php endif;?></p>
      </fieldset>

      <fieldset>
        <legend>SEO</legend>
        <p><label>SEO title <input name="seo_title" maxlength="255" value="<?=e($_POST['seo_title']??$project['seo_title']??'')?>"></label></p>
        <p><label>Meta description <textarea name="meta_description" maxlength="320"><?=e($_POST['meta_description']??$project['meta_description']??'')?></textarea></label></p>
        <p><label>Focus keyword <input name="focus_keyword" value="<?=e($_POST['focus_keyword']??$project['focus_keyword']??'')?>"></label></p>
        <p><label>Secondary keywords <textarea name="secondary_keywords"><?=e($_POST['secondary_keywords']??$project['secondary_keywords']??'')?></textarea></label></p>
        <p><label>Canonical URL <input name="canonical_url" value="<?=e($_POST['canonical_url']??$project['canonical_url']??'')?>"></label></p>
        <p><label>Open Graph title <input name="og_title" value="<?=e($_POST['og_title']??$project['og_title']??'')?>"></label></p>
        <p><label>Open Graph description <textarea name="og_description"><?=e($_POST['og_description']??$project['og_description']??'')?></textarea></label></p>
        <p><label>Open Graph image URL/path <input name="og_image" value="<?=e($_POST['og_image']??$project['og_image']??'')?>"></label></p>
        <p><label>Schema type <select name="schema_type"><option value="WebPage">WebPage</option><option value="Article" <?=($project['schema_type']??'')==='Article'?'selected':''?>>Article</option></select></label></p>
        <p><label class="inline"><input type="checkbox" name="robots_index" value="1" <?=!isset($project['robots_index'])||$project['robots_index']?'checked':''?>> Allow indexing</label></p>
      </fieldset>

      <fieldset>
        <legend>Related Projects</legend>
        <select name="related[]" multiple style="min-height:180px;">
          <?php foreach($allProjects as $rp):?>
            <option value="<?=$rp['id']?>" <?=in_array((int)$rp['id'],$related,true)?'selected':''?>><?=e($rp['title'])?></option>
          <?php endforeach;?>
        </select>
        <small>Hold Ctrl/Cmd to select multiple.</small>
      </fieldset>
    </div>
  </form>
</div>
<?php require __DIR__.'/../../includes/admin_footer.php';
