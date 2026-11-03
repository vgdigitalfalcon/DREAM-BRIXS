<?php
// DreamBrixs - Project Detail Page
if (file_exists(__DIR__ . '/crm/config.php')) {
    require_once __DIR__ . '/crm/config.php';
}

$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
if (!$slug) {
    header('Location: /projects');
    exit;
}

$project = null;
$images = [];
$features = [];
$extra = [];
$related = [];

// Helper to resolve project image URL safely
function resolveDetailImageUrl($img) {
    if (!$img) {
        return 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200';
    }
    if (preg_match('~^(?:https?:)?//~i', $img) || (strlen($img) > 0 && $img[0] === '/')) {
        return $img;
    }
    return '/crm/uploads/projects/' . $img;
}

// 1. Try fetching from database if $pdo is available
if (!empty($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as cat_slug FROM projects p JOIN categories c ON p.category_id = c.id WHERE p.slug = ?");
        $stmt->execute([$slug]);
        $project = $stmt->fetch();
        if ($project) {
            $imagesStmt = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY is_main DESC, sort_order, id");
            $imagesStmt->execute([$project['id']]);
            $images = $imagesStmt->fetchAll();

            $featuresStmt = $pdo->prepare("SELECT feature_text FROM project_features WHERE project_id = ? ORDER BY sort_order");
            $featuresStmt->execute([$project['id']]);
            $features = $featuresStmt->fetchAll(PDO::FETCH_COLUMN);

            $extra = json_decode($project['extra_data'] ?? '{}', true) ?: [];

            $relatedStmt = $pdo->prepare("SELECT p.*, (SELECT image_path FROM project_images WHERE project_id=p.id LIMIT 1) as img FROM projects p WHERE p.category_id = ? AND p.id != ? LIMIT 3");
            $relatedStmt->execute([$project['category_id'], $project['id']]);
            $related = $relatedStmt->fetchAll();
        }
    } catch (Throwable $e) {
        $project = null;
    }
}

// 2. Fallback project catalog if DB is empty or project not yet in DB
$fallbackProjects = [
    'grand-crest-residence' => [
        'id' => 1,
        'title' => 'The Grand Crest Residence',
        'slug' => 'grand-crest-residence',
        'category_name' => 'Individual House',
        'cat_slug' => 'individual-house',
        'location' => 'Anna Nagar West, Chennai',
        'year' => '2025',
        'status' => 'Completed',
        'built_up_area' => '4,200 sq.ft',
        'plot_size' => '2,400 sq.ft',
        'configuration' => '4 BHK Luxury Independent House',
        'duration' => '14 Months',
        'materials' => 'Tata Tiscon Steel, Ultratech Super Cement, Teakwood Doors, Kohler Fittings, Italian Marble Flooring',
        'testimonial' => '"DreamBrixs delivered our dream home right on schedule with top-tier construction quality and full transparency throughout." — Dr. Ramanathan K.',
        'short_description' => 'A contemporary 4 BHK independent residence featuring smart home automation, open-concept living, and landscaped courtyard planning.',
        'detailed_description' => 'The Grand Crest Residence is a landmark independent home crafted in Anna Nagar West. Designed for multi-generational living, this residence blends Vaastu-compliant architecture with contemporary aesthetics, abundant natural light, and premium engineering standards from foundation to final finishes.',
        'seo_title' => 'The Grand Crest Residence | Luxury House Construction in Chennai',
        'seo_description' => 'Explore The Grand Crest Residence, a 4,200 sq.ft premium independent home built by DreamBrixs in Anna Nagar West, Chennai.',
        'features' => [
            'Vaastu-Compliant East-Facing Layout',
            'Double-Height Living Room with Sky-Light',
            'Custom Modular Kitchen with German Hardware',
            'Rooftop Terrace Garden & Solar Provision',
            'Covered Stilt Parking for 2 Large SUVs',
            'Premium Concealed Plumbing with Pressure Booster'
        ],
        'images' => [
            ['image_path' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200'],
            ['image_path' => 'https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?w=800'],
            ['image_path' => 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=800'],
            ['image_path' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800']
        ],
        'extra_data' => ['bhk' => '4 BHK', 'floors' => 'G + 2 Floors']
    ],
    'emerald-coast-villa' => [
        'id' => 2,
        'title' => 'Emerald Coast Villa',
        'slug' => 'emerald-coast-villa',
        'category_name' => 'Villa Construction',
        'cat_slug' => 'villa-construction',
        'location' => 'ECR Beach Road, Chennai',
        'year' => '2024',
        'status' => 'Completed',
        'built_up_area' => '5,600 sq.ft',
        'plot_size' => '4,800 sq.ft',
        'configuration' => '5 BHK Ultra-Luxury Coastal Villa',
        'duration' => '16 Months',
        'materials' => 'Marine-grade anti-corrosive concrete, double-glazed soundproof glass, imported teak, VRV air conditioning',
        'testimonial' => '"Exceptional execution for a coastal property. The engineering integrity against saline air and moisture is commendable." — S. Jayakumar',
        'short_description' => 'A coastal luxury villa offering panoramic deck views, private plunge pool, and weather-resistant structural engineering.',
        'detailed_description' => 'Located along Chennai’s scenic ECR, Emerald Coast Villa is engineered specifically to withstand coastal salinity while providing resort-like luxury. High ceilings, large glazing panels, and outdoor entertainment decks create an unparalleled living experience.',
        'seo_title' => 'Emerald Coast Villa | ECR Luxury Villa Builders Chennai',
        'seo_description' => 'Discover Emerald Coast Villa, a 5,600 sq.ft bespoke beachside villa built by DreamBrixs along ECR, Chennai.',
        'features' => [
            'Private Plunge Pool with Timber Decking',
            'Marine-Grade Concrete & Specialized Waterproofing',
            'Full Home Automation with Voice & App Control',
            'Private Home Theater Room with Acoustic Treatment',
            'Designer Landscaped Lawns & Gazebo'
        ],
        'images' => [
            ['image_path' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1200'],
            ['image_path' => 'https://images.unsplash.com/photo-1600607687644-c7171b42498b?w=800'],
            ['image_path' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800']
        ],
        'extra_data' => ['bhk' => '5 BHK', 'floors' => 'G + 1 Floor + Terrace']
    ],
    'nexus-prime-corporate-hub' => [
        'id' => 3,
        'title' => 'Nexus Prime Corporate Hub',
        'slug' => 'nexus-prime-corporate-hub',
        'category_name' => 'Commercial Construction',
        'cat_slug' => 'commercial-construction',
        'location' => 'Guindy, Chennai',
        'year' => '2025',
        'status' => 'Ongoing',
        'built_up_area' => '18,500 sq.ft',
        'plot_size' => '7,200 sq.ft',
        'configuration' => 'Commercial Office Complex (B+G+4)',
        'duration' => '20 Months',
        'materials' => 'Post-tensioned RCC slabs, High-performance Saint-Gobain structural glazing, OTIS High-speed elevators',
        'testimonial' => '"DreamBrixs is executing our flagship commercial space with strict timelines and zero compromise on safety." — Nexus Group',
        'short_description' => 'A grade-A commercial office building designed for modern tech companies and corporate headquarters.',
        'detailed_description' => 'Nexus Prime Corporate Hub features column-free flexible floor plates, energy-efficient facade glazing, integrated fire-safety systems, and dedicated basement parking in the heart of Guindy’s commercial district.',
        'seo_title' => 'Nexus Prime Corporate Hub | Commercial Builders Chennai',
        'seo_description' => 'Modern commercial building construction in Guindy, Chennai by DreamBrixs Residential and Commercial Builders.',
        'features' => [
            'Column-Free Open Floor Plates',
            'Energy-Efficient Low-E Glass Facade',
            '100% Power Backup with Dual Generator Sync',
            'Integrated Fire Sprinkler & Hydrant Network',
            'Advanced Multi-Tier Access Control System'
        ],
        'images' => [
            ['image_path' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200'],
            ['image_path' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800'],
            ['image_path' => 'https://images.unsplash.com/photo-1577495508048-b635879837f1?w=800']
        ],
        'extra_data' => ['bhk' => 'Commercial', 'floors' => 'Basement + G + 4 Floors']
    ],
    'zenith-retail-business-plaza' => [
        'id' => 4,
        'title' => 'Zenith Retail & Business Plaza',
        'slug' => 'zenith-retail-business-plaza',
        'category_name' => 'Commercial Construction',
        'cat_slug' => 'commercial-construction',
        'location' => 'Anna Nagar, Chennai',
        'year' => '2024',
        'status' => 'Completed',
        'built_up_area' => '12,000 sq.ft',
        'plot_size' => '5,000 sq.ft',
        'configuration' => 'Retail Showroom & Office Space',
        'duration' => '15 Months',
        'materials' => 'Structural steel frame, Toughened glass storefronts, Granite lobbies, Heavy duty vitrified tiles',
        'testimonial' => '"Modern architecture that instantly attracted anchor retail tenants before completion." — Plaza Promoters',
        'short_description' => 'High-visibility retail and commercial destination designed for high footfall in prime Anna Nagar.',
        'detailed_description' => 'Zenith Retail & Business Plaza was planned for maximum frontage visibility. With expansive clear spans, dedicated service corridors, and premium finishings, it stands as an exemplary commercial build in Chennai.',
        'seo_title' => 'Zenith Retail Plaza | Commercial Building Anna Nagar',
        'seo_description' => 'Explore Zenith Retail Plaza in Anna Nagar, Chennai - high quality commercial building by DreamBrixs.',
        'features' => [
            'Triple-Height Grand Entrance Atrium',
            'Ample Customer Parking with Easy Driveways',
            'Dedicated Signage & Digital Display Facade',
            'Heavy-Duty Freight & Passenger Elevators'
        ],
        'images' => [
            ['image_path' => 'https://images.unsplash.com/photo-1577495508048-b635879837f1?w=1200'],
            ['image_path' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800']
        ],
        'extra_data' => ['bhk' => 'Commercial', 'floors' => 'G + 3 Floors']
    ],
    'horizon-commercial-tower' => [
        'id' => 5,
        'title' => 'Horizon Commercial Tower',
        'slug' => 'horizon-commercial-tower',
        'category_name' => 'Commercial Construction',
        'cat_slug' => 'commercial-construction',
        'location' => 'OMR, Chennai',
        'year' => '2024',
        'status' => 'Completed',
        'built_up_area' => '22,000 sq.ft',
        'plot_size' => '9,000 sq.ft',
        'configuration' => 'IT & Corporate Complex',
        'duration' => '18 Months',
        'materials' => 'Fe 550D TMT, M30 grade concrete, Double glazed facade, Smart BMS system',
        'testimonial' => '"Delivered on budget with world-class finishes. Our tenants love the space." — Horizon Ventures',
        'short_description' => 'A landmark tech workspace with modern amenities and sustainable infrastructure on Chennai IT corridor.',
        'detailed_description' => 'Constructed on the fast-growing Old Mahabalipuram Road, Horizon Tower delivers efficient workspaces designed around wellness, acoustic comfort, and green building guidelines.',
        'seo_title' => 'Horizon Commercial Tower | OMR Chennai Builders',
        'seo_description' => 'Horizon Commercial Tower on OMR, Chennai built with structural precision by DreamBrixs Builders.',
        'features' => [
            'IGBC Green Building Standards Compliant',
            'Multi-Level Smart Parking System',
            'Rooftop Cafeteria & Breakout Zone',
            'Dual Fiber Redundancy Infrastructure'
        ],
        'images' => [
            ['image_path' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1200'],
            ['image_path' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800']
        ],
        'extra_data' => ['bhk' => 'Commercial', 'floors' => 'Stilt + 5 Floors']
    ],
    'sri-krishna-enclave' => [
        'id' => 6,
        'title' => 'Sri Krishna Enclave Stilt+4',
        'slug' => 'sri-krishna-enclave',
        'category_name' => 'Apartment Construction',
        'cat_slug' => 'apartment-construction',
        'location' => 'Velachery, Chennai',
        'year' => '2024',
        'status' => 'Completed',
        'built_up_area' => '9,800 sq.ft',
        'plot_size' => '4,000 sq.ft',
        'configuration' => '8 Premium 3 BHK Apartments (Stilt + 4)',
        'duration' => '15 Months',
        'materials' => 'Coromandel King Cement, ARS TMT, Johnson tiles, Havells concealed wiring, Jaquar sanitaryware',
        'testimonial' => '"All 8 families are delighted with the construction quality and acoustic separation between units." — Residents Association',
        'short_description' => 'An exclusive boutique residential apartment enclave with 8 luxury units and modern amenities in Velachery.',
        'detailed_description' => 'Sri Krishna Enclave offers spacious, cross-ventilated 3 BHK apartments planned with privacy and comfort in mind. Engineered with seismic-resistant RCC frames and premium rainwater harvesting systems.',
        'seo_title' => 'Sri Krishna Enclave | Apartment Construction Velachery Chennai',
        'seo_description' => 'Sri Krishna Enclave, a luxury stilt + 4 boutique apartment community built by DreamBrixs in Velachery, Chennai.',
        'features' => [
            'Only 2 Units Per Floor for Maximum Privacy',
            '6-Passenger Automatic Lift with ARD',
            'Dedicated Covered Stilt Car Parking',
            'CCTV Surveillance and Video Door Phones',
            'Continuous 24/7 Water Supply with Softener'
        ],
        'images' => [
            ['image_path' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=1200'],
            ['image_path' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800']
        ],
        'extra_data' => ['bhk' => '3 BHK (8 Units)', 'floors' => 'Stilt + 4 Floors']
    ],
    'mayflower-luxury-duplex' => [
        'id' => 7,
        'title' => 'Mayflower Luxury Duplex',
        'slug' => 'mayflower-luxury-duplex',
        'category_name' => 'Individual House',
        'cat_slug' => 'individual-house',
        'location' => 'Kotturpuram, Chennai',
        'year' => '2024',
        'status' => 'Completed',
        'built_up_area' => '3,800 sq.ft',
        'plot_size' => '2,200 sq.ft',
        'configuration' => '4 BHK Contemporary Duplex',
        'duration' => '13 Months',
        'materials' => 'RCC Framed structure, engineered stone cladding, Burma teakwood woodwork, Schneider automation',
        'testimonial' => '"The attention to detail and space optimization in a central Chennai plot was outstanding." — Mr. & Mrs. Murali',
        'short_description' => 'A sophisticated urban duplex designed with double-height central voids and seamless internal flow.',
        'detailed_description' => 'Mayflower Duplex exemplifies modern city living in Kotturpuram. Built on an urban plot, vertical volume was maximized with an internal courtyard and skylight that floods the entire home with daylight.',
        'seo_title' => 'Mayflower Luxury Duplex | Builders in Kotturpuram Chennai',
        'seo_description' => 'Mayflower Luxury Duplex residence built by DreamBrixs in Kotturpuram, Chennai.',
        'features' => [
            'Central Sky-lit Indoor Green Courtyard',
            'Designer Cantilevered Staircase with Glass Railing',
            'Walk-in Closets and En-Suite Bathrooms in All Bedrooms',
            'Sound-Insulated Master Suite with Private Balcony'
        ],
        'images' => [
            ['image_path' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200'],
            ['image_path' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800']
        ],
        'extra_data' => ['bhk' => '4 BHK', 'floors' => 'G + 1 Floor + Terrace']
    ],
    'serenity-bay-beach-villa' => [
        'id' => 8,
        'title' => 'Serenity Bay Beach Villa',
        'slug' => 'serenity-bay-beach-villa',
        'category_name' => 'Villa Construction',
        'cat_slug' => 'villa-construction',
        'location' => 'Besant Nagar, Chennai',
        'year' => '2023',
        'status' => 'Completed',
        'built_up_area' => '4,900 sq.ft',
        'plot_size' => '3,600 sq.ft',
        'configuration' => '4 BHK Luxury Coastal Villa',
        'duration' => '14 Months',
        'materials' => 'Corrosion-resistant steel, water-repellent exterior silicon paint, Italian travertine, double insulated glass',
        'testimonial' => '"Our beach home is our sanctuary. The breeze, natural cooling, and finishes are sublime." — K. Anand',
        'short_description' => 'An airy, modern coastal villa capturing sea breezes and natural daylight just steps from the shore.',
        'detailed_description' => 'Serenity Bay Villa is oriented to harness cross-ventilation from the Bay of Bengal while shielding interiors from direct harsh tropical heat. Features cantilevered pergolas and extensive terrace decks.',
        'seo_title' => 'Serenity Bay Beach Villa | Besant Nagar Chennai',
        'seo_description' => 'Serenity Bay Beach Villa built by DreamBrixs in Besant Nagar, Chennai.',
        'features' => [
            'Cross-Ventilated Open Concept Living & Dining',
            'Terrace Jacuzzi Deck with Sea View',
            'Full Anti-Corrosive Structural Treatment',
            'Landscaped Tropical Garden with Drip Irrigation'
        ],
        'images' => [
            ['image_path' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1200'],
            ['image_path' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800']
        ],
        'extra_data' => ['bhk' => '4 BHK', 'floors' => 'G + 2 Floors']
    ],
    'aura-minimalist-penthouse' => [
        'id' => 9,
        'title' => 'Aura Minimalist Penthouse',
        'slug' => 'aura-minimalist-penthouse',
        'category_name' => 'Turnkey Interiors',
        'cat_slug' => 'turnkey-interiors',
        'location' => 'Boat Club Road, Chennai',
        'year' => '2024',
        'status' => 'Completed',
        'built_up_area' => '3,400 sq.ft',
        'plot_size' => 'Penthouse Level',
        'configuration' => '3 BHK Turnkey Luxury Interior',
        'duration' => '5 Months',
        'materials' => 'Fluted oak wall paneling, Italian micro-cement, custom brass hardware, smart ambient lighting',
        'testimonial' => '"Executed with precision and minimalist elegance. Truly world-class interior craftsmanship." — Priya Sundaram',
        'short_description' => 'A refined turnkey interior project showcasing minimalist aesthetics, warm woods, and concealed storage.',
        'detailed_description' => 'Aura Penthouse is a complete turnkey transformation. From custom cabinetry and lighting design to acoustics and automated blinds, every element was tailored for serene luxury living on prestigious Boat Club Road.',
        'seo_title' => 'Aura Minimalist Penthouse | Turnkey Interiors Chennai',
        'seo_description' => 'Aura Minimalist Penthouse turnkey interior design and execution by DreamBrixs, Chennai.',
        'features' => [
            'Custom Fluted Wood & Micro-Cement Wall Features',
            'Concealed Magnetic Track Architectural Lighting',
            'Full Smart Home Scene Controller & Motorized Drapes',
            'Bespoke Italian Kitchen with Quartz Island Counter'
        ],
        'images' => [
            ['image_path' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1200'],
            ['image_path' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800']
        ],
        'extra_data' => ['bhk' => '3 BHK Penthouse', 'floors' => 'Penthouse Floor']
    ]
];

if (!$project && isset($fallbackProjects[$slug])) {
    $fb = $fallbackProjects[$slug];
    $project = [
        'id' => $fb['id'],
        'title' => $fb['title'],
        'slug' => $fb['slug'],
        'category_name' => $fb['category_name'],
        'cat_slug' => $fb['cat_slug'],
        'category_id' => $fb['id'],
        'location' => $fb['location'],
        'year' => $fb['year'],
        'status' => $fb['status'],
        'built_up_area' => $fb['built_up_area'],
        'plot_size' => $fb['plot_size'],
        'configuration' => $fb['configuration'],
        'duration' => $fb['duration'],
        'materials' => $fb['materials'],
        'testimonial' => $fb['testimonial'],
        'short_description' => $fb['short_description'],
        'detailed_description' => $fb['detailed_description'],
        'seo_title' => $fb['seo_title'],
        'seo_description' => $fb['seo_description'],
        'extra_data' => json_encode($fb['extra_data'])
    ];
    $images = $fb['images'];
    $features = $fb['features'];
    $extra = $fb['extra_data'];

    // Pick 3 related fallback projects
    $related = [];
    foreach ($fallbackProjects as $s => $fbp) {
        if ($s !== $slug && count($related) < 3) {
            $related[] = [
                'title' => $fbp['title'],
                'slug' => $fbp['slug'],
                'year' => $fbp['year'],
                'img' => $fbp['images'][0]['image_path'] ?? ''
            ];
        }
    }
}

if (!$project) {
    header('Location: /projects');
    exit;
}

$mainImage = !empty($images) ? resolveDetailImageUrl($images[0]['image_path']) : 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200';
$imagePaths = array_map(fn($img) => resolveDetailImageUrl($img['image_path']), $images);

function formatSafe($text) {
    if (!$text) return '';
    $text = str_replace('#', '', (string)$text);
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/\*([^*]+)\*/', '<strong>$1</strong>', $text);
    return nl2br($text);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($project['seo_title'] ?: $project['title']) ?> | DreamBrixs</title>
    <meta name="description" content="<?= htmlspecialchars($project['seo_description'] ?: $project['short_description']) ?>">
    
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
        section:not(.detail-hero) { position: relative; overflow: hidden; }
        section:not(.detail-hero) > .container { position: relative; z-index: 1; }
        .rotating-circle {
            position: absolute; border: 2px dashed var(--accent-yellow); border-radius: 50%;
            opacity: 0.3; animation: spin 22s linear infinite; pointer-events: none; z-index: 0;
            max-width: 42vw; max-height: 42vw;
        }
        .detail-hero {
            min-height: 48vh;
            background: linear-gradient(115deg, rgba(10,22,40,0.88) 0%, rgba(10,22,40,0.55) 50%, rgba(10,22,40,0.78) 100%),
                        url('<?= htmlspecialchars($mainImage) ?>') center/cover no-repeat;
            display: flex; align-items: flex-end; color: #fff; position: relative; overflow: hidden; padding: 110px 0 45px;
        }
        .detail-hero h1 { font-size: 40px; font-weight: 800; line-height: 1.15; }
        .hero-badge-pill {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(245,184,0,0.16); border: 1px solid rgba(245,184,0,0.45);
            color: var(--accent-yellow); padding: 6px 16px; border-radius: 999px;
            font-size: 12.5px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase;
        }
        .breadcrumb { background: transparent; margin-bottom: 10px; }
        .breadcrumb-item a { color: rgba(255,255,255,0.85); text-decoration: none; }
        .breadcrumb-item a:hover { color: var(--accent-yellow); }
        .breadcrumb-item.active { color: #fff; }
        .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,0.55); }
        .gallery-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 12px; }
        .gallery-main {
            border-radius: 18px; overflow: hidden; height: 440px; position: relative; cursor: pointer;
            box-shadow: 0 12px 36px rgba(0,0,0,0.1);
        }
        .gallery-main img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
        .gallery-main:hover img { transform: scale(1.04); }
        .gallery-side { display: flex; flex-direction: column; gap: 12px; }
        .gallery-side .thumb-wrap {
            flex: 1; border-radius: 14px; overflow: hidden; cursor: pointer; position: relative;
            min-height: 140px; box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        .gallery-side .thumb-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
        .gallery-side .thumb-wrap:hover img { transform: scale(1.06); }
        .view-more-overlay {
            position: absolute; inset: 0; background: rgba(10,22,40,0.55); color: #fff;
            display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;
            opacity: 0; transition: opacity 0.3s;
        }
        .thumb-wrap:hover .view-more-overlay { opacity: 1; }
        .content-card {
            background: #fff; border-radius: 16px; padding: 26px 24px;
            box-shadow: 0 8px 28px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;
            margin-bottom: 20px; transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .content-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(245,184,0,0.1); }
        .features-list { list-style: none; padding: 0; margin: 0; }
        .features-list li {
            padding: 9px 0; border-bottom: 1px dashed #eee; display: flex; align-items: flex-start; gap: 10px;
        }
        .features-list li:last-child { border-bottom: none; }
        .features-list li i { color: var(--accent-yellow); margin-top: 2px; }
        .sidebar-card {
            background: #fff; border-radius: 16px; padding: 22px 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #eee;
            position: sticky; top: 100px;
        }
        .detail-table { width: 100%; font-size: 14.5px; }
        .detail-table td { padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
        .detail-table td:first-child { color: var(--text-muted); width: 45%; }
        .status-badge { font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 20px; }
        .status-completed { background: #e8f5e9; color: #2e7d32; }
        .status-ongoing { background: #fff8e1; color: #f57c00; }
        .related-card {
            display: flex; gap: 12px; align-items: center; padding: 10px 0;
            border-bottom: 1px solid #f0f0f0; text-decoration: none; color: inherit;
            transition: all 0.3s ease;
        }
        .related-card:hover { transform: translateX(5px); }
        .related-card:last-child { border-bottom: none; }
        .related-card img { width: 70px; height: 52px; object-fit: cover; border-radius: 8px; }
        .lightbox {
            position: fixed; inset: 0; background: rgba(10,22,40,0.92); z-index: 2000;
            display: none; align-items: center; justify-content: center; padding: 20px;
        }
        .lightbox.show { display: flex; }
        .lightbox img { max-width: 90vw; max-height: 85vh; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,0.4); }
        .lightbox-close, .lightbox-prev, .lightbox-next {
            position: absolute; background: rgba(255,255,255,0.15); border: none; color: #fff;
            width: 48px; height: 48px; border-radius: 50%; font-size: 20px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; transition: background 0.2s;
        }
        .lightbox-close:hover, .lightbox-prev:hover, .lightbox-next:hover { background: var(--accent-yellow); color: #000; }
        .lightbox-close { top: 20px; right: 20px; }
        .lightbox-prev { left: 20px; top: 50%; transform: translateY(-50%); }
        .lightbox-next { right: 20px; top: 50%; transform: translateY(-50%); }
        .lightbox-counter { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); color: #fff; font-size: 14px; }
        .footer-fresh { background: #070d18; color: #bbb; padding: 70px 0 25px; overflow: hidden; }
        .footer-fresh h5 { color: #fff; font-weight: 600; margin-bottom: 20px; }
        .footer-fresh a { color: #bbb; text-decoration: none; }
        .footer-fresh a:hover { color: var(--accent-yellow); }
        .footer-fresh ul li { position: relative; padding-left: 16px; }
        .footer-fresh ul li::before { content: '•'; position: absolute; left: 0; color: var(--accent-yellow); }
        .footer-logo { height: 48px; margin-bottom: 14px; }
        .footer-social a { width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.08); display: inline-flex; align-items: center; justify-content: center; margin-right: 8px; transition: 0.3s; text-decoration: none; }
        .footer-social a:hover { background: var(--accent-yellow); color: #000; }
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
            .detail-hero { min-height: 40vh; padding: 90px 0 40px; }
            .detail-hero h1 { font-size: clamp(28px, 5vw, 36px) !important; }
            .sidebar-card { position: static; margin-top: 24px; }
            .gallery-grid { grid-template-columns: 1fr; }
            .gallery-main { height: 320px; }
            .gallery-side { flex-direction: row; }
            .gallery-side .thumb-wrap { min-height: 120px; }
        }
        @media (max-width: 767.98px) {
            body { font-size: 15px; }
            p { text-align: left; }
            .top-bar { padding: 8px 0; font-size: 11px; }
            .top-bar .container { flex-direction: column; text-align: center; gap: 5px; }
            .navbar { padding: 9px 0; }
            .navbar-brand img { height: 43px; max-width: 165px; }
            .detail-hero { min-height: auto; padding: 80px 0 30px; }
            .detail-hero h1 { font-size: clamp(24px, 7vw, 30px) !important; }
            .gallery-main { height: 240px; }
            .gallery-side { flex-direction: column; }
            .whatsapp-float { right: 14px; bottom: 14px; width: 56px; min-width: 56px; height: 56px; padding: 5px; }
            .whatsapp-label { display: none; }
            .scroll-top { right: 19px; bottom: 82px; width: 42px; height: 42px; }
            .content-card { padding: 18px 16px; }
            .lightbox-prev, .lightbox-next { width: 40px; height: 40px; }
        }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
            .live-float, .whatsapp-float, .whatsapp-float::before, .rotating-circle { animation: none !important; }
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
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
                    <li class="nav-item"><a class="nav-link active" href="/projects">Projects</a></li>
                    <li class="nav-item"><a class="nav-link" href="/blog">Blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="/contact.html">Contact Us</a></li>
                </ul>
                <a href="/contact.html" class="btn btn-consult">GET A FREE CONSULTATION</a>
            </div>
        </div>
    </nav>
    <!-- Hero -->
    <section class="detail-hero">
        <div class="container" data-aos="fade-up">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/projects">Projects</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($project['title']) ?></li>
                </ol>
            </nav>
            <div class="hero-badge-pill mb-2">
                <i class="fas fa-building"></i> <?= htmlspecialchars($project['category_name']) ?>
            </div>
            <h1 class="live-float mb-2"><?= htmlspecialchars($project['title']) ?></h1>
            <p class="mb-0" style="opacity:0.9; font-size:15px;">
                <?= htmlspecialchars($project['location'] ?? '') ?> • <?= htmlspecialchars($project['year'] ?? '') ?>
                <span class="status-badge <?= $project['status']=='Completed' ? 'status-completed' : 'status-ongoing' ?> ms-2"><?= htmlspecialchars($project['status']) ?></span>
            </p>
        </div>
    </section>
    <!-- Main -->
    <section class="py-5" style="background:var(--soft-bg);">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <?php if ($images): ?>
                    <div class="gallery-grid" data-aos="fade-up">
                        <div class="gallery-main" onclick="openLightbox(0)">
                            <img src="<?= htmlspecialchars(resolveDetailImageUrl($images[0]['image_path'])) ?>" alt="<?= htmlspecialchars($project['title']) ?>">
                        </div>
                        <div class="gallery-side">
                            <?php 
                            $sideImages = array_slice($images, 1, 3);
                            foreach ($sideImages as $idx => $img): 
                                $realIndex = $idx + 1;
                            ?>
                            <div class="thumb-wrap" onclick="openLightbox(<?= $realIndex ?>)">
                                <img src="<?= htmlspecialchars(resolveDetailImageUrl($img['image_path'])) ?>" alt="View">
                                <div class="view-more-overlay">View</div>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($images) > 4): ?>
                            <div class="thumb-wrap" onclick="openLightbox(0)" style="background:#0a1628; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:600;">
                                +<?= count($images) - 4 ?> more
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="content-card mt-4" data-aos="fade-up">
                        <h4 class="mb-3">About This Project</h4>
                        <div style="line-height:1.75; font-size:15.5px;">
                            <?= formatSafe($project['detailed_description'] ?? $project['short_description'] ?? '') ?>
                        </div>
                    </div>
                    <?php if ($features): ?>
                    <div class="content-card" data-aos="fade-up">
                        <h4 class="mb-3">Key Features & Amenities</h4>
                        <ul class="features-list">
                            <?php foreach ($features as $f): ?>
                            <li><i class="fas fa-check-circle"></i> <?= formatSafe($f) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($project['materials'])): ?>
                    <div class="content-card" data-aos="fade-up">
                        <h4 class="mb-3">Materials & Specifications</h4>
                        <p class="mb-0" style="line-height:1.75;"><?= formatSafe($project['materials']) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($project['testimonial'])): ?>
                    <div class="content-card" data-aos="fade-up" style="border-left: 4px solid var(--accent-yellow);">
                        <h5 class="mb-2"><i class="fas fa-quote-left me-2" style="color:var(--accent-yellow);"></i>Client Feedback</h5>
                        <p class="mb-0" style="font-style:italic; line-height:1.7;"><?= formatSafe($project['testimonial']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-4">
                    <div class="sidebar-card" data-aos="fade-left">
                        <h5 class="mb-3">Project Details</h5>
                        <table class="detail-table">
                            <tr><td>Category</td><td><?= htmlspecialchars($project['category_name']) ?></td></tr>
                            <tr><td>Location</td><td><?= htmlspecialchars($project['location'] ?? '-') ?></td></tr>
                            <tr><td>Year</td><td><?= htmlspecialchars($project['year'] ?? '-') ?></td></tr>
                            <tr><td>Status</td><td><span class="status-badge <?= $project['status']=='Completed' ? 'status-completed' : 'status-ongoing' ?>"><?= htmlspecialchars($project['status']) ?></span></td></tr>
                            <?php if (!empty($project['built_up_area'])): ?><tr><td>Built-up</td><td><?= htmlspecialchars($project['built_up_area']) ?></td></tr><?php endif; ?>
                            <?php if (!empty($project['plot_size'])): ?><tr><td>Plot Size</td><td><?= htmlspecialchars($project['plot_size']) ?></td></tr><?php endif; ?>
                            <?php if (!empty($project['configuration'])): ?><tr><td>Configuration</td><td><?= htmlspecialchars($project['configuration']) ?></td></tr><?php endif; ?>
                            <?php if (!empty($project['duration'])): ?><tr><td>Duration</td><td><?= htmlspecialchars($project['duration']) ?></td></tr><?php endif; ?>
                            <?php if (!empty($extra['bhk'])): ?><tr><td>BHK</td><td><?= htmlspecialchars($extra['bhk']) ?></td></tr><?php endif; ?>
                            <?php if (!empty($extra['floors'])): ?><tr><td>Floors</td><td><?= htmlspecialchars($extra['floors']) ?></td></tr><?php endif; ?>
                        </table>
                        <a href="https://wa.me/919941769381?text=Hi,%20I%20am%20interested%20in%20the%20project:%20<?= urlencode($project['title']) ?>" 
                           class="btn btn-consult w-100 mt-4" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-whatsapp me-1"></i> Enquire on WhatsApp
                        </a>
                        <a href="/contact.html" class="btn btn-outline-secondary w-100 mt-2" style="border-radius:999px;">Request Similar Project</a>
                    </div>
                    <?php if ($related): ?>
                    <div class="content-card mt-4" data-aos="fade-up">
                        <h5 class="mb-3">Related Projects</h5>
                        <?php foreach ($related as $r): ?>
                        <a href="/projects/<?= htmlspecialchars($r['slug']) ?>" class="related-card">
                            <?php if (!empty($r['img'])): ?>
                            <img src="<?= htmlspecialchars(resolveDetailImageUrl($r['img'])) ?>" alt="<?= htmlspecialchars($r['title']) ?>">
                            <?php else: ?>
                            <div style="width:70px;height:52px;background:#f0f0f0;border-radius:8px;"></div>
                            <?php endif; ?>
                            <div>
                                <div class="fw-semibold small"><?= htmlspecialchars($r['title']) ?></div>
                                <div class="text-muted" style="font-size:12px;"><?= htmlspecialchars($r['year'] ?? '') ?></div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <!-- CTA -->
    <section class="py-5" style="background:linear-gradient(135deg,#0a1628,#122036);color:#fff;position:relative;overflow:hidden;">
        <div class="rotating-circle" style="width:180px;height:180px;bottom:-40px;left:-40px;opacity:0.25;"></div>
        <div class="container text-center" data-aos="fade-up">
            <h2 class="fw-bold mb-3">Inspired by This Project?</h2>
            <p class="mb-4" style="opacity:0.9;max-width:560px;margin:0 auto;text-align:center;">Let’s discuss your dream home or residential project in Chennai.</p>
            <a href="/contact.html" class="btn btn-consult me-2">Get a Free Consultation</a>
            <a href="/projects" class="btn btn-outline-light">View All Projects</a>
        </div>
    </section>
    <!-- Footer -->
    <footer class="footer-fresh">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <img src="/image/dreambrixs.png" alt="DreamBrixs" class="footer-logo">
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
                        <li class="mb-2"><a href="/home.html">Home</a></li>
                        <li class="mb-2"><a href="/about.html">About Us</a></li>
                        <li class="mb-2"><a href="/service.html">Services</a></li>
                        <li class="mb-2"><a href="/projects">Projects</a></li>
                        <li class="mb-2"><a href="/blog">Blog</a></li>
                        <li><a href="/contact.html">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-12 col-lg-3 mb-4">
                    <h5>Services</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/individual-house-construction.html">Individual House</a></li>
                        <li class="mb-2"><a href="/villa-construction.html">Villa Construction</a></li>
                        <li class="mb-2"><a href="/apartment-construction.html">Apartment Construction</a></li>
                        <li class="mb-2"><a href="/commercial-building-construction.html">Commercial Building</a></li>
                        <li class="mb-2"><a href="/residential-project-development.html">Project Development</a></li>
                        <li class="mb-2"><a href="/architectural-structural-design.html">Architectural Design</a></li>
                        <li><a href="/turnkey-interior-solutions.html">Turnkey Interiors</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Get in Touch</h5>
                    <p class="small mb-2"><i class="fas fa-map-marker-alt me-2" style="color:var(--accent-yellow);"></i> 2/10, Devi Parasakthi Nagar, Karambakkam, Porur, Chennai 600116</p>
                    <p class="small mb-2"><i class="fas fa-phone me-2" style="color:var(--accent-yellow);"></i> +91 99417 69381</p>
                    <p class="small mb-2"><i class="fas fa-envelope me-2" style="color:var(--accent-yellow);"></i> balajimani.2505@gmail.com</p>
                    <p class="small"><i class="far fa-clock me-2" style="color:var(--accent-yellow);"></i> Mon–Sat: 8:30 AM – 7:30 PM</p>
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
    <!-- Lightbox -->
    <div class="lightbox" id="lightbox">
        <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
        <button class="lightbox-prev" onclick="changeLightbox(-1)"><i class="fas fa-chevron-left"></i></button>
        <img id="lightboxImg" src="" alt="Project Image">
        <button class="lightbox-next" onclick="changeLightbox(1)"><i class="fas fa-chevron-right"></i></button>
        <div class="lightbox-counter" id="lightboxCounter"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        if (window.AOS) { 
            AOS.init({ duration: 750, once: true, offset: 40, disable: window.matchMedia('(prefers-reduced-motion: reduce)').matches }); 
        }
        const scrollTopBtn = document.getElementById('scrollTop');
        window.addEventListener('scroll', () => scrollTopBtn.classList.toggle('show', window.pageYOffset > 300), { passive: true });
        scrollTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        // Lightbox
        const images = <?= json_encode($imagePaths) ?>;
        let currentIndex = 0;
        function openLightbox(index) {
            if (!images.length) return;
            currentIndex = index;
            updateLightbox();
            document.getElementById('lightbox').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('show');
            document.body.style.overflow = '';
        }
        function changeLightbox(dir) {
            currentIndex = (currentIndex + dir + images.length) % images.length;
            updateLightbox();
        }
        function updateLightbox() {
            document.getElementById('lightboxImg').src = images[currentIndex];
            document.getElementById('lightboxCounter').textContent = (currentIndex + 1) + ' / ' + images.length;
        }
        document.addEventListener('keydown', (e) => {
            const lb = document.getElementById('lightbox');
            if (!lb.classList.contains('show')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') changeLightbox(-1);
            if (e.key === 'ArrowRight') changeLightbox(1);
        });
        document.getElementById('lightbox').addEventListener('click', (e) => {
            if (e.target.id === 'lightbox') closeLightbox();
        });
    </script>
</body>
</html>
