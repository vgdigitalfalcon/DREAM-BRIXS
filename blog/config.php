<?php
/**
 * DreamBrixs Blog CRM - Configuration
 * Theme: Navy (#0a1628) + Yellow (#F5B800) + Black + White | Oswald + Poppins
 * Optimized for Google + Generative Engine Optimization (GEO)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'u670120666_dbblogs');
define('DB_USER', 'u670120666_dbblogs');
define('DB_PASS', 'DBblogs@2026');
define('DB_CHARSET', 'utf8mb4');

// Site
define('SITE_NAME', 'DreamBrixs');
define('SITE_URL', 'https://www.dreambrixs.com');

/**
 * CRM folder name on the server (must match the actual folder).
 * Your file manager shows: ... > blogs > admin > uploads
 */
define('CRM_FOLDER', 'blog');

define('ADMIN_URL', SITE_URL . '/' . CRM_FOLDER . '/admin');
define('UPLOAD_PATH', __DIR__ . '/admin/uploads/');

/**
 * Root-relative path to uploads (starts with /).
 * Works on any domain. Uses CRM_FOLDER above.
 */
define('UPLOAD_URL', '/' . CRM_FOLDER . '/admin/uploads/');

// Recommended Featured / Cover Image size
define('FEATURED_IMAGE_WIDTH', 1200);
define('FEATURED_IMAGE_HEIGHT', 630);
define('FEATURED_IMAGE_NOTE', '1200 × 630 px (JPG or WebP, under 300 KB recommended)');

// Security
define('SESSION_NAME', 'db_crm_session');
define('CSRF_TOKEN_NAME', 'db_csrf');
define('PASSWORD_COST', 12);

// Pagination
define('POSTS_PER_PAGE', 6);

// Allowed image types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB

date_default_timezone_set('Asia/Kolkata');

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// PDO Connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    $pdo = null;
}

// ---------- Helpers ----------

function generate_csrf() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verify_csrf($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function current_user() {
    global $pdo;
    if (empty($_SESSION['user_id'])) return null;
    static $user = null;
    if ($user === null) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
    return $user;
}

function is_admin() {
    $user = current_user();
    return $user && $user['role'] === 'admin';
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'n-a';
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function format_date($datetime, $format = 'd M Y') {
    if (empty($datetime)) return '';
    $ts = strtotime($datetime);
    return $ts ? date($format, $ts) : '';
}

function truncate($text, $length = 100, $suffix = '…') {
    $text = trim(strip_tags($text ?? ''));
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text) <= $length) return $text;
        return mb_substr($text, 0, $length) . $suffix;
    }
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . $suffix;
}

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function get_setting($key, $default = '') {
    global $pdo;
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        if (!empty($pdo)) {
            try {
                $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
                while ($row = $stmt->fetch()) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            } catch (Throwable $e) {
                $settings = [];
            }
        }
    }
    return $settings[$key] ?? $default;
}

/**
 * Public URL helpers
 * Listing : /blog.html  (matches your main site)
 * Single  : /blog/{slug}  (clean URL via .htaccess)
 * Images  : /{CRM_FOLDER}/admin/uploads/{file}
 */
function site_base() {
    return rtrim(get_setting('site_url', SITE_URL), '/');
}

function public_blog_base() {
    return site_base() . '/' . CRM_FOLDER . '/public';
}

/** Full URL to a single post (clean URL: /blog/slug) */
function post_url($slug) {
    return site_base() . '/blog/' . rawurlencode($slug);
}

/**
 * Full URL to the blog listing.
 * Uses blog.html so it matches your main website navigation.
 */
function blog_list_url() {
    return site_base() . '/blog';
}

/**
 * URL for uploaded images (featured, social, media library).
 * Root-relative — works on any domain.
 */
function upload_url($filename = '') {
    $base = rtrim(UPLOAD_URL, '/') . '/';
    return $filename ? $base . ltrim($filename, '/') : $base;
}

/** Absolute URL version (for OG tags, emails, external sharing) */
function upload_url_absolute($filename = '') {
    $path = upload_url($filename);
    if (strpos($path, 'http') === 0) return $path;
    return rtrim(site_base(), '/') . $path;
}

/** URL for public blog CSS/JS assets */
function blog_asset_url($path = '') {
    return public_blog_base() . '/assets/' . ltrim($path, '/');
}

function calculate_reading_time($content) {
    $word_count = str_word_count(strip_tags($content));
    return max(1, ceil($word_count / 200));
}

/**
 * Static website pages (DreamBrixs residential services).
 * Not used by the blog-only sitemap, kept for reference / future full sitemap.
 */
function get_static_pages() {
    $base = site_base();
    return [
        // Home & main pages
        ['loc' => $base . '/',                                      'changefreq' => 'weekly',  'priority' => '1.0'],
        ['loc' => $base . '/about.html',                            'changefreq' => 'monthly', 'priority' => '0.8'],
        ['loc' => $base . '/projects.php',                          'changefreq' => 'weekly',  'priority' => '0.8'],
        ['loc' => $base . '/contact.html',                          'changefreq' => 'monthly', 'priority' => '0.7'],
        ['loc' => $base . '/blog',                                  'changefreq' => 'daily',   'priority' => '0.9'],
        // Services (matching main site navigation)
        ['loc' => $base . '/individual-house-construction.html',    'changefreq' => 'monthly', 'priority' => '0.8'],
        ['loc' => $base . '/villa-construction.html',               'changefreq' => 'monthly', 'priority' => '0.8'],
        ['loc' => $base . '/apartment-construction.html',           'changefreq' => 'monthly', 'priority' => '0.8'],
        ['loc' => $base . '/residential-project-development.html',  'changefreq' => 'monthly', 'priority' => '0.8'],
        ['loc' => $base . '/architectural-structural-design.html',  'changefreq' => 'monthly', 'priority' => '0.8'],
        ['loc' => $base . '/turnkey-interior-solutions.html',       'changefreq' => 'monthly', 'priority' => '0.8'],
        // Legal
        ['loc' => $base . '/privacy-policy.html',                   'changefreq' => 'yearly',  'priority' => '0.3'],
        ['loc' => $base . '/terms.html',                            'changefreq' => 'yearly',  'priority' => '0.3'],
    ];
}

/**
 * Regenerate blog-only sitemap.
 * Writes to /blog/public/sitemap-blog.xml (always writable).
 * Root .htaccess rule serves it as https://dreambrixs.com/sitemap-blog.xml
 * Also attempts to write a copy at the site root if permissions allow.
 * Contains only clean blog URLs (/blog and /blog/slug).
 */
function regenerate_sitemap() {
    global $pdo;
    $site_url = rtrim(get_setting('site_url', SITE_URL), '/');
    $today    = date('Y-m-d');

    $stmt = $pdo->query("SELECT slug, updated_at, published_at, is_featured FROM posts WHERE status = 'published' ORDER BY published_at DESC");
    $posts = $stmt->fetchAll();

    // ---------- Blog-only sitemap ----------
    $blog_xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $blog_xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // Blog listing (clean URL: /blog)
    $blog_xml .= "  <url>\n";
    $blog_xml .= "    <loc>" . blog_list_url() . "</loc>\n";
    $blog_xml .= "    <lastmod>{$today}</lastmod>\n";
    $blog_xml .= "    <changefreq>daily</changefreq>\n";
    $blog_xml .= "    <priority>0.9</priority>\n";
    $blog_xml .= "  </url>\n";

    foreach ($posts as $post) {
        $lastmod  = date('Y-m-d', strtotime($post['updated_at'] ?: $post['published_at']));
        $priority = $post['is_featured'] ? '0.9' : '0.7';
        $blog_xml .= "  <url>\n";
        $blog_xml .= "    <loc>" . post_url($post['slug']) . "</loc>\n";
        $blog_xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $blog_xml .= "    <changefreq>weekly</changefreq>\n";
        $blog_xml .= "    <priority>{$priority}</priority>\n";
        $blog_xml .= "  </url>\n";
    }
    $blog_xml .= '</urlset>';

    // Always write inside the blog folder (this location is writable)
    $public = __DIR__ . '/public/';
    if (!is_dir($public)) {
        @mkdir($public, 0755, true);
    }
    file_put_contents($public . 'sitemap-blog.xml', $blog_xml);

    // Also try to write to the site root (may fail if no permission)
    $site_root = dirname(__DIR__) . '/';
    @file_put_contents($site_root . 'sitemap-blog.xml', $blog_xml);

    // robots.txt (try root, always write inside public)
    $robots = "User-agent: *\nAllow: /\nDisallow: /" . CRM_FOLDER . "/admin/\n\n";
    $robots .= "# Allow major AI / Generative Search crawlers (GEO)\n";
    $robots .= "User-agent: GPTBot\nAllow: /\n\n";
    $robots .= "User-agent: ChatGPT-User\nAllow: /\n\n";
    $robots .= "User-agent: Google-Extended\nAllow: /\n\n";
    $robots .= "User-agent: ClaudeBot\nAllow: /\n\n";
    $robots .= "User-agent: PerplexityBot\nAllow: /\n\n";
    $robots .= "User-agent: Bytespider\nAllow: /\n\n";
    $robots .= "Sitemap: {$site_url}/sitemap-blog.xml\n";

    @file_put_contents($site_root . 'robots.txt', $robots);
    @file_put_contents($public . 'robots.txt', $robots);

    return true;
}

/**
 * Generate JSON-LD structured data for a post
 * Critical for Generative Engine Optimization (AI citations)
 */
function generate_json_ld(array $post, array $author = null, array $category = null) {
    $site_url  = rtrim(get_setting('site_url', SITE_URL), '/');
    $site_name = get_setting('site_name', SITE_NAME);

    $schema_type = $post['schema_type'] ?? 'BlogPosting';
    $url = post_url($post['slug']);
    $image = !empty($post['social_image'])
        ? upload_url_absolute($post['social_image'])
        : (!empty($post['featured_image']) ? upload_url_absolute($post['featured_image']) : '');

    $data = [
        '@context' => 'https://schema.org',
        '@type'    => $schema_type,
        'headline' => $post['title'],
        'description' => $post['meta_description'] ?: $post['excerpt'] ?: '',
        'url' => $url,
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id'   => $url
        ],
        'datePublished' => date('c', strtotime($post['published_at'] ?: $post['created_at'])),
        'dateModified'  => date('c', strtotime($post['updated_at'] ?: $post['created_at'])),
        'publisher' => [
            '@type' => 'Organization',
            'name'  => $site_name,
            'url'   => $site_url,
            'logo'  => [
                '@type' => 'ImageObject',
                'url'   => $site_url . '/assets/image/dc-logos.png'
            ]
        ]
    ];

    if ($image) {
        $data['image'] = [
            '@type'  => 'ImageObject',
            'url'    => $image,
            'width'  => FEATURED_IMAGE_WIDTH,
            'height' => FEATURED_IMAGE_HEIGHT
        ];
    }

    if ($author) {
        $data['author'] = [
            '@type' => 'Person',
            'name'  => $author['full_name'],
            'url'   => $site_url
        ];
        if (!empty($author['expertise'])) {
            $data['author']['jobTitle'] = $author['expertise'];
        }
        if (!empty($author['bio'])) {
            $data['author']['description'] = $author['bio'];
        }
    }

    if ($category) {
        $data['articleSection'] = $category['name'];
    }

    if (!empty($post['focus_keyword'])) {
        $data['keywords'] = $post['focus_keyword'];
    }

    if (!empty($post['key_takeaways'])) {
        $data['abstract'] = $post['key_takeaways'];
    }

    // FAQ schema
    $faq_script = '';
    if (!empty($post['faq_json'])) {
        $items = is_string($post['faq_json']) ? json_decode($post['faq_json'], true) : $post['faq_json'];
        if (is_array($items) && count($items) > 0) {
            $faq_entities = [];
            foreach ($items as $item) {
                if (!empty($item['question']) && !empty($item['answer'])) {
                    $faq_entities[] = [
                        '@type' => 'Question',
                        'name'  => $item['question'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text'  => $item['answer']
                        ]
                    ];
                }
            }
            if ($faq_entities) {
                $faq = [
                    '@context'   => 'https://schema.org',
                    '@type'      => 'FAQPage',
                    'mainEntity' => $faq_entities
                ];
                $faq_script = "\n" . '<script type="application/ld+json">' . json_encode($faq, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';
            }
        }
    }

    $output = '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';
    return $output . $faq_script;
}