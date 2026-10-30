# SellPata — sellpata.com

বাংলা ভাষার মার্কেটিং ওয়েবসাইট: ফেসবুক উদ্যোক্তাদের জন্য নিজের ব্র্যান্ডের ল্যান্ডিং পেজ।
Public marketing site for SellPata (Bengali-first), built with **Laravel 12** + **Vue 3** (Vite).

## Routes

| Path       | View          |
| ---------- | ------------- |
| `/`        | Home          |
| `/clients` | Our Clients   |
| `/contact` | Contact       |
| `POST /api/contact` | Contact endpoint (validated, throttled at 5/min, stored in `contact_messages`) |

## Setup

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate
npm install && npm run build     # or: npm run dev
php artisan serve
```

## Content configuration

Everything a non-developer may change lives in `config/sellpata.php` and is
printed into `window.__SELLPATA__` by `resources/views/app.blade.php`:

```dotenv
SELLPATA_YOUTUBE_URL=            # empty → Bengali placeholder instead of a player
SELLPATA_TEMPLATE_COUNT_LABEL=   # empty → "রেডিমেড ল্যান্ডিং পেজের সংগ্রহ"
SELLPATA_PAYMENTS_AVAILABLE=false   # false → "শিগগিরই আসছে"
SELLPATA_SHIPPING_AVAILABLE=false
SELLPATA_CONTACT_PHONE=
SELLPATA_CONTACT_EMAIL=
SELLPATA_CONTACT_WHATSAPP=
```

Copy that is not environment specific (headlines, benefits, steps, sample
templates) lives in `resources/js/data/`. Client entries and reviews are empty
by default and fall back to Bengali empty states — no invented customers.

## Tests

```bash
php artisan test          # contact endpoint: validation, storage, throttling, routes
npm run build             # Vite production build
```
