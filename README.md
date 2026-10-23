# nhatduysgn.com

Company website and admin for **Công ty TNHH Kỹ thuật Nhật Duy**: company introduction, product catalogue, technical services, projects, news and a contact form.

Replaces the old CakePHP 2.3 / PHP 5.6 site (compromised through an unauthenticated file manager in 2022–2026).

| | |
|---|---|
| Stack | PHP 8.3+, Laravel 13, Filament 5 (admin), MariaDB 10.6+ |
| Public site | Blade + plain CSS/JS (`public/css/site.css`, `public/js/site.js`) — **no Node build needed** |
| Admin | `/admin` (Filament), Vietnamese UI |
| Uploads | `public/uploads` (random file names, image/PDF/Office whitelist, PHP execution disabled by `.htaccess`) |

## Local development

Requires Docker only.

```bash
docker compose up -d --build
docker compose exec app composer install
cp .env.example .env && docker compose exec app php artisan key:generate   # first time only
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan legacy:import          # optional: real data from the old site
docker compose exec app php artisan make:filament-user     # admin account
```

| URL | |
|---|---|
| http://localhost:8000 | Website |
| http://localhost:8000/admin | Admin |
| http://localhost:8026 | Mailpit (emails sent by the contact form) |
| `127.0.0.1:3308` | MariaDB (`nhatduy` / `nhatduy`); databases `nhatduy` (new) and `legacy` (old site dump) |

The `db` container loads the old dump into the `legacy` database on first start. Paths default to the sibling folders of this repo and can be overridden:

```bash
LEGACY_SQL_HOST=/path/to/nha9921a_nhatduy.sql LEGACY_WEBROOT_HOST=/path/to/old/app/webroot docker compose up -d
```

Tests and code style:

```bash
docker compose exec app php artisan test --compact
docker compose exec app vendor/bin/pint
```

## What the admin manages

| Menu | Content |
|---|---|
| Sản phẩm | Products (rich description, spec tables, gallery, downloadable documents, SEO), product groups (2 levels, icons, featured), brands (logos) |
| Nội dung | Services, projects, news posts (scheduled publishing), client logos, certificates |
| Liên hệ | Contact form inbox (unread badge, mark read/unread, reply by email). Each new message is also emailed to *Email nhận thông báo liên hệ* |
| Hệ thống | **Cài đặt website** (company details, homepage / about / services copy, SEO), URL redirects, admin accounts |

Admin accounts: create the first one with `php artisan make:filament-user`; after that, in *Hệ thống → Tài khoản quản trị*. Unticking *Được phép đăng nhập* blocks an account without deleting it.

## Migrating data from the old site

`php artisan legacy:import` copies everything from the old database and upload folders. It is **idempotent** — every record keeps its old ID (`legacy_id` / `legacy_ids` / `legacy_keys`), so it can be run again safely.

| Step (`--step=`) | Old → new |
|---|---|
| `files` | `app/webroot/files` and `media` → `public/uploads/legacy/…` (images, PDF, Office, ZIP only; files whose name or content looks like PHP are skipped) |
| `categories` | `product_categories` → product groups (temporarily, as on the old site) |
| `brands` | `brands` + `suppliers` → brands (merged by name, e.g. supplier FAG → SCHAEFFLER; aliases in `config/legacy.php`) |
| `products` | `products` → products (description/specs cleaned of scripts and inline styles, old image links rewritten, attachment → document) |
| `catalog` | merges the 86 old categories into the 43 standard groups of `config/catalog.php` (see below) |
| `posts` | `articles` in categories News/Project/Event → news |
| `clients` | `customers` → client logos |
| `contacts` | `contacts` → contact inbox (marked as read) |
| `tags` | `tags` → redirects `/products/tag:N` → search |

```bash
php artisan legacy:import                         # everything
php artisan legacy:import --step=products         # one step
php artisan legacy:import --skip-existing         # only add new records, keep admin edits
```

### Product groups (`config/catalog.php`)

The old site had 86 categories organised partly by brand and partly by application, with duplicates and misfiled products. They are standardised into 13 groups by application with 30 sub-groups (brands have their own pages). `config/catalog.php` lists, for each group, the old category IDs it absorbs and any old product IDs moved to it explicitly.

```bash
php artisan catalog:restructure --dry-run   # show what would change
php artisan catalog:restructure             # apply (also runs as the `catalog` import step)
```

It is safe to re-run: products move to their group, old IDs stay on the group (so `/products/category:{id}` still redirects), emptied old categories are deleted, and once a group has been applied its name, icon, image and texts belong to editors. Groups created in the admin are never touched. To change the structure, edit the config and run the command again; `legacy:verify` checks every old category and product against it.

Connection settings: `LEGACY_DB_*` and `LEGACY_WEBROOT` in `.env`. The 4 services are created by `php artisan db:seed` (the old site stored them as one article).

### Proving nothing was lost

```bash
php artisan legacy:verify                       # exit code 0 = everything is there
php artisan legacy:verify --urls=old-urls.txt   # also check real URLs visitors/Google used
```

It reconciles the old site against the new one, record by record, and lists every gap:

| Check | What "complete" means |
|---|---|
| Records | Every old category, brand/supplier, product, news article, client, contact message and tag has a new counterpart (matched by old ID) |
| Product fields | Same name, group, brand, visibility; image and attachment present when the old file existed; description not empty; every old tag kept as a search keyword |
| Files | Every safe file in the old `files/` and `media/` exists under `public/uploads/legacy` (rejected files are listed separately) |
| Content links | Every image/link inside migrated descriptions and posts points to a file or page that exists; no links left to the old domain |
| Old URLs | Every old product/category/brand/supplier/tag/article URL plus the old menu and slider links answers 301 → a page that returns 200 |

It also prints what is **not migrated on purpose** (old admin users, homepage slider, menus, template text blocks) so the report covers every old table.

For the strongest check, run it against the URLs real visitors and Google used, taken from the old server's access logs before switching DNS:

```bash
zcat ~/domains/nhatduysgn.com/logs/*.tar.gz 2>/dev/null | grep -a '" 200 ' | awk '{print $7}' \
  | grep -vE '\.(css|js|ico|woff2?)(\?|$)' | sort | uniq -c | sort -rn | awk '$1 >= 2 {print $2}' > old-urls.txt
php artisan legacy:verify --urls=old-urls.txt
```

**Go-live rule:** run `legacy:import`, then `legacy:verify` (with `--urls`) on the production server; switch DNS only when it exits with code 0. After go-live, watch *Hệ thống → Chuyển hướng URL* and the server's 404 log for a few weeks and add redirects for anything new.

## Old URLs (SEO)

Every old URL is answered with a **301** to its new address, so Google rankings and bookmarks carry over. Handled by `App\Services\LegacyUrlResolver` (fallback route); admin-defined redirects (*Hệ thống → Chuyển hướng URL*) take priority.

| Old | New |
|---|---|
| `/p{id}-{slug}.html`, `/products/view/{id}` | `/san-pham/{slug}` (or `/san-pham` if removed) |
| `/products/category:{id}/…` | `/danh-muc/{slug}` |
| `/products/brand:{id}/…`, `/products/supplier:{id}/…` | `/thuong-hieu/{slug}` |
| `/products/tag:{id}/…`, `/products/key:{base64}` | `/san-pham?q=…` |
| `/a{id}-{slug}.html`, `/articles/view/{id}` | `/tin-tuc/{slug}`; old about/services/certificate pages → `/gioi-thieu`, `/dich-vu`, `/gioi-thieu#chung-nhan` |
| `/gioi-thieu.html`, `/dich-vu.html`, `/chung-chi.html`, `/lien-he.html`, `/su-kien` | `/gioi-thieu`, `/dich-vu`, `/gioi-thieu#chung-nhan`, `/lien-he`, `/tin-tuc` |
| `/files/…`, `/media/…` | `/uploads/legacy/files/…`, `/uploads/legacy/media/…` |

## Deploying to the current hosting (DirectAdmin / cPanel, PHP 8.3)

1. **Contain the old site first** (it is compromised): take it offline or at least delete `public_html/app/webroot/filemanager/` and every `*.php*` file under `app/webroot/media` and `files`; change hosting, FTP, database and email passwords.
2. Create a new MariaDB database + user for the new site. Keep the old database — it is the import source.
3. Upload the project **outside** the web root, e.g. `~/domains/nhatduysgn.com/app/`, and point the domain's document root to its `public/` folder (DirectAdmin: replace `public_html` with a symlink to `app/public`).
4. Create `.env` from `.env.example`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://nhatduysgn.com`, `DB_*`, `LEGACY_DB_*` (old database), `LEGACY_WEBROOT` (old `app/webroot` path), `MAIL_*` (hosting SMTP), `CONTACT_NOTIFY_EMAIL`.
5. On the server (SSH):
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan key:generate
   php artisan migrate --force && php artisan db:seed --force
   php artisan legacy:import
   php artisan make:filament-user
   php artisan optimize
   ```
   No SSH? Run the same commands locally against a copy and upload the project including `vendor/` and `public/js|css|fonts/filament`.
6. Permissions: `storage/`, `bootstrap/cache/` and `public/uploads/` writable by PHP; nothing else.
7. Check: homepage, `/admin`, send a test message from `/lien-he`, open a few old URLs (e.g. `/p832-….html`), then submit `https://nhatduysgn.com/sitemap.xml` in Google Search Console.

After go-live, update with `git pull && composer install --no-dev -o && php artisan migrate --force && php artisan optimize`.
