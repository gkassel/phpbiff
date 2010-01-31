<?php
// 'hello' => '68656c6c6f'

require_once('../testsettings.php');
require_once(APPLICATION_PATH . '/modules/hex2bin.php');

echo bin2hex('hello') . "\n";
echo hex2bin('68656c6c6f') . "\n";

?>
