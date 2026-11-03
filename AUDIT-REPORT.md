# AtriBotix website — production-readiness report

**Scope note.** The live site (atribotix.in/index.php) blocked automated access, so this audit and all fixes were made on the project files in this folder (the static HTML/CSS/JS rebuild of every page). Nothing on the live server was inspected or changed. Everything below was tested locally in Chromium (desktop and mobile), except the PHP files (no PHP runtime was available — see "Needs manual verification").

## 1. Issues found
- No 404 page, favicon, robots.txt, sitemap, Open Graph/Twitter tags, canonical URLs, meta descriptions (all pages shared one generic title), or structured data.
- Forms only checked fields in the browser and sent nothing; no server validation, CSRF, spam protection, loading or error states; no thank-you page.
- No Privacy Policy, Terms, cookie consent or analytics setup. Footer email/phone were plain text.
- Photos had no width/height and all loaded immediately (index: 50 requests, 906 KB).
- Slide-in animations could make phone pages scroll sideways (found by the new tests, fixed).
- Accessibility: no skip link, no visible focus style, form fields had placeholders but no labels, heading levels skipped (h2→h4/h5), brand blue text on white = 2.7:1 contrast (AA needs 4.5:1), the file input of the Career form was not keyboard reachable.
- Dead links: footer About Us / Testimonials / Contact / Terms / Privacy, "Enquire Now" and "Enquire About This Program" pointed to "#".

## 2. Improvements implemented
| # | Requirement | Status |
|---|---|---|
| 1 | Custom 404 | `404.html`, `ErrorDocument 404` keeps real HTTP 404 (tested), works on deep URLs |
| 2 | CTA above the fold | Hero has "Explore ASIP"; navbar "Enquire Now" and "Apply for ASIP" now work |
| 3–4 | Unique titles + descriptions | Every page (checked automatically: no duplicates) |
| 5 | Open Graph / Twitter | Full tags + generated `images/og-image.jpg` (1200×630) |
| 6 | Favicon | `favicon.ico`, 16/32 PNG, Apple touch icon, 192/512 app icons, `site.webmanifest` (made from your logo's robot icon). No SVG favicon — none exists in your brand files |
| 7–8 | robots.txt, sitemap.xml | Public pages listed; `/api/`, thank-you and 404 excluded |
| 9 | Alt text | Every `<img>` has alt; informative photos described, decorative icons/avatars `alt=""` |
| 10 | Responsive | Tested at 320, 375, 430, 768, 1024, 1440 on all pages: no horizontal overflow |
| 11 | Sticky mobile CTA | "Enquire Now", safe-area aware, hides while a form is on screen (tested) |
| 12 | Loading states | Form button spinner + lock, "Sending…" status, 20 s timeout, soft placeholder behind lazy photos |
| 13 | Form errors | Field-level messages (`aria-invalid`, `role="alert"`), values kept, server errors mapped to fields, network failure shows fallback phone/email, double-submit blocked (tested: 1 request) |
| 14 | Thank-you page | `thank-you.php` only opens right after a real submission (session flag), otherwise redirects home; `noindex` |
| 15–16 | Privacy + Terms | `privacy.html`, `terms.html` written from what the site actually does — **have a lawyer review** |
| 17 | Cookie consent | Accept / Reject non-essential / Customize, remembered, reopened from footer "Cookie Settings" (tested) |
| 18 | Analytics | GA4 loader that only runs after consent and only if an ID is set in `js/config.js`; events: CTA clicks (`data-track`), `generate_lead` on form success |
| 19 | Contact details | Real address/phone/email from your site; email = `mailto:`, phones = `tel:`. **No Google Maps link** (address not verified for a map pin) |

SEO/accessibility extras: canonical URLs, Organization + WebSite JSON-LD (home), skip link, visible focus rings, one `<h1>` per page and no skipped heading levels (checked), labelled form fields, darker text blue `#0a75a6` (5.1:1) for small blue text, `aria-label`s on navigation.

## 3. Security fixed / added
- HTTPS redirect, HSTS, `Content-Security-Policy` (no inline scripts/styles; only Google Fonts and, if enabled, Google Analytics allowed), `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, `frame-ancestors`/`X-Frame-Options`, `ServerSignature off`, `X-Powered-By` removed. CSP was tested: zero violations on every page.
- Form endpoints: CSRF token, same-site Origin/Referer check, honeypot, rate limit (5 per 10 min per IP), header-injection-safe input cleaning, server-side validation, no raw errors shown, PHP errors hidden.
- Resume upload: extension + real MIME type + 5 MB limit, random file name, stored **outside** the web root with 0640 permissions, never served.
- Secure session cookie (HttpOnly, SameSite=Lax, Secure on HTTPS).
- `.htaccess` blocks dotfiles, logs, `.sql/.env/.ini/.bak/.py/.md` files and directory listing. No SQL is used (no database), so SQL injection does not apply; output is never echoed unescaped (XSS), the page text is static.
- No credentials, keys or emails other than your public contact email are in the code.

## 4. Performance (initial page load, local test, same machine before/after)
| Page | Before | After |
|---|---|---|
| Home, mobile | 906 KB / 50 requests | **424 KB / 22 requests** |
| Home, desktop | 906 KB / 50 | **733 KB / 35** |
| ASIP, mobile | 852 KB / 36 | **520 KB / 23** |
| Services, mobile | 562 KB / 19 | **419 KB / 16** |
| Features / Career | 446 / 185 KB | 454 / 196 KB (slightly more: cookie banner, favicon and preload added) |

CLS was 0 before and after (image sizes are declared). LCP in this local test was 0.2–0.6 s on both, but that is not a real-network number. CSS+JS shrank 59 KB → 45 KB when minified (`tools/build.py`); gzip, long browser caching for images and the hero preload are configured in `.htaccess`. Images were already WebP. **Run Lighthouse / PageSpeed Insights on the live URL after deploying** — no Lighthouse run was possible here.

## 5. Needs your input (external credentials / business information)
- GA4 Measurement ID (`js/config.js`). Until set, no analytics runs.
- Mail settings in the private `config.php` (a real `From` address on your domain; check SPF/DKIM so mail is not marked as spam).
- Review of Privacy Policy and Terms by a legal adviser; confirm statements such as "we do not sell or rent information", retention wording, and governing law.
- A verified Google Maps link if you want one.
- Pages that don't exist yet: **Media Presence** (footer, disabled link), **About Us** points to the home-page About section, **Explore All Projects** (ASIP page) has no destination, **Enquire Now** goes to the home contact form.
- FAQ answers on the home page are placeholder text written from site content — please review.
- Real project details: Buy Now sends the same WhatsApp message for every project (as you specified).

## 6. Needs manual verification after deploy
- **PHP files were written but not executed** (no PHP in the build environment). Test both forms end-to-end on the server, including a wrong file type and a 6 MB resume.
- Confirm `.htaccess` is honoured (security headers, HTTPS redirect, 404 status): `curl -I https://atribotix.in/`, `curl -I https://atribotix.in/nope`.
- Check CSP in the browser console after enabling GA4.
- Social previews (Facebook Sharing Debugger, X card validator), Google Search Console sitemap submission.
- Lighthouse mobile run; contrast of white text on the brand blue (buttons, footer) is ~2.7:1 and does **not** meet AA — fixing it means darkening the brand blue, which I did not change without your decision.
- Backups and dependency scanning are server-level tasks: schedule hosting backups and keep PHP updated (the project itself has no npm/composer dependencies to audit).

## 7. Production checklist
- [ ] Upload `dist/` incl. `.htaccess`; HTTPS certificate active
- [ ] `config.php` created outside web root; storage folder writable
- [ ] Contact + Career forms tested; email received
- [ ] 404 returns status 404; thank-you page redirects when opened directly
- [ ] Security headers verified
- [ ] GA4 ID added (optional) and consent flow tested
- [ ] Legal pages reviewed
- [ ] Sitemap submitted to Google Search Console
- [ ] Lighthouse ≥ 90 (mobile) on key pages
- [ ] Backups enabled
