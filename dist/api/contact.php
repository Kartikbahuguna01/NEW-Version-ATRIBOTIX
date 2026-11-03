<?php
declare(strict_types=1);
/* contact.php — receives the Contact Us form on the home page. */
require __DIR__ . '/bootstrap.php';
guard_post('contact');

$input = [
    'name'    => clean_line($_POST['name'] ?? '', 80),
    'email'   => clean_line($_POST['email'] ?? '', 120),
    'mobile'  => clean_line($_POST['mobile'] ?? '', 20),
    'message' => clean_text($_POST['message'] ?? '', 2000),
];
$errors = validate_common($input);
if (mb_strlen($input['message']) < 10) { $errors['message'] = 'Please write a message of at least 10 characters.'; }
if ($errors) { json_response(['ok' => false, 'errors' => $errors, 'message' => 'Please fix the highlighted fields.'], 422); }

save_submission('contact', $input);
notify('New website enquiry from ' . $input['name'],
    "Name: {$input['name']}\nEmail: {$input['email']}\nMobile: {$input['mobile']}\n\nMessage:\n{$input['message']}\n",
    $input['email']);

$_SESSION['thank_you'] = 'contact';                    // lets thank-you.php know a real submission happened
json_response(['ok' => true, 'redirect' => 'thank-you.php']);
