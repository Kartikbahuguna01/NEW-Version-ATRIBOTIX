<?php
declare(strict_types=1);
/* career.php — receives the Career application (with resume upload). */
require __DIR__ . '/bootstrap.php';
guard_post('career');

const MAX_RESUME_BYTES = 5 * 1024 * 1024;
const RESUME_TYPES = [
    'pdf'  => ['application/pdf'],
    'doc'  => ['application/msword', 'application/x-ole-storage', 'application/octet-stream'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
];

$input = [
    'name'   => clean_line($_POST['name'] ?? '', 80),
    'email'  => clean_line($_POST['email'] ?? '', 120),
    'mobile' => clean_line($_POST['mobile'] ?? '', 20),
];
$errors = validate_common($input);

/* ---- resume checks: upload ok, size, extension and real file type ---- */
$file = $_FILES['resume'] ?? null;
$extension = '';
if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    $errors['resume'] = 'Please upload your resume.';
} elseif ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
    $errors['resume'] = 'The upload failed. Please try again.';
} elseif ($file['size'] > MAX_RESUME_BYTES) {
    $errors['resume'] = 'Resume must be 5 MB or smaller.';
} else {
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(RESUME_TYPES[$extension]) || !in_array($mime, RESUME_TYPES[$extension], true)) {
        $errors['resume'] = 'Resume must be a PDF, DOC or DOCX file.';
    }
}
if ($errors) { json_response(['ok' => false, 'errors' => $errors, 'message' => 'Please fix the highlighted fields.'], 422); }

/* ---- store the resume outside the web root under a random name ---- */
$stored = bin2hex(random_bytes(16)) . '.' . $extension;
if (!move_uploaded_file($file['tmp_name'], storage_path('resumes') . '/' . $stored)) {
    error_log('AtriBotix: could not store resume');
    json_response(['ok' => false, 'message' => 'We could not save your application. Please try again later.'], 500);
}
@chmod(storage_path('resumes') . '/' . $stored, 0640);

save_submission('career', $input + ['resume_file' => $stored, 'resume_original_name' => clean_line($file['name'], 120)]);
notify('New career application from ' . $input['name'],
    "Name: {$input['name']}\nEmail: {$input['email']}\nMobile: {$input['mobile']}\nResume saved as: {$stored}\n(see the storage folder on the server)\n",
    $input['email']);

$_SESSION['thank_you'] = 'career';
json_response(['ok' => true, 'redirect' => 'thank-you.php']);
