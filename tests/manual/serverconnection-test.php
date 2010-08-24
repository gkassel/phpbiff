<?php
require_once('../../bootstrap.php');
require_once(APPLICATION_PATH . '/modules/serverconnection.php');

$testauthmethod = 'plain';
$testhostname   = 'localhost';
$testpassword   = 'testpass';
$testprotocol   = 'pop3';
$testport       = 110;
$testtimeout    = 10;
$testusername   = 'testuser';

$serverConnectionFactory = new ServerConnectionFactory();

$testServer =
    $serverConnectionFactory->createConnection($testprotocol, $testhostname,
                                               $testport, $testtimeout);

$testServer->login($testusername, $testpassword, $testauthmethod);

echo $testServer->messageCount() . "\n";
?>
