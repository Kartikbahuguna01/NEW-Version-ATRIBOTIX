const FALLBACK_CONTACT = 'Please try again, or contact us on atribotix@gmail.com / +91 9794982969.';
const REQUEST_TIMEOUT_MS = 20000;
function fieldMessage(field) {
const label = (field.dataset.label || field.name).toLowerCase();
if (field.validity.valueMissing) return `Please enter your ${label}.`;
if (field.validity.tooShort) return `Your ${label} is too short.`;
if (field.validity.typeMismatch || field.validity.patternMismatch) return `Please enter a valid ${label}.`;
return field.validationMessage;
}
function setFieldError(form, name, message) {
const input = form.elements[name];
const holder = form.querySelector(`[data-error-for="${name}"]`);
if (holder) holder.textContent = message || '';
const target = input && input.type === 'file' ? form.querySelector('.upload') : input;
if (target) {
if (message) target.setAttribute('aria-invalid', 'true'); else target.removeAttribute('aria-invalid');
}
}
function clearErrors(form) {
form.querySelectorAll('[data-error-for]').forEach((holder) => setFieldError(form, holder.dataset.errorFor, ''));
}
function showStatus(element, message, isError) {
element.textContent = message;
element.classList.toggle('is-error', Boolean(isError));
}
async function refreshCsrf(form) {
const response = await fetch('api/csrf.php', { credentials: 'same-origin', headers: { Accept: 'application/json' } });
const data = await response.json();
form.elements.csrf_token.value = data.token;
}
function setupForm({ form, status, kind, extraValidate }) {
const submitButton = form.querySelector('button[type="submit"]');
let sending = false;
refreshCsrf(form).catch(() => {  });
form.addEventListener('input', (event) => {
if (event.target.name) setFieldError(form, event.target.name, '');
});
form.addEventListener('submit', async (event) => {
event.preventDefault();
if (sending) return;
clearErrors(form);
showStatus(status, '', false);
const problems = {};
Array.from(form.elements).forEach((field) => {
if (field.name && field.willValidate && !field.checkValidity()) problems[field.name] = fieldMessage(field);
});
Object.assign(problems, extraValidate ? extraValidate() : {});
const names = Object.keys(problems);
if (names.length) {
names.forEach((name) => setFieldError(form, name, problems[name]));
showStatus(status, 'Please fix the highlighted fields.', true);
const first = form.elements[names[0]];
if (first && first.focus && first.type !== 'file') first.focus();
return;
}
sending = true;
submitButton.disabled = true;
submitButton.classList.add('is-loading');
form.setAttribute('aria-busy', 'true');
showStatus(status, 'Sending…', false);
const controller = new AbortController();
const timer = setTimeout(() => controller.abort(), REQUEST_TIMEOUT_MS);
let redirecting = false;
try {
if (!form.elements.csrf_token.value) await refreshCsrf(form);
const response = await fetch(form.action, {
method: 'POST', body: new FormData(form), credentials: 'same-origin',
headers: { Accept: 'application/json' }, signal: controller.signal,
});
const data = await response.json().catch(() => null);
if (response.ok && data && data.ok) {
if (window.track) window.track('generate_lead', { form_type: kind });
redirecting = true;
window.location.assign(data.redirect || 'thank-you.php');
return;
}
if (data && data.errors) Object.keys(data.errors).forEach((name) => setFieldError(form, name, data.errors[name]));
if (response.status === 403) refreshCsrf(form).catch(() => {});
showStatus(status, (data && data.message) || `Something went wrong. ${FALLBACK_CONTACT}`, true);
} catch (error) {
const timedOut = error && error.name === 'AbortError';
showStatus(status, `${timedOut ? 'The request took too long.' : 'We could not reach the server.'} ${FALLBACK_CONTACT}`, true);
} finally {
clearTimeout(timer);
if (!redirecting) {
sending = false;
submitButton.disabled = false;
submitButton.classList.remove('is-loading');
form.removeAttribute('aria-busy');
}
}
});
}
