-- ============================================================
-- Dream Catcher Car Accessories Blog CRM Database Schema
-- PHP + MySQL | SEO + Generative Engine Optimization (GEO)
-- Theme: Primary Red (#c00000) + Black + White
-- ============================================================

CREATE DATABASE IF NOT EXISTS u670120666_dcblogs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE u670120666_dcblogs;

-- Users table (Admin / Authors) — E-E-A-T signals
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'editor', 'author') DEFAULT 'author',
    avatar VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    expertise VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categories
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT,
    meta_title VARCHAR(70) DEFAULT NULL,
    meta_description VARCHAR(160) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    icon VARCHAR(50) DEFAULT NULL,
    parent_id INT UNSIGNED DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Posts / Blogs  (SEO + Generative Engine Optimization)
CREATE TABLE posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    excerpt TEXT,
    
    -- Classic SEO
    meta_title VARCHAR(70) DEFAULT NULL,
    meta_description VARCHAR(160) DEFAULT NULL,
    meta_keywords VARCHAR(255) DEFAULT NULL,
    focus_keyword VARCHAR(100) DEFAULT NULL,
    canonical_url VARCHAR(500) DEFAULT NULL,
    
    -- Images
    featured_image VARCHAR(255) DEFAULT NULL,
    featured_image_alt VARCHAR(255) DEFAULT NULL,
    social_image VARCHAR(255) DEFAULT NULL,
    social_image_alt VARCHAR(255) DEFAULT NULL,
    
    -- Classification
    category_id INT UNSIGNED DEFAULT NULL,
    author_id INT UNSIGNED NOT NULL,
    
    -- Status & Features
    status ENUM('draft', 'published', 'scheduled', 'archived') DEFAULT 'draft',
    is_featured TINYINT(1) DEFAULT 0,
    is_sticky TINYINT(1) DEFAULT 0,
    
    -- Reading & Stats
    reading_time INT UNSIGNED DEFAULT 0,
    view_count INT UNSIGNED DEFAULT 0,
    
    -- ========== GENERATIVE ENGINE OPTIMIZATION (GEO) ==========
    key_takeaways TEXT DEFAULT NULL,
    ai_summary TEXT DEFAULT NULL,
    faq_json JSON DEFAULT NULL,
    schema_type VARCHAR(50) DEFAULT 'BlogPosting',
    sources TEXT DEFAULT NULL,
    -- ==========================================================
    
    -- Publishing
    published_at DATETIME DEFAULT NULL,
    scheduled_at DATETIME DEFAULT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    INDEX idx_published (published_at),
    INDEX idx_slug (slug),
    FULLTEXT INDEX ft_search (title, content, excerpt, meta_keywords, key_takeaways, ai_summary)
) ENGINE=InnoDB;

-- Post Images (gallery / additional images with ALT)
CREATE TABLE post_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    caption VARCHAR(500) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tags
CREATE TABLE tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(60) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Post Tags pivot
CREATE TABLE post_tags (
    post_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Media Library
CREATE TABLE media (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    width INT UNSIGNED DEFAULT NULL,
    height INT UNSIGNED DEFAULT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    title VARCHAR(255) DEFAULT NULL,
    uploaded_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Settings
CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Activity log
CREATE TABLE activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) DEFAULT NULL,
    entity_id INT UNSIGNED DEFAULT NULL,
    details TEXT,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Default admin (password: Admin@123)
INSERT INTO users (username, email, password, full_name, role, bio, expertise) VALUES
('admin', 'admin@dreamcatcher.in', '$2y$12$OlqNrb60CijEiibftW2tZeqo8Lqt2vMs/ci8wKb.G0j9XtOmK0LZi', 'Dream Catcher Team', 'admin',
 'Experts in premium car accessories, interior customization, audio upgrades and detailing with over 15 years of experience.', 'Car Accessories & Customization Specialist');

-- Categories matching the Blog designs
INSERT INTO categories (name, slug, description, icon, sort_order) VALUES
('Car Care', 'car-care', 'Tips and guides for car care and maintenance', 'fa-shield-halved', 1),
('Interior', 'interior', 'Premium interior upgrades, seats, lighting and cabin customization', 'fa-couch', 2),
('Exterior', 'exterior', 'Exterior styling, body kits, wraps and appearance upgrades', 'fa-car', 3),
('Audio Systems', 'audio-systems', 'Car audio, speakers, amplifiers and sound system upgrades', 'fa-volume-high', 4),
('Wheels & Tyres', 'wheels-tyres', 'Alloy wheels, tyres, maintenance and performance tips', 'fa-circle', 5),
('Lighting', 'lighting', 'LED, halogen, ambient lighting and headlight upgrades', 'fa-lightbulb', 6),
('PPF & Ceramic', 'ppf-ceramic', 'Paint Protection Film, ceramic coating and paint protection', 'fa-spray-can-sparkles', 7),
('Customization', 'customization', 'Full vehicle customization and personalization projects', 'fa-gears', 8);

-- Default tags
INSERT INTO tags (name, slug) VALUES
('Car Care', 'car-care'),
('Detailing', 'detailing'),
('Interior', 'interior'),
('Exterior', 'exterior'),
('Audio System', 'audio-system'),
('Lighting', 'lighting'),
('Wheels', 'wheels'),
('PPF', 'ppf'),
('Ceramic Coating', 'ceramic-coating'),
('Customization', 'customization'),
('Maintenance', 'maintenance'),
('Tips', 'tips'),
('Luxury', 'luxury'),
('Comfort', 'comfort'),
('Car Upgrade', 'car-upgrade'),
('Car Accessories', 'car-accessories');

-- Default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Dream Catcher Car Accessories'),
('site_url', 'https://www.dreamcatcher.in'),
('blog_title', 'Insights. Inspiration. Innovation.'),
('blog_description', 'Expert tips, trends and guides to keep your car looking its best. Stay updated with the latest in car care, customization and technology.'),
('default_meta_title', 'Dream Catcher Blog | Car Care, Customization & Accessories Tips'),
('default_meta_description', 'Expert articles on interior upgrades, audio systems, lighting, wheels, PPF, ceramic coating and car care from Dream Catcher Car Accessories.'),
('robots_txt', 'User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /blog-crm/admin/\n\n# Allow major AI / Generative Search crawlers (GEO)\nUser-agent: GPTBot\nAllow: /\n\nUser-agent: ChatGPT-User\nAllow: /\n\nUser-agent: Google-Extended\nAllow: /\n\nUser-agent: ClaudeBot\nAllow: /\n\nUser-agent: anthropic-ai\nAllow: /\n\nUser-agent: PerplexityBot\nAllow: /\n\nUser-agent: Bytespider\nAllow: /\n\nUser-agent: CCBot\nAllow: /\n\nSitemap: https://www.dreamcatcher.in/sitemap.xml'),
('sitemap_enabled', '1'),
('posts_per_page', '6'),
('company_address', 'No 9, 10 & 11, Arumurgan Tower, 200 Feet Radial Road, Keelakattalai, Chennai - 600117'),
('company_phone', '+91 98846 66155'),
('company_phone_2', '+91 95661 26666'),
('company_email', 'dreamcatchergk@gmail.com'),
('company_name', 'Dream Catcher Car Accessories'),
('company_hours', 'Mon - Sun: 10:00 AM - 9:30 PM'),
('twitter_handle', '@dreamcatcher.cars'),
('facebook_url', 'https://www.facebook.com/dreamcatcher.cars'),
('instagram_url', 'https://www.instagram.com/dreamcatcher.cars'),
('youtube_url', 'https://www.youtube.com/@dreamcatcher.cars');
