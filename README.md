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

## Landing-page builder (`/start` → `/builder/{publicId}`)

Flow: discovery → template → anonymous draft → schema-driven editor with
autosave → “প্রকাশ করুন” → phone + OTP → draft attached to the account.

- `GET /start` — category search (Bengali, English and alias keywords), template grid.
- `GET /builder/{publicId}` — preview, then the editing workspace.
- `GET /demo/{slug}` — real render of a template, `X-Robots-Tag: noindex, nofollow`, order buttons inert.
- `POST /api/drafts` — create or reuse the draft for this browser.
- `GET|PATCH /api/drafts/{publicId}` — read / autosave (revision-checked).
- `POST /api/drafts/{publicId}/media` — product image upload (private disk).
- `POST /api/drafts/{publicId}/otp`, `POST /api/drafts/{publicId}/otp/verify` — phone verification and ownership transfer.

Nothing is published by this flow: after verification the draft keeps
`status = draft` and has no public URL.

### Anonymous identity

The browser gets one HttpOnly `sp_visitor` cookie (30 days). Only its SHA-256
hash is stored. Every draft read, write, upload and claim checks that hash or
the signed-in owner. Clearing cookies or switching browsers before verifying
means the draft cannot be reached again — the account must be verified first.

### SMS / OTP configuration

`config/otp.php`, driven by `.env`:

| Variable | Purpose |
| --- | --- |
| `OTP_DRIVER` | `log` (local only) or a real driver you register |
| `OTP_TTL_SECONDS` | code lifetime, default 300 |
| `OTP_RESEND_COOLDOWN` | seconds between sends, default 60 |
| `OTP_MAX_ATTEMPTS` | wrong tries per challenge, default 5 |
| `OTP_MAX_SENDS_PER_HOUR` | per phone number, default 5 |
| `OTP_EXPOSE_TEST_CODE` | returns the code in the API response; **local only, never in production** |

With `OTP_DRIVER=log` the code is written to `storage/logs/otp.log` and **no
real SMS is sent**. To go live, implement `App\Services\Otp\OtpSender` for your
provider (SSLWireless, Twilio, …), bind it in a service provider, and set
`OTP_DRIVER` accordingly. The verifier refuses to claim success when the
sender reports itself as unconfigured.

### Demo mode (no SMS yet)

Until a real provider is wired up, the generated code is printed on screen in
the verification step ("ডেমো মোড — এখনো এসএমএস চালু হয়নি") with a one-tap
"কোড বসিয়ে দিন" button, and the same code also goes to `storage/logs/otp.log`.
`OTP_EXPOSE_TEST_CODE=true` is the default outside production; the controller
refuses to return the code when `APP_ENV=production`, so nothing leaks once the
site is live. Wire a real sender and the panel disappears on its own
(`OTP_DRIVER` no longer `log`).

### Housekeeping (not automated yet)

Expired anonymous identities, their drafts and uploads are not purged on a
schedule. Until a command is added, run a periodic cleanup of
`anonymous_identities` past `expires_at` together with unclaimed drafts and
`storage/app/private/drafts/*`.

### Custom domain

Customers connect one domain from **ড্যাশবোর্ড → সেটিংস**. The free
`{username}.sellpata.com` address stays. DNS is checked on demand
(`A` on `@` to `PLATFORM_SERVER_IP`, or `CNAME` `www` to the free host).
Only a **verified** domain on a plan that includes the `custom_domain` entitlement (নিজস্ব ডোমেইন — Growth and Business) serves that store. A downgrade stops the custom host immediately.

| Piece | Contract |
| --- | --- |
| `PLATFORM_BASE_DOMAIN` | platform zone, default `sellpata.com` |
| `PLATFORM_SERVER_IP` | A-record target, default `169.58.90.98` |
| `GET /api/domain`, `POST /api/domain`, `POST /api/domain/check`, `DELETE /api/domain` | signed-in store owner |
| `php artisan domains:verified` | verified apex names, one per line |
| `GET /internal/domain-check?domain=` | `200` only for a verified custom domain (or `www.` of it), and only when both `REMOTE_ADDR` and `Host` are loopback |

TLS for those names is installed on the server later. Point the installer at
`http://127.0.0.1/internal/domain-check?domain=`.

### Product landing themes

Five storefront themes for a merchant's product, separate from the marketing homepage:

| Theme | Demo |
| --- | --- |
| প্রাকৃতিক | `/demo/prakritik` |
| আভিজাত্য | `/demo/abhijatya` |
| স্মার্ট | `/demo/smart` |
| কোমল | `/demo/komol` |
| সরাসরি | `/demo/sorasari` |

Demo pages are `noindex` and refuse real orders. Content stays in a centered column about 1000px wide. Free-plan checkout is cash on delivery only; an online method appears only when the plan allows it and a payment driver plus key are configured.

The webhatbd reference pages returned readable text in this environment. No layout screenshot was attached with the brief.
