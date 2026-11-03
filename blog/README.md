# DreamBrixs Blog CRM

Fully rebranded blog system for DreamBrixs Residential Builders.
Same features as the original CRM, themed to match DreamBrixs (navy + yellow, Poppins, service-page style).

## Deployment

1. Upload the entire `dreambricks-blog` folder to your server as `/public_html/blog/`
2. Import the base SQL (from the original dump or schema.sql)
3. Run `sql/schema-dreambrixs.sql` to update categories and settings
4. Update database credentials in `config.php` if needed
5. Set permissions: `chmod -R 755 admin/uploads`
6. Add the root `.htaccess` rewrite rules (see `root-htaccess-snippet.txt`)

## Login
- URL: `/blog/admin/login.php`
- Default: admin / Admin@123 (change immediately)

## Clean URLs
With root rewrite rules:
- `/blog` → listing
- `/blog/your-post-slug` → single post

## Theme
Matches DreamBrixs service pages exactly:
- Top bar, navbar with mega menu, footer, WhatsApp float, scroll-to-top
- Navy (#0a1628) + Yellow (#F5B800) + Poppins
