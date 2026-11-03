-- DreamBrixs Projects CRM Database Schema
-- Database: u670120666_dreambrixs_crm

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin: username = admin, password = admin123 (change after first login)
INSERT INTO admins (username, password_hash, name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator')
ON DUPLICATE KEY UPDATE username = username;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    sort_order INT DEFAULT 0
);

INSERT INTO categories (name, slug, sort_order) VALUES
('Individual House Construction', 'individual-house', 1),
('Villa Construction', 'villa', 2),
('Apartment Construction', 'apartment', 3),
('Residential Project Development', 'residential-development', 4),
('Architectural & Structural Design', 'architectural-design', 5),
('Turnkey Interior Solutions', 'interiors', 6)
ON DUPLICATE KEY UPDATE name = VALUES(name);

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    location VARCHAR(150),
    year YEAR,
    status ENUM('Completed', 'Ongoing') DEFAULT 'Completed',
    short_description TEXT,
    detailed_description LONGTEXT,
    built_up_area VARCHAR(50),
    plot_size VARCHAR(50),
    configuration VARCHAR(150),
    duration VARCHAR(50),
    materials TEXT,
    testimonial TEXT,
    extra_data JSON,
    seo_title VARCHAR(255),
    seo_description TEXT,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS project_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_main TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    alt_text VARCHAR(255),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS project_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    feature_text VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Indexes
CREATE INDEX idx_projects_category ON projects(category_id);
CREATE INDEX idx_projects_status ON projects(status);
CREATE INDEX idx_projects_year ON projects(year);
CREATE INDEX idx_projects_slug ON projects(slug);
CREATE INDEX idx_images_project ON project_images(project_id);
