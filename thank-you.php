<?php
declare(strict_types=1);
/* thank-you.php — only reachable right after a real form submission (see api/contact.php, api/career.php). */
session_name('atribotix_sid');
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'), 'httponly' => true, 'samesite' => 'Lax']);
session_start();
$kind = $_SESSION['thank_you'] ?? null;
if ($kind === null) { header('Location: index.html', true, 302); exit; }   // no submission -> no confirmation page
unset($_SESSION['thank_you']);
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Thank You | AtriBotix</title>
  <meta name="description" content="Thank you for contacting AtriBotix.">
  <meta name="robots" content="noindex, nofollow">
  <link rel="canonical" href="https://atribotix.in/thank-you.php">
  <meta name="theme-color" content="#2aa8e0">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="AtriBotix">
  <meta property="og:locale" content="en_IN">
  <meta property="og:title" content="Thank You | AtriBotix">
  <meta property="og:description" content="Thank you for contacting AtriBotix.">
  <meta property="og:url" content="https://atribotix.in/thank-you.php">
  <meta property="og:image" content="https://atribotix.in/images/og-image.jpg">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="AtriBotix – students building robotics and electronics projects">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Thank You | AtriBotix">
  <meta name="twitter:description" content="Thank you for contacting AtriBotix.">
  <meta name="twitter:image" content="https://atribotix.in/images/og-image.jpg">
  <link rel="icon" href="favicon.ico" sizes="48x48">
  <link rel="icon" type="image/png" sizes="32x32" href="icons/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="icons/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="icons/apple-touch-icon.png">
  <link rel="manifest" href="site.webmanifest">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/components.css">
  <link rel="stylesheet" href="css/legal.css">
  <script src="js/boot.js"></script>
</head>
<body>
  <a class="skip-link" href="#main">Skip to main content</a>

  <!-- NAVBAR (same on every page; change .is-active for the current page) -->
  <div class="navbar-wrap">
    <nav class="navbar" aria-label="Primary">
      <a class="navbar__logo" href="index.html"><img src="images/logo.webp" alt="AtriBotix" width="520" height="173"></a>
      <div class="navbar__links"><a class="navbar__link" href="index.html">Home</a><a class="navbar__link" href="services.html">Services</a><a class="navbar__link" href="features.html">Features</a><a class="navbar__link" href="asip.html">ASIP</a><a class="navbar__link" href="career.html">Career</a></div>
      <div class="navbar__actions">
        <a class="btn btn--outline" href="apply.html" data-track="apply_for_asip_click">Apply for ASIP</a>
        <a class="btn btn--primary" href="index.html#contact" data-track="enquire_now_click">Enquire Now</a>
      </div>
      <button class="navbar__toggle" type="button" aria-expanded="false" aria-controls="nav-menu" aria-label="Open menu"><span class="navbar__toggle-icon" aria-hidden="true"></span></button>
      <div class="navbar__menu" id="nav-menu">
        <div class="navbar__menu-links"><a class="navbar__menu-link" href="index.html">Home</a><a class="navbar__menu-link" href="services.html">Services</a><a class="navbar__menu-link" href="features.html">Features</a><a class="navbar__menu-link" href="asip.html">ASIP</a><a class="navbar__menu-link" href="career.html">Career</a></div>
        <div class="navbar__menu-actions">
        <a class="btn btn--outline" href="apply.html" data-track="apply_for_asip_click">Apply for ASIP</a>
        <a class="btn btn--primary" href="index.html#contact" data-track="enquire_now_click">Enquire Now</a>
      </div>
      </div>
    </nav>
  </div>

  <!-- HERO -->
  <header class="page-head"><h1>Thank <span>You</span></h1></header>

  <main class="container" id="main"><div class="notice">
<p class="notice__code" aria-hidden="true">&#10003;</p>
<h2 class="notice__title"><?= $kind === 'career' ? 'Application received' : 'Message received' ?></h2>
<p><?= $kind === 'career'
  ? 'Thank you for applying to AtriBotix. We have received your details and resume and will contact you if your profile matches an opening.'
  : 'Thank you for contacting AtriBotix. We have received your message and our team will get back to you.' ?></p>
<div class="notice__actions"><a class="btn btn--primary" href="index.html">Back to Home</a><a class="btn btn--outline" href="<?= $kind === 'career' ? 'services.html' : 'asip.html' ?>"><?= $kind === 'career' ? 'Explore Our Services' : 'Explore ASIP Projects' ?></a></div>
</div></main>

  <!-- FOOTER (same on every page) -->
  <footer class="footer">
    <div class="footer__grid">
      <div class="footer__brand">
        <img src="images/logo.webp" alt="AtriBotix" width="520" height="173" loading="lazy" decoding="async">
        <p>Bistauli Bujurg, Belipar<br>Gorakhpur, UP-273413</p>
        <p>Phone: <a href="tel:+919794982969">+91 9794982969</a>, <a href="tel:+919651382969">+91 9651382969</a></p>
        <p>Email: <a href="mailto:atribotix@gmail.com">atribotix@gmail.com</a></p>
      </div>
      <div class="footer__col"><h2 class="footer__heading">Useful Links</h2><a href="index.html">Home</a><a href="index.html#about">About Us</a><a href="features.html">Features</a><a href="services.html">Services</a><a href="asip.html">ASIP</a></div>
      <div class="footer__col"><h2 class="footer__heading">Our Services</h2><a href="services.html">IoT</a><a href="services.html">Robotics</a><a href="services.html">Coding &amp; Programming</a><a href="services.html">AI &amp; Machine Learning</a><a href="services.html">Drone Technology</a><a href="services.html">3D Printing</a></div>
      <div class="footer__col"><h2 class="footer__heading">Other Links</h2><a href="asip.html">ASIP</a><a href="index.html#testimonials">Testimonials</a><a href="#" aria-disabled="true">Media Presence</a><a href="career.html">Career</a></div>
      <div class="footer__col"><h2 class="footer__heading">Legal</h2><a href="index.html#contact">Contact</a><a href="terms.html">Terms &amp; Conditions</a><a href="privacy.html">Privacy Policy</a><button type="button" class="footer__link-button" data-cookie-settings>Cookie Settings</button></div>
    </div>
    <div class="footer__wordmark" aria-hidden="true">ATRIBOTIX</div>
  </footer>

  <script src="js/config.js"></script>
  <script src="js/consent.js"></script>
  <script src="js/main.js"></script>
  <script src="js/legal.js"></script>
</body>
</html>
