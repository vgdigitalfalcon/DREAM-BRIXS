# DreamBrixs Project CRM (PHP + MySQL)

A functional first-version project CMS/CRM built for XAMPP or a PHP 8+ / MySQL environment. The interface intentionally uses basic HTML; visual styling can be added later without changing the data model.

## Included

- Admin login with password hashing, session regeneration and CSRF tokens.
- Six seeded project categories based on the supplied DreamBrixs service pages.
- Category-specific dynamic specification fields stored as metadata (no separate project table per category).
- Public/private/optional-public visibility per specification.
- Project create/edit, services, draft/published/archive status, featured flag, ordering and location.
- Automatic slug generation, manual slug editing and a Generate Slug button. Slugs are made unique server-side.
- Project overview/content sections with per-section public visibility.
- Cover, hero and gallery image uploads (JPG, PNG, WebP; 8 MB each), image approval and public visibility.
- SEO metadata, canonical URL, Open Graph fields, index/noindex and a basic SEO checklist.
- Related projects and revision snapshots on every successful save.
- Public project listing with category/service/city/search filters and pagination.
- Public individual project page with public specifications, approved images, related projects and JSON-LD.
- Project view counts: total and current-month views in admin dashboard/listing and public project page.
- Admin preview of draft projects; preview views are excluded from analytics.

## Requirements

- PHP 8.1+ (8.2 recommended)
- MySQL 8.0+ or MariaDB 10.5+
- PHP extensions: PDO, pdo_mysql, fileinfo, mbstring, iconv, exif (optional)
- Apache with `mod_rewrite` is not required; routes use ordinary PHP files.

## XAMPP installation

1. Copy the `dreambrixs-crm` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL in XAMPP.
3. Copy `config.example.php` to `config.php`.
4. In phpMyAdmin, create an empty database named `dreambrixs_crm` (collation `utf8mb4_unicode_ci`). The installer will create the tables inside it.
5. Edit `config.php` with your local database credentials and set `base_url` to `http://localhost/dreambrixs-crm`.
6. Open `http://localhost/dreambrixs-crm/install.php` once. The installer creates the database tables, categories, field templates, services and a starter admin account.
7. Log in at `http://localhost/dreambrixs-crm/admin/login.php`.
8. Starter login: `admin@dreambrixs.local` / `ChangeMe@12345`.
9. Change the starter password immediately (see below), then delete `install.php` from the server.

## Change the starter password

Use a one-time PHP script in the project root, run it once, then delete it:

```php
<?php
require_once __DIR__ . '/includes/bootstrap.php';
$newPassword = 'PUT_A_LONG_UNIQUE_PASSWORD_HERE';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);
db()->prepare('UPDATE users SET password_hash=? WHERE email=?')->execute([$hash, 'admin@dreambrixs.local']);
echo 'Password updated. Delete this script now.';
```

## URLs

- Admin dashboard: `/admin/`
- Admin projects: `/admin/projects/`
- Public project listing: `/projects.php`
- Public project detail: `/project.php?slug=your-project-slug`

## View analytics definition

The application records one view per PHP session per project per configured deduplication window (30 minutes by default). This reduces refresh inflation. Admin preview does not record a view. The dashboard's monthly count uses the server's configured timezone. This is a lightweight first-party counter, not a replacement for analytics platforms; bots may still be counted and a cleared/new browser session can count as a new visitor.

## Privacy and publishing

- A specification marked Private is not selected by the public project page query.
- Cover and hero images require their own public and approved flags. Gallery images are not public unless both `is_public=1` and `is_approved=1`.
- New cover, hero and gallery uploads default to private and unapproved. Direct access to uploaded files is blocked; `media.php` serves only approved public images (or images to a logged-in admin).
- Customer identities, budgets, contracts, BOQs and internal financial information are not included as project fields.
- Only publish content and media when the customer/owner has granted permission.

## Important production hardening before going live

1. Serve the CRM over HTTPS.
2. The included `uploads/.htaccess` blocks direct file access; images are served through `media.php`, which checks approval and visibility. Keep this protection enabled and prevent script execution in uploads at the web-server level too.
3. Add rate limiting and login lockout, and configure backups.
4. Review admin roles/permissions before creating editor accounts.
5. Add a dedicated FAQ management form if project FAQs will be edited frequently; the table is already included in the schema.
6. Add pagination to the admin list for large portfolios.
7. Use a real analytics/consent policy appropriate to your jurisdiction if you add cookies or tracking beyond the session-based counter.

## Notes

This is a working first version of the CRM foundation, not the final branded website design. The CSS layer is intentionally minimal. The public project page currently renders content sections as plain text and should be styled to match the existing DreamBrixs site in the next design phase.
