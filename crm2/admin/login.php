<?php
require_once __DIR__.'/../includes/bootstrap.php';
if (!empty($_SESSION['admin_id'])) redirect('admin/index.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$email=trim($_POST['email']??'');$password=$_POST['password']??'';$q=db()->prepare('SELECT * FROM users WHERE email=? AND is_active=1 LIMIT 1');$q->execute([$email]);$u=$q->fetch();if($u && password_verify($password,$u['password_hash'])){session_regenerate_id(true);$_SESSION['admin_id']=(int)$u['id'];$_SESSION['admin_name']=$u['name'];$_SESSION['admin_role']=$u['role'];redirect('admin/index.php');}$error='Invalid email or password.';}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>CRM Login</title></head><body><h1>DreamBrixs CRM Login</h1><?php if($error)echo '<p>'.e($error).'</p>';?><form method="post"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><p><label>Email <input type="email" name="email" required></label></p><p><label>Password <input type="password" name="password" required></label></p><button type="submit">Login</button></form></body></html>
