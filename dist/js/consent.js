(function () {
const KEY = 'atribotix_consent_v1';
const config = window.ATRIBOTIX_CONFIG || {};
let analyticsLoaded = false;
let banner = null;
function readChoice() {
try { return JSON.parse(localStorage.getItem(KEY)); } catch (error) { return null; }
}
function saveChoice(analytics) {
try { localStorage.setItem(KEY, JSON.stringify({ analytics, date: new Date().toISOString() })); } catch (error) {  }
applyChoice({ analytics });
}
function loadAnalytics() {
if (analyticsLoaded || !config.gaMeasurementId) return;
analyticsLoaded = true;
window.dataLayer = window.dataLayer || [];
window.gtag = function () { window.dataLayer.push(arguments); };
window.gtag('js', new Date());
window.gtag('config', config.gaMeasurementId, { anonymize_ip: true });
const script = document.createElement('script');
script.async = true;
script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(config.gaMeasurementId);
document.head.appendChild(script);
}
function stopAnalytics() {
if (config.gaMeasurementId) window['ga-disable-' + config.gaMeasurementId] = true;
document.cookie.split(';').forEach((cookie) => {
const name = cookie.split('=')[0].trim();
if (name.indexOf('_ga') === 0) document.cookie = name + '=; Max-Age=0; path=/';
});
}
function applyChoice(choice) {
if (choice && choice.analytics) {
if (config.gaMeasurementId) window['ga-disable-' + config.gaMeasurementId] = false;
loadAnalytics();
} else {
stopAnalytics();
}
}
window.track = function (name, params) {
const choice = readChoice();
if (!choice || !choice.analytics || !window.gtag) return;
window.gtag('event', name, params || {});
};
function el(tag, attrs, children) {
const node = document.createElement(tag);
Object.keys(attrs || {}).forEach((key) => {
if (key === 'text') node.textContent = attrs[key]; else node.setAttribute(key, attrs[key]);
});
(children || []).forEach((child) => node.appendChild(child));
return node;
}
function button(label, className, onClick) {
const node = el('button', { type: 'button', class: 'btn ' + className, text: label });
node.addEventListener('click', onClick);
return node;
}
function closeBanner() { if (banner) banner.hidden = true; }
function buildBanner() {
const current = readChoice() || { analytics: false };
const checkbox = el('input', { type: 'checkbox', id: 'consent-analytics' });
checkbox.checked = Boolean(current.analytics);
const options = el('fieldset', { class: 'cookie__options', hidden: '' }, [
el('label', {}, [el('input', { type: 'checkbox', checked: '', disabled: '' }), el('span', { text: 'Essential — needed for the site and forms to work (always on).' })]),
el('label', {}, [checkbox, el('span', { text: 'Analytics — helps us understand which pages and buttons are used.' })]),
]);
options.insertBefore(el('legend', { class: 'sr-only', text: 'Cookie categories' }), options.firstChild);
const customize = button('Customize', 'btn--outline', () => {
const open = options.hidden;
options.hidden = !open;
customize.hidden = open;
save.hidden = !open;
});
const save = button('Save choices', 'btn--primary', () => { saveChoice(checkbox.checked); closeBanner(); });
save.hidden = true;
banner = el('div', { class: 'cookie', role: 'dialog', 'aria-labelledby': 'cookie-title', 'aria-describedby': 'cookie-text', tabindex: '-1' }, [
el('h2', { id: 'cookie-title', text: 'Your privacy choices' }),
el('p', { id: 'cookie-text', text: 'We use essential storage to run this site. With your permission we also use analytics cookies to improve it. ' }),
options,
el('div', { class: 'cookie__actions' }, [
button('Accept all', 'btn--primary', () => { saveChoice(true); closeBanner(); }),
button('Reject non-essential', 'btn--outline', () => { saveChoice(false); closeBanner(); }),
customize,
save,
]),
]);
const link = el('a', { href: 'privacy.html', text: 'Read our Privacy Policy.' });
banner.querySelector('#cookie-text').appendChild(link);
banner.addEventListener('keydown', (event) => { if (event.key === 'Escape' && readChoice()) closeBanner(); });
document.body.appendChild(banner);
}
function openBanner() {
if (banner) banner.remove();
banner = null;
buildBanner();
banner.focus();
}
document.addEventListener('click', (event) => {
if (event.target.closest('[data-cookie-settings]')) openBanner();
});
const saved = readChoice();
if (saved) applyChoice(saved); else buildBanner();
})();
