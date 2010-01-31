<?php
$key = 'mailboxes';
$filename = hash('sha256', $key, $raw_output = false);

echo "Key: $key Filename: $filename";
?>
