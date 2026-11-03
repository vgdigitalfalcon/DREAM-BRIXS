<?php
/**
 * Public Blog Listing Page - DreamBrixs
 * Fully themed to match DreamBrixs service pages (navy + yellow, Poppins)
 */
require_once __DIR__ . '/../config.php';

// Always render in pure PHP


$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = (int)get_setting('posts_per_page', 6);
$offset = ($page - 1) * $per_page;

$category_slug = trim($_GET['category'] ?? '');
$search = trim($_GET['q'] ?? '');

$where = "p.status = 'published'";
$params = [];

if ($category_slug) {
    $where .= " AND c.slug = ?";
    $params[] = $category_slug;
}
if ($search) {
    $where .= " AND (p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$posts = [];
$popular = [];
$categories = [];
$tags = [];
$total = 0;
$total_pages = 1;

if (!empty($pdo)) {
    try {
        $count_sql = "SELECT COUNT(*) FROM posts p LEFT JOIN categories c ON p.category_id = c.id WHERE $where";
        $stmt = $pdo->prepare($count_sql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();
        $total_pages = max(1, ceil($total / $per_page));

        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug, u.full_name as author_name
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN users u ON p.author_id = u.id
            WHERE $where
            ORDER BY p.is_featured DESC, p.published_at DESC
            LIMIT $per_page OFFSET $offset
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        $popular = $pdo->query("
            SELECT p.id, p.title, p.slug, p.featured_image, p.published_at, p.view_count
            FROM posts p
            WHERE p.status = 'published'
            ORDER BY p.view_count DESC, p.published_at DESC
            LIMIT 5
        ")->fetchAll();

        $categories = $pdo->query("
            SELECT c.*, COUNT(p.id) as post_count
            FROM categories c
            LEFT JOIN posts p ON p.category_id = c.id AND p.status = 'published'
            WHERE c.is_active = 1
            GROUP BY c.id
            ORDER BY c.sort_order ASC, c.name ASC
        ")->fetchAll();

        $tags = $pdo->query("SELECT t.*, COUNT(pt.post_id) as usage_count FROM tags t LEFT JOIN post_tags pt ON t.id = pt.tag_id GROUP BY t.id ORDER BY usage_count DESC, t.name ASC LIMIT 20")->fetchAll();
    } catch (Throwable $e) {
        $posts = [];
    }
}

if (empty($posts)) {
    $fallback_all_posts = [
        [
            'title' => "Building a House in Chennai: A Beginner's Complete Guide",
            'slug' => 'building-a-house-in-chennai-beginners-guide',
            'featured_image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800',
            'category_name' => 'Homeowner Guide',
            'category_slug' => 'homeowner-guide',
            'published_at' => '2024-03-15 10:00:00',
            'reading_time' => 5,
            'excerpt' => 'From CMDA plan sanctions to soil bearing tests and coastal foundation selection, here is everything you must know before starting construction in Chennai.',
            'content' => ''
        ],
        [
            'title' => 'What Determines the Real Cost of Building a House in Chennai?',
            'slug' => 'what-determines-cost-of-building-house-chennai',
            'featured_image' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=800',
            'category_name' => 'Cost Breakdown',
            'category_slug' => 'cost-breakdown',
            'published_at' => '2024-02-20 10:00:00',
            'reading_time' => 6,
            'excerpt' => 'A transparent, nuanced analysis of structural materials, steel grades, interior finishes, MEP works, and how to prevent budget overruns in Chennai projects.',
            'content' => ''
        ],
        [
            'title' => 'Villa vs Independent House: Which One Fits Your Chennai Lifestyle?',
            'slug' => 'villa-vs-independent-house-which-is-right',
            'featured_image' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800',
            'category_name' => 'Architecture Trends',
            'category_slug' => 'architecture-trends',
            'published_at' => '2024-01-25 10:00:00',
            'reading_time' => 4,
            'excerpt' => 'Evaluating privacy, footprint utilization, maintenance convenience, and resale value across suburban and prime residential plots in Tamil Nadu.',
            'content' => ''
        ]
    ];
    $posts = $fallback_all_posts;
    $total = count($posts);
    $total_pages = 1;
}

if (empty($categories)) {
    $categories = [
        ['name' => 'Homeowner Guide', 'slug' => 'homeowner-guide', 'post_count' => 4],
        ['name' => 'Cost Breakdown', 'slug' => 'cost-breakdown', 'post_count' => 3],
        ['name' => 'Architecture Trends', 'slug' => 'architecture-trends', 'post_count' => 5],
        ['name' => 'Engineering & Quality', 'slug' => 'engineering-quality', 'post_count' => 2]
    ];
}
if (empty($popular)) {
    $popular = [
        ['title' => 'Building a House in Chennai: Beginner Guide', 'slug' => 'building-a-house-in-chennai-beginners-guide'],
        ['title' => 'Real Cost of Construction per Sq.Ft', 'slug' => 'what-determines-cost-of-building-house-chennai'],
        ['title' => 'Villa vs Independent House Selection', 'slug' => 'villa-vs-independent-house-which-is-right']
    ];
}
if (empty($tags)) {
    $tags = [
        ['name' => 'Chennai Construction'],
        ['name' => 'CMDA Approval'],
        ['name' => 'Vastu Design'],
        ['name' => 'Villa Architecture'],
        ['name' => 'Civil Engineering']
    ];
}

$site_url = rtrim(get_setting('site_url', SITE_URL), '/');
$site_name = get_setting('site_name', SITE_NAME);
$blog_title = get_setting('blog_title', 'Insights on Homes & Interiors');
$blog_desc = get_setting('blog_description', 'Expert guides on residential construction, architectural design, and turnkey interiors in Chennai.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($blog_title) ?> | <?= e($site_name) ?></title>
    <meta name="description" content="<?= e($blog_desc) ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-navy: #0a1628;
            --secondary-navy: #122036;
            --accent-yellow: #F5B800;
            --accent-orange: #E89B00;
            --text-dark: #1a1a1a;
            --text-muted: #555;
            --soft-bg: #f8f9fa;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, h1, h2, h3, h4, h5, h6, p, a, button, .navbar, .btn, .nav-link {
            font-family: 'Poppins', sans-serif !important;
        }
        html { scroll-behavior: smooth; scroll-padding-top: 90px; }
        body { color: var(--text-dark); overflow-x: hidden; }
        img { max-width: 100%; }
        p { text-align: justify; overflow-wrap: anywhere; }
        h1, h2, h3, h4, h5 { font-weight: 700; }

        .live-float { animation: liveFloat 5s ease-in-out infinite; }
        @keyframes liveFloat { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .top-bar { background: var(--primary-navy); color: #fff; font-size: 13px; padding: 10px 0; }
        .top-bar a { color: #fff; text-decoration: none; }
        .top-bar a:hover { color: var(--accent-yellow); }
        .navbar { background: #fff !important; box-shadow: 0 2px 15px rgba(0,0,0,0.08); padding: 12px 0; }
        .navbar-brand img { height: 52px; max-width: 190px; object-fit: contain; }
        .nav-link { font-weight: 500; color: var(--text-dark) !important; margin: 0 8px; }
        .nav-link:hover, .nav-link.active { color: var(--accent-yellow) !important; }
        .btn-consult {
            display: inline-flex; align-items: center; justify-content: center; gap: 9px;
            background: linear-gradient(135deg, var(--accent-yellow) 0%, #ffd34d 52%, var(--accent-orange) 100%);
            color: #101010 !important; font-weight: 700; border-radius: 999px; padding: 12px 23px;
            text-decoration: none; transition: all 0.25s ease;
        }
        .btn-consult:hover { transform: translateY(-3px); color: #101010 !important; }
        .dropdown:hover > .dropdown-menu { display: block; }
        @media (min-width: 992px) {
            .dropdown-menu.mega {
                width: 780px;
                max-width: calc(100vw - 30px);
                left: 50% !important;
                right: auto !important;
                transform: translateX(-50%) !important;
            }
        }
        .dropdown-menu.mega { width: 780px; max-width: calc(100vw - 30px); padding: 20px; border: none; box-shadow: 0 15px 40px rgba(0,0,0,0.12); border-radius: 12px; }
        .mega-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
        .mega-item { padding: 12px 8px; border-radius: 8px; text-align: center; transition: 0.3s; }
        .mega-item:hover { background: #fff8e6; }
        .mega-item i { font-size: 20px; color: var(--accent-yellow); margin-bottom: 6px; display: block; }
        .mega-item a { color: var(--text-dark); font-weight: 500; font-size: 13.5px; text-decoration: none; }

        .blog-hero {
            min-height: 48vh;
            background: linear-gradient(115deg, rgba(10,22,40,0.88), rgba(10,22,40,0.65)),
                        url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1600') center/cover;
            display: flex; align-items: center; color: #fff; padding: 100px 0 60px;
        }
        .blog-hero h1 { font-size: 46px; font-weight: 800; }
        .blog-hero h1 span { color: var(--accent-yellow); }

        .post-card {
            background: #fff; border-radius: 16px; overflow: hidden;
            box-shadow: 0 8px 28px rgba(0,0,0,0.06); transition: all 0.4s ease; height: 100%;
            border: 1px solid #f0f0f0;
        }
        .post-card:hover { transform: translateY(-8px); box-shadow: 0 18px 40px rgba(245,184,0,0.14); }
        .post-card img { height: 210px; width: 100%; object-fit: cover; transition: transform 0.5s; }
        .post-card:hover img { transform: scale(1.05); }
        .post-card-body { padding: 18px 20px 22px; }
        .post-meta { font-size: 12.5px; color: var(--text-muted); margin-bottom: 8px; }
        .post-meta span { margin-right: 12px; }
        .category-badge { background: #fff8e6; color: #b58500; font-size: 11.5px; font-weight: 600; padding: 3px 10px; border-radius: 20px; }

        .sidebar-card { background: #fff; border-radius: 16px; padding: 22px; box-shadow: 0 8px 24px rgba(0,0,0,0.05); margin-bottom: 22px; border: 1px solid #eee; }
        .sidebar-card h5 { margin-bottom: 14px; }
        .sidebar-list { list-style: none; padding: 0; margin: 0; }
        .sidebar-list li { padding: 8px 0; border-bottom: 1px dashed #eee; }
        .sidebar-list li:last-child { border-bottom: none; }
        .sidebar-list a { color: var(--text-dark); text-decoration: none; font-size: 14.5px; }
        .sidebar-list a:hover { color: var(--accent-yellow); }
        .tag-cloud a { display: inline-block; background: #f4f4f4; color: #555; padding: 4px 12px; border-radius: 20px; font-size: 12.5px; margin: 4px 4px 0 0; text-decoration: none; }
        .tag-cloud a:hover { background: var(--accent-yellow); color: #000; }

        .pagination .page-link { border-radius: 8px; margin: 0 3px; color: var(--primary-navy); }
        .pagination .page-item.active .page-link { background: var(--accent-yellow); border-color: var(--accent-yellow); color: #000; }

        .footer-fresh { background: #070d18; color: #bbb; padding: 70px 0 25px; }
        .footer-fresh h5 { color: #fff; font-weight: 600; }
        .footer-fresh a { color: #bbb; text-decoration: none; }
        .footer-fresh a:hover { color: var(--accent-yellow); }
        .footer-logo { height: 48px; margin-bottom: 14px; }
        .whatsapp-float {
            position: fixed; right: 22px; bottom: 22px; z-index: 1050;
            display: inline-flex; align-items: center; gap: 11px;
            color: #fff !important; text-decoration: none; border-radius: 999px;
            background: linear-gradient(135deg, #20c96b, #0d9f57); padding: 6px 8px 6px 19px;
            box-shadow: 0 9px 25px rgba(18,159,87,.32);
        }
        .scroll-top {
            position: fixed; right: 28px; bottom: 94px; z-index: 1040; width: 46px; height: 46px;
            border: 0; border-radius: 50%; background: linear-gradient(135deg, var(--accent-yellow), #ffd34d);
            color: #111; display: none; align-items: center; justify-content: center; cursor: pointer;
        }
        .scroll-top.show { display: flex; }

        @media (max-width: 991.98px) {
            .blog-hero h1 { font-size: clamp(28px, 5vw, 38px) !important; }
            .dropdown:hover > .dropdown-menu { display: none !important; }
            .dropdown-menu.show { display: block !important; }
            .dropdown-menu.mega { position: static; width: 100%; }
            .mega-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 767.98px) {
            p { text-align: left; }
            .top-bar .container { flex-direction: column; text-align: center; }
            .whatsapp-float { right: 14px; bottom: 14px; width: 56px; min-width: 56px; padding: 5px; }
            .whatsapp-float span:first-child { display: none; }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <span><i class="far fa-clock"></i> Mon-Sat: 8:30 AM - 7:30 PM</span>
                <span class="mx-2">|</span>
                <a href="tel:+919941769381"><i class="fas fa-phone"></i> +91 99417 69381</a>
                <span class="mx-2">|</span>
                <a href="mailto:balajimani.2505@gmail.com"><i class="fas fa-envelope"></i> balajimani.2505@gmail.com</a>
            </div>
            <div><i class="fas fa-map-marker-alt"></i> Chennai, Tamil Nadu</div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="../index.html"><img src="../image/dreambrixs.png" alt="DreamBrixs"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="../about.html">About Us</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Services</a>
                        <div class="dropdown-menu mega">
                            <div class="mega-grid">
                                <div class="mega-item"><i class="fas fa-home"></i><a href="../individual-house-construction.html">Individual House</a></div>
                                <div class="mega-item"><i class="fas fa-building"></i><a href="../villa-construction.html">Villa Construction</a></div>
                                <div class="mega-item"><i class="fas fa-city"></i><a href="../apartment-construction.html">Apartment Construction</a></div>
                                <div class="mega-item"><i class="fas fa-briefcase"></i><a href="../commercial-building-construction.html">Commercial Building</a></div>
                                <div class="mega-item"><i class="fas fa-project-diagram"></i><a href="../residential-project-development.html">Project Development</a></div>
                                <div class="mega-item"><i class="fas fa-drafting-compass"></i><a href="../architectural-structural-design.html">Architectural Design</a></div>
                                <div class="mega-item"><i class="fas fa-couch"></i><a href="../turnkey-interior-solutions.html">Turnkey Interiors</a></div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="../projects.php">Projects</a></li>
                    <li class="nav-item"><a class="nav-link active" href="<?= e(blog_list_url()) ?>">Blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="../contact.html">Contact Us</a></li>
                </ul>
                <a href="../contact.html" class="btn btn-consult">GET A FREE CONSULTATION</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="blog-hero">
        <div class="container text-center" data-aos="fade-up">
            <div class="hero-badge-pill mb-3" style="display:inline-flex;align-items:center;gap:8px;background:rgba(245,184,0,0.16);border:1px solid rgba(245,184,0,0.45);color:var(--accent-yellow);padding:6px 16px;border-radius:999px;font-size:12.5px;font-weight:600;letter-spacing:1.2px;text-transform:uppercase;">
                <i class="fas fa-pen-fancy"></i> INSIGHTS & GUIDES
            </div>
            <h1 class="live-float"><?= e($blog_title) ?></h1>
            <p class="mt-3" style="opacity:0.9;max-width:620px;margin:0 auto;text-align:center;"><?= e($blog_desc) ?></p>
        </div>
    </section>

    <!-- Main -->
    <section class="py-5" style="background:var(--soft-bg);">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <?php if ($search || $category_slug): ?>
                    <div class="mb-4">
                        <a href="<?= e(blog_list_url()) ?>" class="text-decoration-none" style="color:var(--accent-yellow);"><i class="fas fa-arrow-left me-1"></i> Clear filters</a>
                        <?php if ($search): ?><span class="ms-3">Search: <strong><?= e($search) ?></strong></span><?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="row g-4">
                        <?php if (empty($posts)): ?>
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">No articles found. Check back soon for new insights.</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($posts as $i => $p): ?>
                        <div class="col-md-6" data-aos="fade-up" data-aos-delay="<?= $i*80 ?>">
                            <a href="<?= e(post_url($p['slug'])) ?>" class="text-decoration-none text-dark">
                                <div class="post-card">
                                    <?php if ($p['featured_image']): ?>
                                    <img src="<?= (strpos($p['featured_image'], 'http') === 0) ? e($p['featured_image']) : e(upload_url($p['featured_image'])) ?>" alt="<?= e($p['title']) ?>">
                                    <?php else: ?>
                                    <div style="height:210px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#999;">No Image</div>
                                    <?php endif; ?>
                                    <div class="post-card-body">
                                        <?php if ($p['category_name']): ?>
                                        <span class="category-badge"><?= e($p['category_name']) ?></span>
                                        <?php endif; ?>
                                        <h5 class="mt-2 mb-2"><?= e($p['title']) ?></h5>
                                        <div class="post-meta">
                                            <span><i class="far fa-calendar me-1"></i><?= format_date($p['published_at']) ?></span>
                                            <span><i class="far fa-clock me-1"></i><?= (int)$p['reading_time'] ?> min</span>
                                        </div>
                                        <p class="small text-muted mb-0"><?= e(truncate($p['excerpt'] ?: $p['content'], 110)) ?></p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php if ($total_pages > 1): ?>
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i==$page?'active':'' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $category_slug?'&category='.e($category_slug):'' ?><?= $search?'&q='.urlencode($search):'' ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <div class="sidebar-card">
                        <h5>Search</h5>
                        <form method="get" action="<?= e(blog_list_url()) ?>">
                            <div class="input-group">
                                <input type="text" name="q" class="form-control" placeholder="Search articles..." value="<?= e($search) ?>">
                                <button class="btn btn-consult" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>

                    <div class="sidebar-card">
                        <h5>Categories</h5>
                        <ul class="sidebar-list">
                            <?php foreach ($categories as $c): ?>
                            <li>
                                <a href="?category=<?= e($c['slug']) ?>">
                                    <?= e($c['name']) ?> <span class="float-end text-muted"><?= (int)$c['post_count'] ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <?php if ($popular): ?>
                    <div class="sidebar-card">
                        <h5>Popular Articles</h5>
                        <ul class="sidebar-list">
                            <?php foreach ($popular as $pp): ?>
                            <li><a href="<?= e(post_url($pp['slug'])) ?>"><?= e($pp['title']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <?php if ($tags): ?>
                    <div class="sidebar-card">
                        <h5>Tags</h5>
                        <div class="tag-cloud">
                            <?php foreach ($tags as $t): ?>
                            <a href="?q=<?= urlencode($t['name']) ?>"><?= e($t['name']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-fresh">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <img src="../image/dreambrixs.png" alt="DreamBrixs" class="footer-logo">
                    <p class="small" style="line-height:1.7;">We build with expertise and innovation, creating residential spaces that reflect quality, reliability and lasting architectural value across Chennai, Tamil Nadu.</p>
                </div>
                <div class="col-lg-2 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="../index.html">Home</a></li>
                        <li class="mb-2"><a href="../about.html">About Us</a></li>
                        <li class="mb-2"><a href="../projects.php">Projects</a></li>
                        <li class="mb-2"><a href="<?= e(blog_list_url()) ?>">Blog</a></li>
                        <li><a href="../contact.html">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Services</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="../individual-house-construction.html">Individual House</a></li>
                        <li class="mb-2"><a href="../villa-construction.html">Villa Construction</a></li>
                        <li class="mb-2"><a href="../apartment-construction.html">Apartment Construction</a></li>
                        <li><a href="../turnkey-interior-solutions.html">Turnkey Interiors</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Get in Touch</h5>
                    <p class="small mb-2"><i class="fas fa-map-marker-alt me-2" style="color:var(--accent-yellow);"></i> 2/10, Devi Parasakthi Nagar, Karambakkam, Porur, Chennai 600116</p>
                    <p class="small mb-2"><i class="fas fa-phone me-2" style="color:var(--accent-yellow);"></i> +91 99417 69381</p>
                    <p class="small"><i class="fas fa-envelope me-2" style="color:var(--accent-yellow);"></i> balajimani.2505@gmail.com</p>
                </div>
            </div>
            <div class="text-center pt-3 mt-3" style="border-top:1px solid rgba(255,255,255,0.1);">
                <p class="mb-0 small">&copy; 2026 DreamBrixs Residential Builders. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <a href="https://wa.me/919941769381" class="whatsapp-float" target="_blank"><span>Chat with us</span><i class="fab fa-whatsapp" style="font-size:27px;"></i></a>
    <button type="button" class="scroll-top" id="scrollTop"><i class="fas fa-arrow-up"></i></button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        if (window.AOS) AOS.init({ duration: 700, once: true });
        const scrollTopBtn = document.getElementById('scrollTop');
        window.addEventListener('scroll', () => scrollTopBtn.classList.toggle('show', window.pageYOffset > 300), { passive: true });
        scrollTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    </script>
</body>
</html>