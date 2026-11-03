<?php
/**
 * Single Blog Post Page - DreamBrixs
 * Fully themed to match DreamBrixs service pages
 */
require_once __DIR__ . '/../config.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: ' . blog_list_url()); exit; }

$post = null;
$related_posts = [];

if (!empty($pdo)) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug, 
                   u.full_name as author_name, u.bio as author_bio, u.expertise as author_expertise
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN users u ON p.author_id = u.id
            WHERE p.slug = ? AND p.status = 'published'
        ");
        $stmt->execute([$slug]);
        $post = $stmt->fetch();

        if ($post) {
            // Increment view
            $pdo->prepare("UPDATE posts SET view_count = view_count + 1 WHERE id = ?")->execute([$post['id']]);

            // Related
            $related = $pdo->prepare("
                SELECT p.title, p.slug, p.featured_image, p.published_at, c.name as category_name
                FROM posts p LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'published' AND p.id != ? AND (p.category_id = ? OR p.category_id IS NULL)
                ORDER BY p.published_at DESC LIMIT 3
            ");
            $related->execute([$post['id'], $post['category_id']]);
            $related_posts = $related->fetchAll();
        }
    } catch (Throwable $e) {
        $post = null;
    }
}

if (!$post) {
    // Pure PHP fallback articles
    $articles = [
        'building-a-house-in-chennai-2025-guide' => [
            'id' => 1,
            'title' => "Building a House in Chennai: A Beginner's 2025 Guide",
            'slug' => 'building-a-house-in-chennai-2025-guide',
            'category_name' => 'Homeowner Guide',
            'category_slug' => 'homeowner-guide',
            'author_name' => 'DreamBrixs Engineering Team',
            'author_bio' => 'Senior Civil Engineers at DreamBrixs Residential Builders with over two decades of on-site structural expertise in Chennai.',
            'featured_image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1000',
            'excerpt' => 'Planning your first residential construction project in Chennai can be daunting. From verifying Patta and obtaining CMDA/GCC plan sanction permits to soil plate bearing tests, this guide covers every step.',
            'content' => "<p>Planning your first residential construction project in Chennai can be daunting. From verifying Patta and obtaining CMDA/GCC plan sanction permits to conducting soil plate bearing tests, this guide breaks down every step.</p><p>Key considerations include choosing pile foundations vs isolated footings for high water-table zones like Velachery and OMR, budgeting realistic contingency buffers for material price shifts, and ensuring 100% Vastu compliance right from initial 2D line sketches.</p>",
            'published_at' => '2025-03-01 10:00:00',
            'reading_time' => 5,
            'view_count' => 1420
        ],
        'cost-of-building-a-house-in-chennai' => [
            'id' => 2,
            'title' => 'What Determines the Cost of Building a House in Chennai?',
            'slug' => 'cost-of-building-a-house-in-chennai',
            'category_name' => 'Cost Breakdown',
            'category_slug' => 'cost-breakdown',
            'author_name' => 'Civil Estimation Desk',
            'author_bio' => 'Quantity Surveyors and Cost Estimators at DreamBrixs managing transparent BOQs across Tamil Nadu.',
            'featured_image' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=1000',
            'excerpt' => 'Construction costs in Chennai are influenced by structural engineering specifications, architectural facade complexity, and internal finish grades.',
            'content' => "<p>Construction costs in Chennai are influenced by three primary variables: structural engineering specifications (such as Fe550D TMT and M25 Ready-Mix concrete), architectural facade complexity, and internal finish grades.</p><p>We analyze the differences between standard civil finishing, premium grade joinery (teak vs UPVC), multi-layer waterproofing, and smart MEP conduits. Learn how transparent BOQ billing with zero escalation clauses guarantees your budget remains intact.</p>",
            'published_at' => '2025-02-15 11:30:00',
            'reading_time' => 6,
            'view_count' => 980
        ]
    ];
    $post = $articles[$slug] ?? reset($articles);
}

$site_name = get_setting('site_name', SITE_NAME);
$meta_title = !empty($post['meta_title']) ? $post['meta_title'] : ($post['title'] . ' | ' . $site_name);
$meta_desc = !empty($post['meta_description']) ? $post['meta_description'] : truncate(!empty($post['excerpt']) ? $post['excerpt'] : strip_tags($post['content']), 155);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($meta_title) ?></title>
    <meta name="description" content="<?= e($meta_desc) ?>">
    <?php if (!empty($post['meta_keywords'])): ?><meta name="keywords" content="<?= e($post['meta_keywords']) ?>"><?php endif; ?>
    <link rel="canonical" href="<?= e(!empty($post['canonical_url']) ? $post['canonical_url'] : post_url($post['slug'])) ?>">
    
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
        .post-content h2, .post-content h3 { margin-top: 1.6rem; margin-bottom: 0.8rem; }
        .post-content p { margin-bottom: 1rem; line-height: 1.75; }
        .post-content ul, .post-content ol { margin-bottom: 1rem; padding-left: 1.4rem; }
        .post-content blockquote { border-left: 4px solid var(--accent-yellow); padding: 12px 18px; background: #fff8e6; margin: 1.2rem 0; font-style: italic; }

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

        .post-hero {
            min-height: 42vh;
            background: linear-gradient(115deg, rgba(10,22,40,0.88), rgba(10,22,40,0.6)),
                        url('<?= !empty($post['featured_image']) ? e(upload_url($post['featured_image'])) : "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1600" ?>') center/cover;
            display: flex; align-items: flex-end; color: #fff; padding: 100px 0 50px;
        }
        .breadcrumb { background: transparent; }
        .breadcrumb-item a { color: rgba(255,255,255,0.85); text-decoration: none; }
        .breadcrumb-item a:hover { color: var(--accent-yellow); }
        .breadcrumb-item.active { color: #fff; }
        .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,0.55); }

        .content-card { background: #fff; border-radius: 16px; padding: 32px 28px; box-shadow: 0 8px 28px rgba(0,0,0,0.05); border: 1px solid #f0f0f0; }
        .related-card { background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.05); transition: all 0.3s; height: 100%; }
        .related-card:hover { transform: translateY(-4px); }
        .related-card img { height: 150px; width: 100%; object-fit: cover; }

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
        }
        .scroll-top {
            position: fixed; right: 28px; bottom: 94px; z-index: 1040; width: 46px; height: 46px;
            border: 0; border-radius: 50%; background: linear-gradient(135deg, var(--accent-yellow), #ffd34d);
            color: #111; display: none; align-items: center; justify-content: center; cursor: pointer;
        }
        .scroll-top.show { display: flex; }

        @media (max-width: 991.98px) {
            .post-hero h1 { font-size: clamp(26px, 5vw, 36px) !important; }
            .dropdown:hover > .dropdown-menu { display: none !important; }
            .dropdown-menu.show { display: block !important; }
            .dropdown-menu.mega { position: static; width: 100%; }
            .mega-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 767.98px) {
            p { text-align: left; }
            .top-bar .container { flex-direction: column; text-align: center; }
            .content-card { padding: 22px 16px; }
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
            <a class="navbar-brand" href="/home.html"><img src="/image/dreambrixs.png" alt="DreamBrixs"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="/home.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/about.html">About Us</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Services</a>
                        <div class="dropdown-menu mega">
                            <div class="mega-grid">
                                <div class="mega-item"><i class="fas fa-home"></i><a href="/individual-house-construction.html">Individual House</a></div>
                                <div class="mega-item"><i class="fas fa-building"></i><a href="/villa-construction.html">Villa Construction</a></div>
                                <div class="mega-item"><i class="fas fa-city"></i><a href="/apartment-construction.html">Apartment Construction</a></div>
                                <div class="mega-item"><i class="fas fa-briefcase"></i><a href="/commercial-building-construction.html">Commercial Building</a></div>
                                <div class="mega-item"><i class="fas fa-project-diagram"></i><a href="/residential-project-development.html">Project Development</a></div>
                                <div class="mega-item"><i class="fas fa-drafting-compass"></i><a href="/architectural-structural-design.html">Architectural Design</a></div>
                                <div class="mega-item"><i class="fas fa-couch"></i><a href="/turnkey-interior-solutions.html">Turnkey Interiors</a></div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="/projects">Projects</a></li>
                    <li class="nav-item"><a class="nav-link active" href="<?= e(blog_list_url()) ?>">Blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="/contact.html">Contact Us</a></li>
                </ul>
                <a href="/contact.html" class="btn btn-consult">GET A FREE CONSULTATION</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="post-hero">
        <div class="container" data-aos="fade-up">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= e(blog_list_url()) ?>">Blog</a></li>
                    <?php if (!empty($post['category_name'])): ?>
                    <li class="breadcrumb-item"><a href="<?= e(blog_list_url()) ?>?category=<?= e($post['category_slug']) ?>"><?= e($post['category_name']) ?></a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active"><?= e($post['title']) ?></li>
                </ol>
            </nav>
            <h1 class="fw-bold mb-2" style="font-size:38px;line-height:1.2;"><?= e($post['title']) ?></h1>
            <p class="mb-0" style="opacity:0.9;font-size:14.5px;">
                <i class="far fa-calendar me-1"></i> <?= format_date($post['published_at']) ?>
                <span class="mx-2">•</span>
                <i class="far fa-clock me-1"></i> <?= (int)$post['reading_time'] ?> min read
                <?php if (!empty($post['author_name'])): ?>
                <span class="mx-2">•</span> By <?= e($post['author_name']) ?>
                <?php endif; ?>
            </p>
        </div>
    </section>

    <!-- Content -->
    <section class="py-5" style="background:var(--soft-bg);">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="content-card" data-aos="fade-up">
                        <div class="post-content">
                            <?= $post['content'] ?>
                        </div>
                    </div>

                    <?php if (!empty($related_posts)): ?>
                    <h4 class="mt-5 mb-3">Related Articles</h4>
                    <div class="row g-3">
                        <?php foreach ($related_posts as $r): ?>
                        <div class="col-md-4">
                            <a href="<?= e(post_url($r['slug'])) ?>" class="text-decoration-none text-dark">
                                <div class="related-card">
                                    <?php if (!empty($r['featured_image'])): ?>
                                    <img src="<?= e(upload_url($r['featured_image'])) ?>" alt="<?= e($r['title']) ?>">
                                    <?php else: ?>
                                    <div style="height:150px;background:#f0f0f0;"></div>
                                    <?php endif; ?>
                                    <div class="p-3">
                                        <h6 class="mb-1 small"><?= e($r['title']) ?></h6>
                                        <div class="text-muted" style="font-size:12px;"><?= format_date($r['published_at']) ?></div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <div class="content-card text-center">
                        <h5 class="mb-3">Need Help With Your Project?</h5>
                        <p class="small text-muted mb-3">Get a free consultation for your home or interior project in Chennai.</p>
                        <a href="/contact.html" class="btn btn-consult w-100 mb-2">Get Free Consultation</a>
                        <a href="https://wa.me/919941769381" class="btn btn-outline-secondary w-100" target="_blank" style="border-radius:999px;">Chat on WhatsApp</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-fresh">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <img src="/image/dreambrixs.png" alt="DreamBrixs" class="footer-logo">
                    <p class="small" style="line-height:1.7;">We build with expertise and innovation, creating residential spaces that reflect quality, reliability and lasting architectural value across Chennai, Tamil Nadu.</p>
                </div>
                <div class="col-lg-2 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/home.html">Home</a></li>
                        <li class="mb-2"><a href="/about.html">About Us</a></li>
                        <li class="mb-2"><a href="/projects">Projects</a></li>
                        <li class="mb-2"><a href="<?= e(blog_list_url()) ?>">Blog</a></li>
                        <li><a href="/contact.html">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Services</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/individual-house-construction.html">Individual House</a></li>
                        <li class="mb-2"><a href="/villa-construction.html">Villa Construction</a></li>
                        <li class="mb-2"><a href="/apartment-construction.html">Apartment Construction</a></li>
                        <li><a href="/turnkey-interior-solutions.html">Turnkey Interiors</a></li>
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
