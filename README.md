# WP GeoScale - Programmatic SEO Engine

## What is WP GeoScale?
WP GeoScale is an elite programmatic SEO engine designed specifically for WordPress. It allows you to rapidly generate hundreds or thousands of high-quality, targeted landing pages (such as location pages or service pages) from a single master template and a CSV dataset.

## Why is it Useful?
Traditionally, generating thousands of pages in WordPress meant importing them directly into the `wp_posts` database table. This approach causes several critical issues:
1. **Database Bloat**: Thousands of posts heavily slow down your WordPress admin interface and general database queries.
2. **Management Nightmare**: Updating a design or a typo across 10,000 physical pages is incredibly tedious.
3. **Performance Limits**: MySQL struggles to index and query massive `wp_posts` tables efficiently on budget hosting.

**WP GeoScale solves all of this through Virtual Routing:**
- **Zero Database Bloat**: It does NOT create physical posts. It intercepts URLs in real-time.
- **Lightning Fast**: It utilizes a "Flat Index + JSON Payload" custom database architecture combined with WordPress object caching. The database lookup bypasses standard WordPress loops entirely.
- **Single Source of Truth**: You design **one** master template using your favorite page builder (Elementor, Gutenberg, etc.). All virtual pages inherit this design dynamically.
- **SEO Native**: Despite being virtual pages, WP GeoScale guarantees a `200 OK` HTTP status, seamlessly integrates with Yoast SEO & RankMath, and injects all your virtual URLs into the native WordPress XML sitemap.

---

## User Guide & Manual

### Step 1: Prepare Your Master Template
1. In WordPress, go to **Pages > Add New**.
2. Design your landing page exactly how you want it.
3. Wherever you want dynamic data to appear (e.g., the name of a city, phone number, or local image URL), use the WP GeoScale shortcode:
   - `[geoscale field="city_name"]`
   - `[geoscale field="phone_number" default="555-0199"]`
4. Publish this page. This is your "Master Template".

### Step 2: Prepare Your CSV Data
Create a spreadsheet and save it as a CSV. Your CSV **must** follow these rules:
1. **`route_slug` (Required)**: You must have a column header named `route_slug`. This determines the URL. For example, if you enter `new-york`, the generated URL will be `yourdomain.com/locations/new-york/`.
2. **`seo_title` & `seo_description` (Recommended)**: If you want custom SEO metadata per page, include these columns. The plugin automatically syncs them with Yoast and RankMath.
3. **Custom Fields**: Add any other columns you need (e.g., `city_name`, `population`, `local_plumber`). These exact column headers are what you will use in your shortcodes.

**Example CSV Structure:**
| route_slug | seo_title | seo_description | city_name | population |
|---|---|---|---|---|
| new-york | Best Plumber in New York | Need plumbing in NY? Call us. | New York | 8,000,000 |
| austin | Top Rated Plumber in Austin | Fast Austin plumbing services. | Austin | 950,000 |

### Step 3: Upload and Generate
1. Go to **GeoScale** in your WordPress admin menu.
2. Select your published **Master Template Page** from the dropdown menu.
3. Click "Choose File" and upload your prepared CSV.
4. Click **Upload and Process CSV**.

### Step 4: Verify and Scale
- The plugin will ingest the data instantly.
- Visit `yourdomain.com/locations/new-york/` (replace with your actual slug) to see the live page.
- Check your WordPress sitemap (`yourdomain.com/wp-sitemap.xml`) to verify that the `geoscale` provider has injected your new URLs for Google to crawl.

### Advanced Usage (Shortcode)
The `[geoscale]` shortcode accepts two parameters:
- `field`: The exact name of the column header from your CSV.
- `default`: (Optional) Text to display if that column is empty for a specific row.
