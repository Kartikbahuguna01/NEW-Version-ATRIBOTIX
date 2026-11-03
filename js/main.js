/* main.js — shared helpers used by every page */

/* Fade elements in when they scroll into view.
   `selector` = CSS selector of the elements to animate. */
function initReveal(selector) {
  const items = document.querySelectorAll(selector);

  items.forEach((item) => {
    item.setAttribute('data-reveal', '');
    // small stagger so neighbours appear one after another
    const position = Array.from(item.parentNode.children).indexOf(item);
    item.style.transitionDelay = `${(position % 3) * 0.12}s`;
  });

  const reveal = (item) => {
    item.classList.add('is-visible');
    // remove the delay afterwards so hover effects feel instant
    setTimeout(() => (item.style.transitionDelay = '0s'), 1600);
  };

  if (!('IntersectionObserver' in window)) {
    items.forEach(reveal);
    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          reveal(entry.target);
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.12, rootMargin: '0px 0px -6% 0px' }
  );
  items.forEach((item) => observer.observe(item));
}

/* Deepen the navbar shadow once the page is scrolled. */
function initNavbarShadow() {
  const navbar = document.querySelector('.navbar');
  const update = () => navbar.classList.toggle('is-scrolled', window.scrollY > 20);
  update();
  window.addEventListener('scroll', update, { passive: true });
}

/* Sticky mobile call-to-action: hidden while a form is on screen so it never covers fields. */
function initStickyCta() {
  const bar = document.querySelector('.sticky-cta');
  if (!bar || !('IntersectionObserver' in window)) return;
  const onScreen = new Set();
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => (entry.isIntersecting ? onScreen.add(entry.target) : onScreen.delete(entry.target)));
    bar.classList.toggle('is-hidden', onScreen.size > 0);
  });
  document.querySelectorAll('form, .contact').forEach((target) => observer.observe(target));
}

/* Analytics events: any element with data-track="event_name" is reported (only after consent). */
function initTracking() {
  document.addEventListener('click', (event) => {
    const element = event.target.closest('[data-track]');
    if (!element || !window.track) return;
    const project = element.closest('.project');
    window.track(element.dataset.track, {
      link_text: element.textContent.trim().slice(0, 60),
      project: project ? project.querySelector('.project__title').textContent : undefined,
    });
  });
}

/* Hamburger menu (tablet + phone): opens/closes the panel with all navigation links.
   Closes on link click, outside click, Escape, or when the window becomes wide. */
function initMobileMenu() {
  const navbar = document.querySelector('.navbar');
  const toggle = navbar && navbar.querySelector('.navbar__toggle');
  if (!toggle) return;
  const menu = navbar.querySelector('.navbar__menu');

  function setOpen(open, returnFocus) {
    navbar.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    if (open) menu.querySelector('a').focus();
    else if (returnFocus) toggle.focus();
  }

  toggle.addEventListener('click', () => setOpen(!navbar.classList.contains('is-open')));
  menu.addEventListener('click', (event) => { if (event.target.closest('a')) setOpen(false); });
  document.addEventListener('click', (event) => {
    if (navbar.classList.contains('is-open') && !navbar.contains(event.target)) setOpen(false);
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && navbar.classList.contains('is-open')) setOpen(false, true);
  });
  window.matchMedia('(min-width: 1025px)').addEventListener('change', (event) => { if (event.matches) setOpen(false); });
}

initNavbarShadow();
initMobileMenu();
initStickyCta();
initTracking();
