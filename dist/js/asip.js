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
const WHATSAPP_NUMBER = '919794982969';
const BUY_MESSAGE = 'I want to buy this student project listed on AtriBotix website. Please share details.';
document.querySelectorAll('[data-whatsapp]').forEach((button) => {
const link = `https://wa.me/${WHATSAPP_NUMBER}`;
button.href = button.dataset.whatsapp === 'buy' ? `${link}?text=${encodeURIComponent(BUY_MESSAGE)}` : link;
button.target = '_blank';
button.rel = 'noopener';
});
