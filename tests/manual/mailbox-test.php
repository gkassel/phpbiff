<?php
require_once('../testsettings.php');
require_once(APPLICATION_PATH . '/model/mailbox.php');

$testaccountname = 'Test account';
$testauthmethod = 'plain';
$testcheckfrequency = 60;
$testdisplayorder = 1;
$testhostname = 'localhost';
$testport = 110;
$testpassword = 'testpass';
$testprotocol = 'pop3';
$testtimeout = '100';
$testusername = 'testuser';

$mailbox = new Mailbox($testaccountname, $testauthmethod, $testcheckfrequency,
                       $testdisplayorder, $testhostname, $testpassword,
                       $testport, $testprotocol, $testtimeout, $testusername);

$mailbox->check();
echo $mailbox->getStatus() . "\n";
echo $mailbox->getMessageCount() . "\n";
$mailbox->markAsRead();
echo $mailbox->getStatus() . "\n";
echo $mailbox->getMessageCount() . "\n";
?>
