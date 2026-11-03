# AtriBotix website

A plain **HTML + CSS + JavaScript** project — no framework, no build step.
Open `index.html` in a browser, or run a local server from this folder:

```bash
python3 -m http.server 8000      # then visit http://localhost:8000
```

## Deploying to production (atribotix.in)

```bash
python3 tools/build.py      # minifies css/js into dist/
```

1. Upload the **contents of `dist/`** (including the hidden `.htaccess`) to the web root.
2. Needs **Apache or LiteSpeed** with `mod_rewrite`, `mod_headers`, `mod_deflate`, `mod_expires`, and **PHP 8.1+** with the `mbstring` and `fileinfo` extensions.
3. Create the private config **outside the web root**: copy `config/config.sample.php` to `../atribotix-config/config.php` (one level above the web root) and set the mail addresses and `site_host`. Submissions and resumes are saved to `../atribotix-storage/` (also outside the web root; the folder is created automatically, make sure PHP can write to the parent folder).
4. Optional analytics: put your GA4 ID in `js/config.js` (`gaMeasurementId`) before building. Leave it empty for no analytics.
5. Test: submit both forms, check `atribotix-storage/submissions.log`, check the mail arrives, open a wrong URL (404 page), run `curl -I https://atribotix.in` to see the security headers.

See `AUDIT-REPORT.md` for what was changed and what still needs your input.

## Folder structure

```
atribotix-site/
├── index.html        Home
├── privacy.html  terms.html  404.html  thank-you.php
├── services.html     Services
├── features.html     Features
├── asip.html         ASIP marketplace
├── apply.html        Apply for ASIP
├── career.html       Career (application form)
├── css/
│   ├── base.css        shared: variables, reset, navbar, buttons, hero, footer, animations
│   ├── components.css  shared: badges, avatar stack, form fields
│   └── <page>.css      only what that page needs (home, services, features, asip, apply, career)
├── js/
│   ├── main.js         shared: scroll-reveal animation + navbar shadow
│   ├── forms.js        shared: form validation helpers (Home + Career)
│   └── <page>.js       small per-page script (home, services, features, asip, apply, career)
├── api/              PHP form endpoints (contact.php, career.php, csrf.php, bootstrap.php)
├── config/           config.sample.php (copy outside the web root)
├── icons/            favicons, apple-touch-icon, app icons
├── tools/build.py    builds dist/ (minified, ready to upload)
├── robots.txt  sitemap.xml  site.webmanifest  .htaccess
└── images/           every photo, icon and logo used by the pages (.webp) + og-image.jpg
```

Every page loads its files in the same order:

```html
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/components.css">
<link rel="stylesheet" href="css/<page>.css">
...
<script src="js/main.js"></script>
<script src="js/<page>.js"></script>
```

## How the code is organised

- **Naming (BEM-style):** `block__element--modifier`, e.g. `program__title`, `btn--primary`, `navbar__link.is-active`.
- **Colors / shadows / easing** live in CSS variables at the top of `base.css` (`--blue`, `--ink`, `--muted`, …). Change the brand blue in one place.
- **Navbar and footer** are identical HTML in every page. To change a link, edit it in each `.html` file (search for `navbar` / `footer`).
- **Active page:** the current page's navbar link has `class="navbar__link is-active"` and `aria-current="page"`.
- **Scroll animation:** each page script calls `initReveal('<css selectors>')` (from `main.js`). Add a selector to the list to animate a new block.
- **Animations respect** the visitor's *reduced motion* setting.
- **Each hero** has its photo set in the page's own css file (`.hero { background-image: … }`).

## Add a new page

1. Copy `features.html` → `my-page.html`, change the `<title>`, the hero text and the `<main>` content.
2. Create `css/my-page.css` and `js/my-page.js` (start `my-page.js` with `initReveal('.section-head, …')`).
3. Point the `<link>` / `<script>` tags in the new page at those files.
4. Move `is-active` to the right navbar link (or remove it), and add the page to the navbar and footer of the other pages.

## Things still to connect (TODO)

| Where | What |
| --- | --- |
| `asip.html` | **Buy Now** and **Contact** open WhatsApp (number set at the top of the WhatsApp block in `js/asip.js`). **Explore All Projects** is visual only |
| `index.html`, `career.html` | Forms validate in the browser but are **not sent anywhere**. Look for `TODO` in `js/home.js` and `js/career.js` |
| `apply.html` | The WhatsApp button opens `wa.me/919794982969`. Change the number if needed |
| Navbar | **Enquire Now** button, **About Us** and legal footer links point to `#` |
| `index.html` | **Learn More** (Why Choose section) points to `#`. The FAQ answers are placeholder text — please review |
| Fonts | Inter is loaded from Google Fonts (needs internet). Self-host it if you need offline use |
