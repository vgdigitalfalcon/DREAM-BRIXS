<?php
declare(strict_types=1);
$configFile = dirname(__DIR__) . '/config.php';
if (!is_file($configFile)) { http_response_code(500); exit('Configuration missing. Copy config.example.php to config.php and update database settings.'); }
$config = require $configFile;
date_default_timezone_set($config['timezone'] ?? 'Asia/Kolkata');
session_name($config['session_name'] ?? 'dreambrixs_crm_session');
session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','samesite'=>'Lax']);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function db(): PDO {
    static $pdo = null;
    global $config;
    if ($pdo instanceof PDO) return $pdo;
    $dsn = 'mysql:host='.$config['db_host'].';dbname='.$config['db_name'].';charset=utf8mb4';
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]);
    $offset = date('P'); $pdo->exec("SET time_zone = '" . $offset . "'");
    return $pdo;
}
function e(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function base_url(string $path=''): string { global $config; return rtrim($config['base_url'] ?? '', '/') . ($path ? '/' . ltrim($path, '/') : ''); }
function redirect(string $path): never { header('Location: '.base_url($path)); exit; }
function csrf_token(): string { if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32)); return $_SESSION['_csrf']; }
function verify_csrf(): void { if ($_SERVER['REQUEST_METHOD']==='POST' && !hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'] ?? '')) { http_response_code(419); exit('Invalid security token. Refresh the page and try again.'); } }
function require_login(): void { if (empty($_SESSION['admin_id'])) redirect('admin/login.php'); }
function current_user(): ?array { return empty($_SESSION['admin_id']) ? null : ['id'=>(int)$_SESSION['admin_id'],'name'=>$_SESSION['admin_name'] ?? 'Admin','role'=>$_SESSION['admin_role'] ?? 'editor']; }
function slugify(string $text): string { $text = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$text) ?: $text; $text = strtolower($text); $text = preg_replace('/[^a-z0-9]+/','-',$text) ?? ''; return trim($text,'-') ?: 'project'; }
function unique_slug(string $slug, int $excludeId=0): string { $base = slugify($slug); $candidate=$base; $i=2; $q=db()->prepare('SELECT id FROM projects WHERE slug=? AND id<>? LIMIT 1'); while (true) { $q->execute([$candidate,$excludeId]); if (!$q->fetch()) return $candidate; $candidate=$base.'-'.$i++; } }
function json_decode_array(?string $value): array { $decoded=json_decode((string)$value,true); return is_array($decoded)?$decoded:[]; }
function setting(string $key, string $default=''): string { $q=db()->prepare('SELECT setting_value FROM settings WHERE setting_key=?'); $q->execute([$key]); $r=$q->fetch(); return $r ? (string)$r['setting_value'] : $default; }
function flash(string $type,string $message): void { $_SESSION['_flash']=['type'=>$type,'message'=>$message]; }
function show_flash(): void { if (!empty($_SESSION['_flash'])) { $f=$_SESSION['_flash']; unset($_SESSION['_flash']); echo '<p class="notice '.e($f['type']).'">'.e($f['message']).'</p>'; } }
function log_activity(?int $projectId,string $action,string $details=''): void { $u=current_user(); $q=db()->prepare('INSERT INTO activity_logs(user_id,project_id,action,details,ip_address) VALUES(?,?,?,?,?)'); $q->execute([$u['id']??null,$projectId,$action,$details,substr($_SERVER['REMOTE_ADDR']??'',0,45)]); }
function page_header(string $title): void { ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($title)?> | DreamBrixs CRM</title></head><body><header><strong>DreamBrixs CRM</strong> <nav><a href="<?=base_url('admin/index.php')?>">Dashboard</a> | <a href="<?=base_url('admin/projects/index.php')?>">Projects</a> | <a href="<?=base_url('admin/categories/index.php')?>">Categories & Fields</a> | <a href="<?=base_url('admin/logout.php')?>">Logout</a></nav></header><main><h1><?=e($title)?></h1><?php show_flash(); }
function page_footer(): void { ?></main><footer>DreamBrixs Project CMS</footer></body></html><?php }
function field_definitions(int $categoryId): array { $q=db()->prepare('SELECT * FROM category_fields WHERE category_id=? AND is_active=1 ORDER BY sort_order,id'); $q->execute([$categoryId]); return $q->fetchAll(); }
function seo_checklist(array $p): array { return [
 'SEO title is present' => !empty($p['seo_title']),
 'Meta description is present (50–160 characters)' => !empty($p['meta_description']) && (function_exists('mb_strlen')?mb_strlen($p['meta_description']):strlen($p['meta_description']))>=50 && (function_exists('mb_strlen')?mb_strlen($p['meta_description']):strlen($p['meta_description']))<=160,
 'URL slug is present' => !empty($p['slug']),
 'Focus keyword is present' => !empty($p['focus_keyword']),
 'Project overview is present' => !empty($p['overview']),
 'Public cover image is approved' => !empty($p['cover_image']) && !empty($p['cover_image_public']) && !empty($p['cover_image_approved']),
 'H1 heading is present' => !empty($p['h1_heading']),
 ]; }
