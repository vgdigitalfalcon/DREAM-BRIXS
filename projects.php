<?php
// Place this file in your main public_html (or adjust path)
require_once __DIR__ . '/crm/config.php';

$projects = [];
$categories = [];
$total = 0;
$totalPages = 1;

if (!empty($pdo)) {
    try {
        $countSql = "SELECT COUNT(*) FROM projects p JOIN categories c ON p.category_id = c.id WHERE 1=1";
        $countParams = [];
        if ($cat) { $countSql .= " AND c.slug = ?"; $countParams[] = $cat; }
        if ($status) { $countSql .= " AND p.status = ?"; $countParams[] = $status; }
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($countParams);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT p.*, c.name as category_name, c.slug as cat_slug,
                (SELECT image_path FROM project_images WHERE project_id = p.id AND is_main = 1 LIMIT 1) as main_image,
                (SELECT image_path FROM project_images WHERE project_id = p.id ORDER BY sort_order LIMIT 1) as any_image
                FROM projects p JOIN categories c ON p.category_id = c.id WHERE 1=1";
        $params = [];
        if ($cat) { $sql .= " AND c.slug = ?"; $params[] = $cat; }
        if ($status) { $sql .= " AND p.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY p.year DESC, p.created_at DESC LIMIT $perPage OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $projects = $stmt->fetchAll();
        $categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
    } catch (Exception $e) {
        $projects = [];
    }
}

if (empty($projects)) {
    $categories = [
        ['name' => 'Individual House', 'slug' => 'individual-house'],
        ['name' => 'Villa Construction', 'slug' => 'villa-construction'],
        ['name' => 'Apartment Construction', 'slug' => 'apartment-construction'],
        ['name' => 'Commercial Construction', 'slug' => 'commercial-construction'],
        ['name' => 'Project Development', 'slug' => 'project-development'],
        ['name' => 'Turnkey Interiors', 'slug' => 'turnkey-interiors']
    ];
    $allFallbackProjects = [
        [
            'title' => 'The Grand Crest Residence',
            'slug' => 'grand-crest-residence',
            'location' => 'Anna Nagar West',
            'year' => '2025',
            'category_name' => 'Individual House',
            'cat_slug' => 'individual-house',
            'status' => 'Completed',
            'main_image' => '',
            'any_image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800'
        ],
        [
            'title' => 'Emerald Coast Villa',
            'slug' => 'emerald-coast-villa',
            'location' => 'ECR Beach Road',
            'year' => '2024',
            'category_name' => 'Villa Construction',
            'cat_slug' => 'villa-construction',
            'status' => 'Completed',
            'main_image' => '',
            'any_image' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800'
        ],
        [
            'title' => 'Nexus Prime Corporate Hub',
            'slug' => 'nexus-prime-corporate-hub',
            'location' => 'Guindy, Chennai',
            'year' => '2025',
            'category_name' => 'Commercial Construction',
            'cat_slug' => 'commercial-construction',
            'status' => 'Ongoing',
            'main_image' => '',
            'any_image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800'
        ],
        [
            'title' => 'Zenith Retail & Business Plaza',
            'slug' => 'zenith-retail-business-plaza',
            'location' => 'Anna Nagar, Chennai',
            'year' => '2024',
            'category_name' => 'Commercial Construction',
            'cat_slug' => 'commercial-construction',
            'status' => 'Completed',
            'main_image' => '',
            'any_image' => 'https://images.unsplash.com/photo-1577495508048-b635879837f1?w=800'
        ],
        [
            'title' => 'Horizon Commercial Tower',
            'slug' => 'horizon-commercial-tower',
            'location' => 'OMR, Chennai',
            'year' => '2024',
            'category_name' => 'Commercial Construction',
            'cat_slug' => 'commercial-construction',
            'status' => 'Completed',
            'main_image' => '',
            'any_image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800'
        ],
        [
            'title' => 'Sri Krishna Enclave Stilt+4',
            'slug' => 'sri-krishna-enclave',
            'location' => 'Velachery, Chennai',
            'year' => '2024',
            'category_name' => 'Apartment Construction',
            'cat_slug' => 'apartment-construction',
            'status' => 'Completed',
            'main_image' => '',
            'any_image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800'
        ],
        [
            'title' => 'Mayflower Luxury Duplex',
            'slug' => 'mayflower-luxury-duplex',
            'location' => 'Kotturpuram, Chennai',
            'year' => '2024',
            'category_name' => 'Individual House',
            'cat_slug' => 'individual-house',
            'status' => 'Completed',
            'main_image' => '',
            'any_image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800'
        ],
        [
            'title' => 'Serenity Bay Beach Villa',
            'slug' => 'serenity-bay-beach-villa',
            'location' => 'Besant Nagar, Chennai',
            'year' => '2023',
            'category_name' => 'Villa Construction',
            'cat_slug' => 'villa-construction',
            'status' => 'Completed',
            'main_image' => '',
            'any_image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800'
        ],
        [
            'title' => 'Aura Minimalist Penthouse',
            'slug' => 'aura-minimalist-penthouse',
            'location' => 'Boat Club Road',
            'year' => '2024',
            'category_name' => 'Turnkey Interiors',
            'cat_slug' => 'turnkey-interiors',
            'status' => 'Completed',
            'main_image' => '',
            'any_image' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=800'
        ]
    ];
    if ($cat) {
        $allFallbackProjects = array_values(array_filter($allFallbackProjects, function($p) use ($cat) { return $p['cat_slug'] === $cat; }));
    }
    $projects = $allFallbackProjects;
    $total = count($projects);
    $totalPages = 1;
}

// Helper to build filter URL keeping current filters
function buildUrl($extra = []) {
    global $cat, $status;
    $params = array_filter([
        'category' => $cat ?: null,
        'status' => $status ?: null,
    ]);
    $params = array_merge($params, $extra);
    return 'projects.php?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Projects | DreamBrixs Residential Builders | Chennai</title>
    <meta name="description" content="Explore DreamBrixs completed and ongoing projects across Chennai – Individual Houses, Villas, Apartments, Interiors and more.">
    
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
        body, h1, h2, h3, h4, h5, h6, p, a, button, input, select, textarea, .navbar, .btn, .nav-link, .dropdown-menu {
            font-family: 'Poppins', sans-serif !important;
        }
        html { scroll-behavior: smooth; scroll-padding-top: 90px; }
        body { color: var(--text-dark); overflow-x: hidden; width: 100%; max-width: 100%; }
        img { max-width: 100%; }
        p { text-align: justify; overflow-wrap: anywhere; }
        h1, h2, h3, h4, h5 { font-weight: 700; }

        .live-float { animation: liveFloat 5s ease-in-out infinite; }
        @keyframes liveFloat { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        /* Top Bar & Navbar */
        .top-bar { background: var(--primary-navy); color: #fff; font-size: 13px; padding: 10px 0; }
        .top-bar a { color: #fff; text-decoration: none; }
        .top-bar a:hover { color: var(--accent-yellow); }
        .navbar { background: #fff !important; box-shadow: 0 2px 15px rgba(0,0,0,0.08); padding: 12px 0; }
        .navbar-brand { flex-shrink: 0; }
        .navbar-brand img { height: 52px; display: block; max-width: 190px; width: auto; object-fit: contain; }
        .navbar-toggler { border-radius: 10px; padding: 7px 10px; }
        .nav-link { font-weight: 500; color: var(--text-dark) !important; margin: 0 8px; padding-top: 10px; padding-bottom: 10px; }
        .nav-link:hover, .nav-link.active { color: var(--accent-yellow) !important; }
        .btn-consult {
            display: inline-flex; align-items: center; justify-content: center; gap: 9px;
            background: linear-gradient(135deg, var(--accent-yellow) 0%, #ffd34d 52%, var(--accent-orange) 100%);
            color: #101010 !important; font-weight: 700; border: 1px solid rgba(232,155,0,.35);
            border-radius: 999px; padding: 12px 23px; line-height: 1.35; text-decoration: none;
            box-shadow: 0 7px 18px rgba(232,155,0,.18);
            transition: transform .25s ease, box-shadow .25s ease, filter .25s ease;
            background-size: 160% 160%; background-position: left center;
        }
        .btn-consult:hover, .btn-consult:focus-visible {
            color: #101010 !important; background-position: right center; filter: saturate(1.08);
            transform: translateY(-3px); box-shadow: 0 12px 24px rgba(232,155,0,.28);
        }
        .btn-outline-light { border-radius: 999px; padding: 11px 22px; font-weight: 600; transition: all .25s ease; }
        .btn-outline-light:hover { transform: translateY(-2px); }
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
        .mega-item:hover, .mega-item.active-service { background: #fff8e6; }
        .mega-item i { font-size: 20px; color: var(--accent-yellow); margin-bottom: 6px; display: block; }
        .mega-item a { color: var(--text-dark); font-weight: 500; font-size: 13.5px; text-decoration: none; }

        section:not(.proj-hero) { position: relative; overflow: hidden; }
        section:not(.proj-hero) > .container { position: relative; z-index: 1; }
        .rotating-circle {
            position: absolute; border: 2px dashed var(--accent-yellow); border-radius: 50%;
            opacity: 0.3; animation: spin 22s linear infinite; pointer-events: none; z-index: 0;
            max-width: 42vw; max-height: 42vw;
        }

        /* Hero - Desktop + Mobile backgrounds */
        .proj-hero {
            min-height: 62vh;
            background: linear-gradient(115deg, rgba(10,22,40,0.88) 0%, rgba(10,22,40,0.62) 50%, rgba(10,22,40,0.78) 100%),
                        url('image/projects-hero-bg.png') center/cover no-repeat;
            display: flex; align-items: center; color: #fff; position: relative; overflow: hidden; padding: 70px 0 50px;
        }
        .proj-hero::before {
            content: ''; position: absolute; width: 260px; height: 260px; border: 2px dashed var(--accent-yellow);
            border-radius: 50%; top: -80px; right: -80px; opacity: 0.25; animation: spin 24s linear infinite;
        }
        .proj-hero h1 { font-size: 48px; font-weight: 800; line-height: 1.15; }
        .proj-hero h1 span { color: var(--accent-yellow); }
        .hero-badge-pill {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(245,184,0,0.16); border: 1px solid rgba(245,184,0,0.45);
            color: var(--accent-yellow); padding: 6px 16px; border-radius: 999px;
            font-size: 12.5px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase;
        }

        /* Filters */
        .filter-wrap { background: #fff; border-radius: 60px; padding: 14px 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; }
        .filter-btn {
            border-radius: 999px; padding: 9px 18px; font-weight: 600; font-size: 14px;
            border: 1px solid #e0e0e0; background: #fff; color: var(--text-dark); text-decoration: none;
            transition: all 0.3s ease;
        }
        .filter-btn:hover, .filter-btn.active {
            background: var(--accent-yellow); color: #101010; border-color: var(--accent-yellow);
            transform: translateY(-2px);
        }

        /* Project Cards */
        .project-corp { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 28px rgba(0,0,0,0.06); transition: all 0.45s; height: 100%; cursor: pointer; }
        .project-corp:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(245,184,0,0.14); }
        .project-corp img { height: 210px; width: 100%; object-fit: cover; transition: transform 0.5s; }
        .project-corp:hover img { transform: scale(1.06); }
        .project-corp-body { padding: 16px 18px 18px; }
        .project-meta { display: flex; gap: 8px; flex-wrap: wrap; font-size: 12px; margin-top: 8px; }
        .project-meta span { background: #f4f4f4; padding: 3px 10px; border-radius: 20px; color: #555; }
        .status-badge { font-size: 11.5px; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
        .status-completed { background: #e8f5e9; color: #2e7d32; }
        .status-ongoing { background: #fff8e1; color: #f57c00; }

        /* Pagination */
        .pagination-wrap { display: flex; justify-content: center; gap: 6px; flex-wrap: wrap; margin-top: 40px; }
        .page-link {
            min-width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;
            border-radius: 12px; border: 1px solid #e0e0e0; background: #fff; color: var(--text-dark);
            font-weight: 600; text-decoration: none; transition: all 0.25s ease;
        }
        .page-link:hover { border-color: var(--accent-yellow); color: var(--accent-orange); }
        .page-link.active { background: var(--accent-yellow); color: #101010; border-color: var(--accent-yellow); }
        .page-link.disabled { opacity: 0.45; pointer-events: none; }

                /* Unified 4-Column Balanced Footer */
        .footer-fresh {
            background: #070d18;
            color: #94a3b8;
            padding: 70px 0 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-family: 'Poppins', sans-serif;
        }

        .footer-fresh h5 {
            color: #fff;
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 22px;
            position: relative;
            padding-bottom: 10px;
            letter-spacing: 0.3px;
        }

        .footer-fresh h5::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 32px;
            height: 2px;
            background: var(--accent-yellow);
            border-radius: 2px;
        }

        .footer-fresh p {
            color: #94a3b8;
            font-size: 14px;
            line-height: 1.65;
        }

        .footer-fresh ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .footer-fresh ul li {
            margin-bottom: 9px;
            position: static;
            padding-left: 0;
        }

        .footer-fresh ul li::before {
            content: none !important;
        }

        .footer-fresh ul li a {
            color: #94a3b8;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.25s ease;
        }

        .footer-fresh ul li a::before {
            content: '\203A';
            color: var(--accent-yellow);
            font-size: 16px;
            line-height: 1;
            transition: transform 0.25s ease;
        }

        .footer-fresh ul li a:hover {
            color: var(--accent-yellow);
            transform: translateX(4px);
        }

        .footer-fresh ul li a:hover::before {
            transform: translateX(2px);
        }

        .footer-fresh .footer-contact-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 12px;
            font-size: 13.5px;
            color: #94a3b8;
            line-height: 1.5;
        }

        .footer-fresh .footer-contact-item i {
            color: var(--accent-yellow);
            font-size: 15px;
            margin-top: 3px;
            flex-shrink: 0;
            width: 16px;
            text-align: center;
        }

        .footer-logo {
            height: 48px;
            margin-bottom: 16px;
        }

        .footer-social a {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            transition: all 0.3s;
            color: #fff;
            text-decoration: none;
        }

        .footer-social a:hover {
            background: var(--accent-yellow);
            color: #000;
            transform: translateY(-3px);
        }

        .whatsapp-float {
            position: fixed; right: 22px; bottom: 22px; z-index: 1050;
            width: auto; min-width: 62px; height: 58px; padding: 6px 8px 6px 19px;
            display: inline-flex; align-items: center; justify-content: center; gap: 11px;
            color: #fff !important; text-decoration: none; border-radius: 999px;
            background: linear-gradient(135deg, #20c96b, #0d9f57);
            box-shadow: 0 9px 25px rgba(18,159,87,.32);
            animation: whatsappBob 4s ease-in-out infinite;
        }
        .whatsapp-float::before {
            content: ''; position: absolute; inset: -5px; border: 1px solid rgba(37,211,102,.45);
            border-radius: inherit; animation: whatsappPulse 2.4s ease-out infinite; pointer-events: none;
        }
        .whatsapp-label { font-size: 13px; font-weight: 700; white-space: nowrap; }
        .whatsapp-icon-wrap { width: 43px; height: 43px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,.17); font-size: 27px; }
        .scroll-top {
            position: fixed; right: 28px; bottom: 94px; z-index: 1040; width: 46px; height: 46px;
            border: 0; border-radius: 50%; background: linear-gradient(135deg, var(--accent-yellow), #ffd34d);
            color: #111; box-shadow: 0 7px 18px rgba(0,0,0,.16); display: none; align-items: center; justify-content: center; cursor: pointer;
        }
        .scroll-top.show { display: flex; }
        @keyframes whatsappBob { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }
        @keyframes whatsappPulse { 0% { transform: scale(.96); opacity: .65; } 75%,100% { transform: scale(1.13); opacity: 0; } }

        /* Responsive */
        @media (max-width: 1199.98px) {
            .nav-link { margin: 0 3px; font-size: 14px; }
            .btn-consult { padding: 11px 18px; }
        }
        @media (max-width: 991.98px) {
            .dropdown:hover > .dropdown-menu { display: none !important; }
            .dropdown-menu.show { display: block !important; }
            .dropdown-menu.mega { position: static; float: none; width: 100%; margin-top: 8px; box-shadow: none; border: 1px solid #eee; }
            .navbar-collapse { padding: 14px 0 8px; }
            .nav-link { margin: 0; padding: 11px 8px; }
            .navbar-collapse > .btn-consult { width: 100%; margin-top: 10px; }
            .mega-grid { grid-template-columns: repeat(2, 1fr); }
            .proj-hero { min-height: 52vh; padding: 60px 0 40px; }
            .proj-hero h1 { font-size: clamp(32px, 5vw, 42px) !important; }
            .filter-wrap { border-radius: 24px; padding: 16px; }
        }
        @media (max-width: 767.98px) {
            body { font-size: 15px; }
            p { text-align: left; }
            .top-bar { padding: 8px 0; font-size: 11px; }
            .top-bar .container { flex-direction: column; text-align: center; gap: 5px; }
            .navbar { padding: 9px 0; }
            .navbar-brand img { height: 43px; max-width: 165px; }
            /* Mobile hero background */
            .proj-hero {
                min-height: auto; padding: 70px 0 40px;
                background-image: linear-gradient(115deg, rgba(10,22,40,0.88) 0%, rgba(10,22,40,0.65) 50%, rgba(10,22,40,0.78) 100%),
                                  url('image/projects-hero-bg-mob.png') center/cover no-repeat !important;
            }
            .proj-hero h1 { font-size: clamp(28px, 8vw, 36px) !important; }
            section.py-5 { padding-top: 48px !important; padding-bottom: 48px !important; }
            section h2 { font-size: clamp(24px, 6.3vw, 30px) !important; }
            .rotating-circle { opacity: .12; }
            .whatsapp-float { right: 14px; bottom: 14px; width: 56px; min-width: 56px; height: 56px; padding: 5px; }
            .whatsapp-label { display: none; }
            .scroll-top { right: 19px; bottom: 82px; width: 42px; height: 42px; }
            .filter-btn { padding: 8px 14px; font-size: 13px; }
            .page-link { min-width: 36px; height: 36px; font-size: 14px; }
        }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
            .live-float, .whatsapp-float, .whatsapp-float::before, .rotating-circle, .proj-hero::before { animation: none !important; }
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
            <a class="navbar-brand" href="index.html"><img src="image/dreambrixs.png" alt="DreamBrixs"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.html">About Us</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Services</a>
                        <div class="dropdown-menu mega">
                            <div class="mega-grid">
                                <div class="mega-item"><i class="fas fa-home"></i><a href="individual-house-construction.html">Individual House</a></div>
                                <div class="mega-item"><i class="fas fa-building"></i><a href="villa-construction.html">Villa Construction</a></div>
                                <div class="mega-item"><i class="fas fa-city"></i><a href="apartment-construction.html">Apartment Construction</a></div>
                                <div class="mega-item"><i class="fas fa-briefcase"></i><a href="commercial-building-construction.html">Commercial Building</a></div>
                                <div class="mega-item"><i class="fas fa-project-diagram"></i><a href="residential-project-development.html">Project Development</a></div>
                                <div class="mega-item"><i class="fas fa-drafting-compass"></i><a href="architectural-structural-design.html">Architectural Design</a></div>
                                <div class="mega-item"><i class="fas fa-couch"></i><a href="turnkey-interior-solutions.html">Turnkey Interiors</a></div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item"><a class="nav-link active" href="projects.php">Projects</a></li>
                    <li class="nav-item"><a class="nav-link" href="blog.php">Blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.html">Contact Us</a></li>
                </ul>
                <a href="contact.html" class="btn btn-consult">GET A FREE CONSULTATION</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="proj-hero">
        <div class="container text-center" data-aos="fade-up">
            <h1 class="live-float">Our <span>Projects</span></h1>
            <p class="mt-3 mx-auto" style="font-size:16px; color:rgba(255,255,255,0.9); max-width:620px; text-align:center;">Explore completed and ongoing residential projects by DreamBrixs across Chennai — from individual homes and villas to apartments and interiors.</p>
        </div>
    </section>

    <!-- Filters + Projects -->
    <section class="py-5" style="background:var(--soft-bg);">
        <div class="container">
            <div class="text-center mb-4" data-aos="fade-up">
                <div class="filter-wrap d-inline-flex">
                    <a href="<?= buildUrl(['page' => 1]) ?>" class="filter-btn <?= !$cat ? 'active' : '' ?>">All</a>
                    <?php foreach ($categories as $c): ?>
                        <a href="<?= buildUrl(['category' => $c['slug'], 'page' => 1]) ?>" class="filter-btn <?= $cat == $c['slug'] ? 'active' : '' ?>">
                            <?= htmlspecialchars($c['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="row g-4" id="projectsContainer">
                <?php foreach ($projects as $i => $p): 
                    $img = $p['main_image'] ?: $p['any_image'];
                    $imgUrl = $img ? 'crm/uploads/projects/' . htmlspecialchars($img) : 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800';
                    $statusClass = ($p['status'] == 'Completed') ? 'status-completed' : 'status-ongoing';
                ?>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 100 ?>">
                    <a href="/projects/<?= htmlspecialchars($p['slug']) ?>" class="text-decoration-none text-dark">
                        <div class="project-corp">
                            <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                            <div class="project-corp-body">
                                <h6 class="mb-1"><?= htmlspecialchars($p['title']) ?></h6>
                                <div class="small text-muted mb-2"><?= htmlspecialchars($p['location'] ?? '') ?> • <?= htmlspecialchars($p['year'] ?? '') ?></div>
                                <div class="project-meta">
                                    <span><?= htmlspecialchars($p['category_name']) ?></span>
                                    <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($p['status']) ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
                <?php if (empty($projects)): ?>
                <div class="col-12 text-center text-muted py-5">
                    <i class="fas fa-folder-open fa-2x mb-3" style="color:var(--accent-yellow);"></i>
                    <p class="mb-0">No projects found for the selected filter.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination-wrap" data-aos="fade-up">
                <a href="<?= buildUrl(['page' => max(1, $page - 1)]) ?>" class="page-link <?= $page <= 1 ? 'disabled' : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                if ($start > 1) echo '<a href="' . buildUrl(['page' => 1]) . '" class="page-link">1</a>';
                if ($start > 2) echo '<span class="page-link disabled">…</span>';
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <a href="<?= buildUrl(['page' => $i]) ?>" class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($end < $totalPages - 1) echo '<span class="page-link disabled">…</span>'; ?>
                <?php if ($end < $totalPages) echo '<a href="' . buildUrl(['page' => $totalPages]) . '" class="page-link">' . $totalPages . '</a>'; ?>
                <a href="<?= buildUrl(['page' => min($totalPages, $page + 1)]) ?>" class="page-link <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="py-5" style="background:linear-gradient(135deg,#0a1628,#122036);color:#fff;position:relative;overflow:hidden;">
        <div class="rotating-circle" style="width:180px;height:180px;bottom:-40px;left:-40px;opacity:0.25;"></div>
        <div class="container text-center" data-aos="fade-up">
            <h2 class="fw-bold mb-3">Ready to Start Your Project?</h2>
            <p class="mb-4" style="opacity:0.9;max-width:580px;margin:0 auto;text-align:center;">Partner with DreamBrixs for turnkey residential solutions across Chennai.</p>
            <a href="contact.html" class="btn btn-consult me-2">Get a Free Consultation</a>
            <a href="https://wa.me/919941769381" class="btn btn-outline-light" target="_blank" rel="noopener noreferrer">Chat on WhatsApp</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-fresh">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <img src="image/dreambrixs.png" alt="DreamBrixs" class="footer-logo">
                    <p class="small" style="line-height:1.7;">We build with expertise and innovation, creating residential spaces that reflect quality, reliability and lasting architectural value across Chennai, Tamil Nadu.</p>
                    <div class="footer-social mt-3">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="https://wa.me/919941769381" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="col-12 col-lg-2 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="home.html">Home</a></li>
                        <li class="mb-2"><a href="about.html">About Us</a></li>
                        <li class="mb-2"><a href="service.html">Services</a></li>
                        <li class="mb-2"><a href="projects.php">Projects</a></li>
                        <li class="mb-2"><a href="blog.php">Blog</a></li>
                        <li><a href="contact.html">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-12 col-lg-3 mb-4">
                    <h5>Services</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="individual-house-construction.html">Individual House</a></li>
                        <li class="mb-2"><a href="villa-construction.html">Villa Construction</a></li>
                        <li class="mb-2"><a href="apartment-construction.html">Apartment Construction</a></li>
                        <li class="mb-2"><a href="commercial-building-construction.html">Commercial Building</a></li>
                        <li class="mb-2"><a href="residential-project-development.html">Project Development</a></li>
                        <li class="mb-2"><a href="architectural-structural-design.html">Architectural Design</a></li>
                        <li><a href="turnkey-interior-solutions.html">Turnkey Interiors</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Get in Touch</h5>
                    <div class="footer-contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>2/10, Devi Parasakthi Nagar, Karambakkam, Porur, Chennai 600116</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-phone"></i>
                        <a href="tel:+919941769381" style="color:#94a3b8; text-decoration:none;">+91 99417 69381</a>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:balajimani.2505@gmail.com" style="color:#94a3b8; text-decoration:none;">balajimani.2505@gmail.com</a>
                    </div>
                    <div class="footer-contact-item">
                        <i class="far fa-clock"></i>
                        <span>Mon&ndash;Sat: 8:30 AM &ndash; 7:30 PM</span>
                    </div>
                </div>
            </div>
            <div class="footer-bottom d-flex flex-wrap justify-content-between align-items-center mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.1);">
                <p class="mb-0">&copy; 2026 DreamBrixs Residential Builders. All Rights Reserved. TN/RERA Registered.</p>
                <div>
                    <a href="#">Privacy Policy</a> &nbsp;|&nbsp; <a href="#">Terms of Engagement</a> &nbsp;|&nbsp; <a href="#">RERA Disclosures</a>
                </div>
            </div>
        </div>
    </footer>

    <a href="https://wa.me/919941769381" class="whatsapp-float" target="_blank" rel="noopener noreferrer" aria-label="Chat with DreamBrixs on WhatsApp">
        <span class="whatsapp-label">Chat with us</span>
        <span class="whatsapp-icon-wrap"><i class="fab fa-whatsapp" aria-hidden="true"></i></span>
    </a>
    <button type="button" class="scroll-top" id="scrollTop" aria-label="Scroll to top"><i class="fas fa-arrow-up" aria-hidden="true"></i></button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        if (window.AOS) { 
            AOS.init({ duration: 750, once: true, offset: 40, disable: window.matchMedia('(prefers-reduced-motion: reduce)').matches }); 
        }

        const scrollTopBtn = document.getElementById('scrollTop');
        window.addEventListener('scroll', () => scrollTopBtn.classList.toggle('show', window.pageYOffset > 300), { passive: true });
        scrollTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    </script>
</body>
</html>