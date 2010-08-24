<?php
$key = 'mailboxes';
$filename = hash('sha256', $key, $raw_output = FALSE);

echo "Key: $key Filename: $filename";
?>
