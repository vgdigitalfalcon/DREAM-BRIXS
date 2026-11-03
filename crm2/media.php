<?php
require_once __DIR__.'/includes/bootstrap.php';
$admin=!empty($_SESSION['admin_id']);$path=null;$mime=null;
if(isset($_GET['image'])){
 $id=(int)$_GET['image'];$q=db()->prepare('SELECT i.file_path,i.is_public,i.is_approved,p.status FROM project_images i JOIN projects p ON p.id=i.project_id WHERE i.id=?');$q->execute([$id]);$r=$q->fetch();if($r && ($admin || ($r['status']==='published' && $r['is_public'] && $r['is_approved'])))$path=$r['file_path'];
}elseif(isset($_GET['project'],$_GET['type'])){
 $id=(int)$_GET['project'];$type=$_GET['type']==='hero'?'hero':'cover';$q=db()->prepare('SELECT cover_image,cover_image_public,cover_image_approved,hero_image,hero_image_public,hero_image_approved,status FROM projects WHERE id=?');$q->execute([$id]);$r=$q->fetch();if($r){$allowed=$admin || ($r['status']==='published' && $r[$type.'_image_public'] && $r[$type.'_image_approved']);if($allowed)$path=$r[$type.'_image'];}
}
if(!$path || !str_starts_with($path,'uploads/projects/')){http_response_code(404);exit;}$root=realpath(__DIR__.'/uploads/projects');$file=realpath(__DIR__.'/'.$path);if(!$root||!$file||!str_starts_with($file,$root.DIRECTORY_SEPARATOR)||!is_file($file)){http_response_code(404);exit;}$finfo=new finfo(FILEINFO_MIME_TYPE);$mime=$finfo->file($file);if(!in_array($mime,['image/jpeg','image/png','image/webp'],true)){http_response_code(415);exit;}header('Content-Type: '.$mime);header('Content-Length: '.filesize($file));header('X-Content-Type-Options: nosniff');header('Cache-Control: '.($admin?'private, no-store':'public, max-age=3600'));readfile($file);
