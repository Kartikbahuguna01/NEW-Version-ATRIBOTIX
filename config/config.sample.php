<?php
/* config.sample.php — copy to config.php and edit.
   IMPORTANT: put the real config.php OUTSIDE the public web folder
   (for example one level above public_html) so it can never be downloaded. */
return [
    'mail_to'     => 'atribotix@gmail.com',        // where enquiries and applications are delivered
    'mail_from'   => 'no-reply@atribotix.in',      // must be an address on your own domain
    'site_host'   => 'atribotix.in',               // forms are only accepted from this host
    // Folder for saved submissions + resumes. Must be OUTSIDE the public web folder and writable.
    'storage_dir' => dirname(__DIR__, 2) . '/atribotix-storage',
];
