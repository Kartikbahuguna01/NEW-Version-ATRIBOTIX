/* home.js — Home page: scroll animation, FAQ accordion, contact form checks */

initReveal([
  '.section-head', '.stats', '.service', '.why', '.hub-card', '.explore',
  '.review', '.about__chips', '.about__card', '.faq__item', '.contact',
  '.footer__brand', '.footer__col', '.footer__wordmark',
].join(', '));

/* FAQ: one answer open at a time ---------------------------- */
const faqItems = document.querySelectorAll('.faq__item');
faqItems.forEach((item) => {
  const button = item.querySelector('.faq__question');
  button.addEventListener('click', () => {
    const willOpen = !item.classList.contains('is-open');
    faqItems.forEach((other) => {
      other.classList.remove('is-open');
      other.querySelector('.faq__question').setAttribute('aria-expanded', 'false');
    });
    if (willOpen) {
      item.classList.add('is-open');
      button.setAttribute('aria-expanded', 'true');
    }
  });
});

/* Contact form ------------------------------------------------ */
setupForm({
  form: document.getElementById('contact-form'),
  status: document.querySelector('.form-status'),
  kind: 'contact',
});
