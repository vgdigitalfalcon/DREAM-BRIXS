-- DreamBrixs Blog Schema Updates
-- Run this after importing the base schema to rebrand categories and settings

UPDATE settings SET setting_value = 'DreamBrixs' WHERE setting_key = 'site_name';
UPDATE settings SET setting_value = 'https://www.dreambrixs.com' WHERE setting_key = 'site_url';
UPDATE settings SET setting_value = 'Insights on Homes & Interiors' WHERE setting_key = 'blog_title';
UPDATE settings SET setting_value = 'Expert guides on residential construction, architectural design, turnkey interiors, and home building in Chennai.' WHERE setting_key = 'blog_description';
UPDATE settings SET setting_value = 'DreamBrixs Blog | Home Construction, Design & Interior Tips' WHERE setting_key = 'default_meta_title';
UPDATE settings SET setting_value = 'Expert articles on individual houses, villas, apartments, architectural design, and turnkey interiors from DreamBrixs Residential Builders, Chennai.' WHERE setting_key = 'default_meta_description';
UPDATE settings SET setting_value = 'DreamBrixs Residential Builders' WHERE setting_key = 'company_name';
UPDATE settings SET setting_value = 'contact@dreambrixs.com' WHERE setting_key = 'company_email';
UPDATE settings SET setting_value = '+91 98400 00000' WHERE setting_key = 'company_phone';

-- Replace categories with DreamBrixs-relevant ones
TRUNCATE TABLE categories;
INSERT INTO categories (name, slug, description, icon, sort_order, is_active) VALUES
('Individual House', 'individual-house', 'Guides on building individual houses in Chennai', 'fa-home', 1, 1),
('Villa Construction', 'villa-construction', 'Luxury villa design and construction tips', 'fa-building', 2, 1),
('Apartment Construction', 'apartment-construction', 'Multi-family and apartment project insights', 'fa-city', 3, 1),
('Architectural Design', 'architectural-design', 'Floor plans, 3D elevations, and structural design', 'fa-drafting-compass', 4, 1),
('Turnkey Interiors', 'turnkey-interiors', 'Modular kitchens, wardrobes, and complete interiors', 'fa-couch', 5, 1),
('Project Development', 'project-development', 'Joint ventures, gated communities, and land development', 'fa-project-diagram', 6, 1),
('Home Tips', 'home-tips', 'Practical tips for homeowners and builders', 'fa-lightbulb', 7, 1),
('Quality & Materials', 'quality-materials', 'Construction materials, waterproofing, and quality standards', 'fa-shield-halved', 8, 1);
