/* career.js — Career page: scroll animation + application form checks */

initReveal(['.section-head', '.apply', '.footer__brand', '.footer__col', '.footer__wordmark'].join(', '));

const form = document.getElementById('career-form');
const resume = document.getElementById('resume');
const upload = document.querySelector('.upload');
const uploadTitle = document.querySelector('.upload__title');
const uploadHint = document.querySelector('.upload__hint');

const MAX_SIZE = 5 * 1024 * 1024;            // 5 MB
const ALLOWED_TYPES = ['pdf', 'doc', 'docx'];
const DEFAULT_TITLE = uploadTitle.textContent;
const DEFAULT_HINT = uploadHint.textContent;

/* Returns an error message, or '' when the resume file is fine. */
function checkResume(file) {
  if (!file) return 'Please upload your resume.';
  const type = file.name.split('.').pop().toLowerCase();
  if (!ALLOWED_TYPES.includes(type)) return 'Resume must be a PDF, DOC or DOCX file.';
  if (file.size > MAX_SIZE) return 'Resume must be 5 MB or smaller.';
  return '';
}

/* Show the chosen file name inside the upload box. */
resume.addEventListener('change', () => {
  const file = resume.files[0];
  const error = file ? checkResume(file) : '';
  uploadTitle.textContent = file ? file.name : DEFAULT_TITLE;
  uploadHint.textContent = file ? 'File selected' : DEFAULT_HINT;
  setFieldError(form, 'resume', error);
});

setupForm({
  form,
  status: document.querySelector('.form-status'),
  kind: 'career',
  extraValidate() {
    const error = checkResume(resume.files[0]);
    return error ? { resume: error } : {};
  },
});
