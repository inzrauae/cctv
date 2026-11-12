# Last Solution (Pvt) Ltd: CCTV and security website

A Laravel 12 website and online shop for Last Solution (Pvt) Ltd, No: 135/8 Ebulpuraya, Nugawela, Kandy (076 722 3260).

- **Services:** CCTV installation, CCTV repair and service, A/C installation and service, LED name boards and solar services. Each service has its own page and a booking form.
- **Shop:** CCTV packages, cameras, DVR and NVR recorders, surveillance hard drives, cables and connectors, power supplies and accessories, organised by shop category. Customers search by name, product code or model number.

Customers can add priced, in-stock items to a cart and check out. They choose pickup or delivery, and pay at the office, cash on delivery or by bank transfer. At checkout, customers can optionally select **With installation** and provide the installation address. The installation fee is quoted separately based on the distance from the Nugawela office and is not included in the order total. Items with no price or no stock show an enquiry form instead. Service bookings, item requests and product enquiries are saved to the database and offer a WhatsApp follow-up link. Staff manage everything at `/admin`.

## Local development

Requirements: PHP 8.2+ with pdo_sqlite (or pdo_mysql) and Composer.

```bash
composer install
cp .env.example .env        # then set ADMIN_EMAIL / ADMIN_PASSWORD
php artisan key:generate
php artisan migrate --seed  # creates the admin user, the CCTV shop categories and sample products
php artisan serve           # http://127.0.0.1:8000  ·  admin: http://127.0.0.1:8000/admin
```

If `ADMIN_PASSWORD` is empty, the seeder generates a password and prints it once.

Tests:

```bash
php artisan test   # feature tests: shop, cart, checkout and stock, enquiries, admin, installer
```

## Data model

| Table | Purpose |
|---|---|
| `categories` | Shop categories (CCTV Cameras, DVR & NVR Recorders…). Each has a code used to number its products, e.g. `CAM-00001`. |
| `products` | Fields: name, category, model number, product code (SKU), key specifications, price (empty = "price on request"), stock, warranty, image |
| `orders`, `order_items` | Orders, including optional installation requests and their property address. Installation is quoted separately based on distance and is not included in the order total. Each line keeps a copy of the product name, model number and price at the time of purchase. |
| `enquiries` | Service bookings, item requests and product enquiries |
| `stock_movements` | Every stock change (opening stock, orders, cancellations, adjustments) |

Stock is reserved when an order is placed, inside a locked transaction. It is returned if an admin cancels the order.

## Admin (`/admin`)

- **Dashboard:** pending orders, new enquiries, this month's sales, low stock.
- **Orders:** change status, add internal notes, call or WhatsApp the customer.
- **Enquiries & bookings:** follow-up status and notes.
- **Products:** create and edit products and upload images (saved to `public/uploads/products`).
- **Inventory:** receive and adjust stock, reorder list, CSV export.
- **Shop categories:** names, order, visibility and product-code prefixes.
- **Payments & delivery:** payment methods, bank details and delivery fee.

## Settings

Business details are read from `.env` into `config/shop.php`:

`SHOP_PHONE`, `SHOP_PHONE_DISPLAY`, `SHOP_WHATSAPP`, `SHOP_EMAIL`, `SHOP_ADDRESS`, `SHOP_FACEBOOK_URL`, `SHOP_TIKTOK_URL`, `SHOP_DELIVERY_FEE`, `SHOP_FREE_DELIVERY_OVER`, `SHOP_BANK_DETAILS`, `SHOP_NOTIFY_EMAIL`.

The service list, service FAQs and the "how it works" steps are also in `config/shop.php`.

If `SHOP_NOTIFY_EMAIL` is set, the shop gets an e-mail for each new order and enquiry. This also needs the `MAIL_*` settings.

## Deploying to cPanel

Replace `yourdomain.lk` and `youraccount` below with the real domain and cPanel account name. The app is a Git clone in `/home/youraccount/lastsolution`, and `public_html` is a link to its `public/` folder.

```
commit + push to main
  → GitHub Action (.github/workflows/deploy.yml, if set up): runs the tests
  → cPanel API: Git Version Control pulls main
  → .cpanel.yml runs deploy.sh: composer install, migrate, rebuild caches
```

The server's `.env` and the uploaded images in `public/uploads` are not in Git, so deployments never overwrite them.

### One-time setup

1. **PHP:** cPanel → MultiPHP Manager → set `yourdomain.lk` to **PHP 8.2 or newer**.
2. **Database:** cPanel → MySQL Databases. Create a database and a user, and add the user to the database with **ALL PRIVILEGES**.
3. **Clone:** cPanel → Git Version Control → Create, with Repository Path `/home/youraccount/lastsolution`. Do not clone into `public_html`.
4. **`.env`:** in `/home/youraccount/lastsolution` create a file named `.env` with the production settings. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://yourdomain.lk`, the `DB_*` values, `ADMIN_EMAIL`/`ADMIN_PASSWORD` and `DEPLOY_PUBLIC_HTML=/home/youraccount/public_html`. Keep this file out of Git.
5. **First deploy:** cPanel → Git Version Control → Manage → Pull or Deploy → **Deploy HEAD Commit**. On the first run, `deploy.sh`:
   - generates the app key if it is missing
   - creates the tables, the admin user and the CCTV shop categories
   - moves the old `public_html` to `public_html.before-laravel-<date>` and links `public_html` to the app's `public/` folder

   If the site does not load, read the log in `~/.cpanel/logs/`.
6. **SSL:** cPanel → SSL/TLS Status → Run AutoSSL. The site then always redirects to `https://` without `www`.
7. **Auto-deploy from GitHub (optional):** create a cPanel API token, then add the GitHub Actions secrets `CPANEL_HOST`, `CPANEL_USER`, `CPANEL_TOKEN` and `CPANEL_REPO_PATH`.

Run `DemoProductSeeder` only if you want the sample products. **Its prices, stock and model numbers are illustrative.**

### Deploying by hand

Use cPanel → Git Version Control → **Update from Remote**, then **Deploy HEAD Commit**. If the account has Terminal, you can instead run `cd ~/lastsolution && bash deploy.sh`.

## Not included yet

- Online card payments (for example PayHere). Checkout currently records pay-at-office, cash-on-delivery and bank-transfer orders.
- Bulk CSV import for products.
- Customer accounts and order tracking. Customers get a signed confirmation link instead.
