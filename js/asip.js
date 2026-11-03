/* asip.js — ASIP page: choose which blocks animate on scroll */
initReveal([
  '.section-head',
  '.project',
  '.explore',
  '.about-card',
  '.value',
  '.footer__brand',
  '.footer__col',
  '.footer__wordmark',
].join(', '));

/* WhatsApp buttons ------------------------------------------------
   Buy Now  -> opens WhatsApp with a ready message.
   Contact  -> opens WhatsApp with no message.
   Change the number here (country code + number, digits only). */
const WHATSAPP_NUMBER = '919794982969';
const BUY_MESSAGE = 'I want to buy this student project listed on AtriBotix website. Please share details.';

document.querySelectorAll('[data-whatsapp]').forEach((button) => {
  const link = `https://wa.me/${WHATSAPP_NUMBER}`;
  button.href = button.dataset.whatsapp === 'buy' ? `${link}?text=${encodeURIComponent(BUY_MESSAGE)}` : link;
  button.target = '_blank';
  button.rel = 'noopener';
});
