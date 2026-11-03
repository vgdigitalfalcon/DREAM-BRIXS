# DreamBrixs Projects CRM

## Setup Instructions

1. **Database**
   - Import `schema.sql` into your database `u670120666_dreambrixs_crm`
   - Or run the SQL queries manually

2. **Upload Files**
   - Upload the entire `crm/` folder to `public_html/crm/`
   - Upload `public-projects/projects.php` and `project-detail.php` to your main `public_html/` (adjust the `require_once` path if needed)

3. **Permissions**
   - Make `crm/uploads/projects/` writable (chmod 755 or 777)

4. **Login**
   - URL: `https://yourdomain.com/crm/`
   - Default login: **admin** / **admin123**
   - Change the password after first login (you can update the hash in the admins table)

5. **Public Pages**
   - Projects listing: `https://yourdomain.com/projects.php`
   - Individual project: `https://yourdomain.com/project-detail.php?slug=your-slug`
   - Clean URLs can be added with .htaccess rewrite later

## Features

- Category-specific form fields (shows only relevant fields)
- Multiple image upload with preview + remove
- Month-by-month dashboard
- Featured projects
- Ongoing + Completed projects
- SEO fields
- Public listing with filters
- Individual project pages for SEO

## Integration with Service Pages

On each service HTML page, you can add a PHP include or AJAX call to show featured projects of that category.  
Example snippet for Individual House page:

```php
<?php
require_once 'crm/config.php';
$stmt = $pdo->prepare("SELECT p.*, (SELECT image_path FROM project_images WHERE project_id=p.id LIMIT 1) as img 
                       FROM projects p JOIN categories c ON p.category_id=c.id 
                       WHERE c.slug='individual-house' AND is_featured=1 LIMIT 3");
$stmt->execute();
$featured = $stmt->fetchAll();
?>
```

## Security Notes

- Change default password immediately
- The uploads folder should not execute PHP
- Consider adding CSRF protection later for production
