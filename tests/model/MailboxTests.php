<?php
/*
 * Copyright (c) Geoff Kassel, 2010. All rights reserved.
 *
 * This file is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE TO ANY PARTY FOR DIRECT, INDIRECT,
 * SPECIAL, INCIDENTAL, OR CONSEQUENTIAL DAMAGES ARISING OUT OF THE USE OF
 * THIS CODE, EVEN IF THE AUTHOR HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * THE AUTHOR SPECIFICALLY DISCLAIMS ANY WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
 * PARTICULAR PURPOSE.  THE CODE PROVIDED HEREUNDER IS ON AN "AS IS" BASIS,
 * AND THERE IS NO OBLIGATION WHATSOEVER TO PROVIDE MAINTENANCE,
 * SUPPORT, UPDATES, ENHANCEMENTS, OR MODIFICATIONS.
 *
 * Email: gkassel_at_users_dot_sourceforge_dot_net
 */
/**
 * Test case suite for the mailbox model class.
 *
 * @author Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright Copyright (c) Geoff Kassel, 2010. All rights reserved.
 * @license http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @package phpbiff
 */

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../testsettings.php');
require_once(APPLICATION_PATH . '/model/mailbox.php');
require_once(APPLICATION_PATH . '/modules/mockserver.php');

class MailboxTests extends PHPUnit_Framework_TestCase
{
    /**
     * Test the constructor method.
     */
    public function testConstructor()
    {
        $connection = new Mailbox();
    }

    /**
     * Test accessor methods.
     *
     * @depends testConstructor
     */
    public function testAccessors()
    {
        // Set up the mailbox to test.
        $mailbox = new Mailbox('Test account', 'plain', 10, 1,
                               '127.0.0.1', 'testpass', 10000,
                               'pop3', 10, 'testuser');

        // Check the get methods.
        $this->assertEquals('Test account', $mailbox->getAccountName());
        $this->assertEquals('plain', $mailbox->getAuthenticationMethod());
        $this->assertEquals(10, $mailbox->getCheckFrequency());
        $this->assertEquals(1, $mailbox->getDisplayOrder());
        $this->assertEquals('127.0.0.1', $mailbox->getHostname());
        $this->assertEquals(NULL, $mailbox->getLastChecked());
        $this->assertEquals(0, $mailbox->getMessageCount());
        $this->assertEquals(10000, $mailbox->getPort());
        $this->assertEquals('pop3', $mailbox->getProtocol());
        $this->assertEquals(0, $mailbox->getReadMessageCount());
        $this->assertEquals('error', $mailbox->getStatus());
        $this->assertEquals(10, $mailbox->getTimeout());
        $this->assertEquals('testuser', $mailbox->getUsername());

        // Check the set methods.
        $mailbox->setAccountName('New account');
        $mailbox->setAuthenticationMethod('login');
        $mailbox->setCheckFrequency(20);
        $mailbox->setDisplayOrder(2);
        $mailbox->setHostname('localhost');
        $mailbox->setPassword('newpass');
        $mailbox->setPort(110);
        $mailbox->setProtocol('pop3s');
        $mailbox->setTimeout(20);
        $mailbox->setUsername('newuser');

        // Verify the sets were successful.
        $this->assertEquals('New account', $mailbox->getAccountName());
        $this->assertEquals('login', $mailbox->getAuthenticationMethod());
        $this->assertEquals(20, $mailbox->getCheckFrequency());
        $this->assertEquals(2, $mailbox->getDisplayOrder());
        $this->assertEquals('localhost', $mailbox->getHostname());
        $this->assertEquals(110, $mailbox->getPort());
        $this->assertEquals('pop3s', $mailbox->getProtocol());
        $this->assertEquals(20, $mailbox->getTimeout());
        $this->assertEquals('newuser', $mailbox->getUsername());
    }

    /**
     * Test the check function with a valid account and all unread mail.
     *
     * @depends testConstructor
     */
    public function testCheckValidAllUnreadMail()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10000);
        $mockServer->start();

        // Make sure the mock server is stopped on error.
        try
        {
            // Fork the current test process in order to create a
            // client to the server.
            $pid = pcntl_fork();

            // Proceed whether we're the child or the parent.
            if ($pid)
            {
                // We're the parent process testing the mailbox.

                // Set up the mailbox to test.
                $mailbox = new Mailbox('Test account', 'plain', 10, 1,
                                       '127.0.0.1', 'testpass', 10000,
                                       'pop3', 10, 'testuser');

                // Issue a check.
                $mailbox->check();

                // Check the state of the mailbox.

                // It should be in the new mail state, with a
                // unread message count of 123, and a read message count of 0.
                $this->assertEquals('new mail', $mailbox->getStatus());
                $this->assertEquals(123, $mailbox->getMessageCount());
                $this->assertEquals(0, $mailbox->getReadMessageCount());

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, read a command off, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
                $this->assertEquals('USER testuser', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
                $this->assertEquals('PASS testpass', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
                $this->assertEquals('STAT', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK 123 12300'));
                $mockServer->endCommunication();
                $mockServer->stop();

                // Exit.
                exit(0);
            }
        } catch (Exception $e) {
            // Stop the mock server, and re-raise the exception.
            $mockServer->stop();
            throw $e;
        }
    }

    /**
     * Test the check function with an invalid account.
     *
     * @depends testConstructor
     */
    public function testCheckInvalid()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10000);
        $mockServer->start();

        // Make sure the mock server is stopped on error.
        try
        {
            // Fork the current test process in order to create a
            // client to the server.
            $pid = pcntl_fork();

            // Proceed whether we're the child or the parent.
            if ($pid)
            {
                // We're the parent process testing the mailbox.

                // Set up the mailbox to test.
                $mailbox = new Mailbox('Test account', 'plain', 10, 1,
                                       '127.0.0.1', 'testpass', 10000,
                                       'pop3', 10, 'testuser');

                // Issue a check.
                $mailbox->check();

                // Check the state of the mailbox.

                // It should be in the error state, with an unread
                // message count of 0, and a read message count of 0.
                $this->assertEquals('error', $mailbox->getStatus());
                $this->assertEquals(0, $mailbox->getMessageCount());
                $this->assertEquals(0, $mailbox->getReadMessageCount());

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, read a command off, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
                $this->assertEquals('USER testuser', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
                $this->assertEquals('PASS testpass', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('-ERR'));
                $mockServer->endCommunication();
                $mockServer->stop();

                // Exit.
                exit(0);
            }
        } catch (Exception $e) {
            // Stop the mock server, and re-raise the exception.
            $mockServer->stop();
            throw $e;
        }
    }

    /**
     * Test the check function with a valid account and no mail.
     *
     * @depends testConstructor
     */
    public function testCheckValidNoMail()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10001);
        $mockServer->start();

        // Make sure the mock server is stopped on error.
        try
        {
            // Fork the current test process in order to create a
            // client to the server.
            $pid = pcntl_fork();

            // Proceed whether we're the child or the parent.
            if ($pid)
            {
                // We're the parent process testing the mailbox.

                // Set up the mailbox to test.
                $mailbox = new Mailbox('Test account', 'plain', 10, 1,
                                       '127.0.0.1', 'testpass', 10001,
                                       'pop3', 10, 'testuser');

                // Issue a check.
                $mailbox->check();

                // Check the state of the mailbox.

                // It should be in the no mail state, with an unread
                // message count of 0, and a read message count of 0.
                $this->assertEquals('no mail', $mailbox->getStatus());
                $this->assertEquals(0, $mailbox->getMessageCount());
                $this->assertEquals(0, $mailbox->getReadMessageCount());

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, read a command off, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
                $this->assertEquals('USER testuser', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
                $this->assertEquals('PASS testpass', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
                $this->assertEquals('STAT', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK 0 0'));
                $mockServer->endCommunication();
                $mockServer->stop();

                // Exit.
                exit(0);
            }
        } catch (Exception $e) {
            // Stop the mock server, and re-raise the exception.
            $mockServer->stop();
            throw $e;
        }
    }

    /**
     * Test the markAsRead function with an unchecked account.
     *
     * @depends testConstructor
     */
    public function testMarkAsReadUnchecked()
    {
        // Set up the mailbox to test.
        $mailbox = new Mailbox('Test account', 'plain', 10, 1,
                               '127.0.0.1', 'testpass', 10002,
                               'pop3', 10, 'testuser');

        // Mark any current mail as read.
        $mailbox->markAsRead();

        // Check the state of the mailbox.

        // It should be in the error state, with a unread message
        // count of 0, and a read message count of 0.
        $this->assertEquals('error', $mailbox->getStatus());
        $this->assertEquals(0, $mailbox->getMessageCount());
        $this->assertEquals(0, $mailbox->getReadMessageCount());
    }

    /**
     * Test the markAsRead function with a checked account.
     *
     * @depends testCheckValidAllUnreadMail
     */
    public function testMarkAsReadChecked()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10003);
        $mockServer->start();

        // Make sure the mock server is stopped on error.
        try
        {
            // Fork the current test process in order to create a
            // client to the server.
            $pid = pcntl_fork();

            // Proceed whether we're the child or the parent.
            if ($pid)
            {
                // We're the parent process testing the mailbox.

                // Set up the mailbox to test.
                $mailbox = new Mailbox('Test account', 'plain', 10, 1,
                                       '127.0.0.1', 'testpass', 10003,
                                       'pop3', 10, 'testuser');

                // Issue a check.
                $mailbox->check();

                // Check the state of the mailbox.

                // It should be in the new mail state, with a
                // unread message count of 123, and a read message count of 0.
                $this->assertEquals('new mail', $mailbox->getStatus());
                $this->assertEquals(123, $mailbox->getMessageCount());
                $this->assertEquals(0, $mailbox->getReadMessageCount());

                // Mark any current mail as read.
                $mailbox->markAsRead();

                // Check the state of the mailbox.

                // It should be in the old mail state, with a
                // unread message count of 123, and a read message count of 123.
                $this->assertEquals('old mail', $mailbox->getStatus());
                $this->assertEquals(123, $mailbox->getMessageCount());
                $this->assertEquals(123, $mailbox->getReadMessageCount());

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, read a command off, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
                $this->assertEquals('USER testuser', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
                $this->assertEquals('PASS testpass', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
                $this->assertEquals('STAT', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK 123 12300'));
                $mockServer->endCommunication();
                $mockServer->stop();

                // Exit.
                exit(0);
            }
        } catch (Exception $e) {
            // Stop the mock server, and re-raise the exception.
            $mockServer->stop();
            throw $e;
        }
    }

    /**
     * Test the check function with a valid account and all read mail.
     *
     * @depends testConstructor
     */
    public function testCheckValidAllReadMail()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10004);
        $mockServer->start();

        // Make sure the mock server is stopped on error.
        try
        {
            // Fork the current test process in order to create a
            // client to the server.
            $pid = pcntl_fork();

            // Proceed whether we're the child or the parent.
            if ($pid)
            {
                // We're the parent process testing the mailbox.

                // Set up the mailbox to test.
                $mailbox = new Mailbox('Test account', 'plain', 10, 1,
                                       '127.0.0.1', 'testpass', 10004,
                                       'pop3', 10, 'testuser');

                // Issue a check.
                $mailbox->check();

                // Check the state of the mailbox.

                // It should be in the new mail state, with a
                // unread message count of 123, and a read message count of 0.
                $this->assertEquals('new mail', $mailbox->getStatus());
                $this->assertEquals(123, $mailbox->getMessageCount());
                $this->assertEquals(0, $mailbox->getReadMessageCount());

                // Mark the current mail as read.
                $mailbox->markAsRead();

                // Issue another check.
                $mailbox->check();

                // Check the state of the mailbox.

                // It should be in the old mail state, with a
                // unread message count of 123, and a read message count of 0.
                $this->assertEquals('old mail', $mailbox->getStatus());
                $this->assertEquals(123, $mailbox->getMessageCount());
                $this->assertEquals(123, $mailbox->getReadMessageCount());

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, read a command off, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
                $this->assertEquals('USER testuser', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
                $this->assertEquals('PASS testpass', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
                $this->assertEquals('STAT', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK 123 12300'));
                $mockServer->endCommunication();
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
                $this->assertEquals('USER testuser', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
                $this->assertEquals('PASS testpass', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
                $this->assertEquals('STAT', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK 123 12300'));
                $mockServer->endCommunication();
                $mockServer->stop();

                // Exit.
                exit(0);
            }
        } catch (Exception $e) {
            // Stop the mock server, and re-raise the exception.
            $mockServer->stop();
            throw $e;
        }
    }
}
?>
