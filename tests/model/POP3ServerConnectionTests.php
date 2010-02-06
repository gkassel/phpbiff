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
 * Test case suite for the base server conection class.
 *
 * @author Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright Copyright (c) Geoff Kassel, 2010. All rights reserved.
 * @license http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @package phpbiff
 */

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../testsettings.php');
require_once(APPLICATION_PATH . '/model/serverconnection.php');
require_once(APPLICATION_PATH . '/modules/mockserver.php');

class POP3ServerConnectionTests extends PHPUnit_Framework_TestCase
{
    /**
     * Test the constructor method.
     */
    public function testConstructor()
    {
        $connection = new POP3ServerConnection();
    }

    /**
     * Test an open and close cycle.
     *
     * @depends testConstructor
     */
    public function testOpenClose()
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10000, 10);

                // Open a connection.
                $connection->open();

                // Close the connection.
                $connection->close();

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
     * Test the command function.
     *
     * @depends testOpenClose
     */
    public function testCommand()
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10001, 10);

                // Open a connection.
                $connection->open();

                // Send a NOOP command.
                $result = rtrim($connection->command('NOOP'), "\r\n");
                $this->assertEquals('+OK', $result);

                // Close the connection.
                $connection->close();

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
                $this->assertEquals('NOOP', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
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
     * Test the command function against a closed connection.
     *
     * @depends testOpenClose
     */
    public function testCommandClosedConnection()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10002);
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10002, 10);

                // Send a NOOP command.
                $result = rtrim($connection->command('NOOP'), "\r\n");
                $this->assertEquals('+OK', $result);

                // Close the connection.
                $connection->close();

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
                $this->assertEquals('NOOP', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
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
     * Test the isAlive function.
     *
     * @depends testCommand
     */
    public function testIsAlive()
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10003, 10);

                // Open a connection.
                $connection->open();

                // Check if the connection is alive.
                $this->assertEquals(true, $connection->isAlive());

                // Close the connection.
                $connection->close();

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
                $this->assertEquals('NOOP', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
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
     * Test the login function with (unimplemented) APOP authentication.
     *
     * @depends testCommand
     * @depends testSuccessfulResponse
     * @expectedException RuntimeException
     */
    public function testLoginAPOP()
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10004, 10);

                // Open a connection.
                $connection->open();

                // Try to login.
                $result = $connection->login('testuser', 'testpass', 'APOP');

                // Check whether the login was successful.
                $this->assertEquals(true, $result);

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, try to authenticate, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
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
     * Test the login function with (unimplemented) CRAM-MD5 authentication.
     *
     * @depends testCommand
     * @depends testSuccessfulResponse
     * @expectedException RuntimeException
     */
    public function testLoginCRAMMD5()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10005);
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10005, 10);

                // Open a connection.
                $connection->open();

                // Try to login.
                $result = $connection->login('testuser', 'testpass',
                                             'CRAM-MD5');

                // Check whether the login was successful.
                $this->assertEquals(true, $result);

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, try to authenticate, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
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
     * Test the login function with (unimplemented) Digest-MD5 authentication.
     *
     * @depends testCommand
     * @depends testSuccessfulResponse
     * @expectedException RuntimeException
     */
    public function testLoginDigestMD5()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10006);
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10006, 10);

                // Open a connection.
                $connection->open();

                // Try to login.
                $result = $connection->login('testuser', 'testpass',
                                             'DIGEST-MD5');

                // Check whether the login was successful.
                $this->assertEquals(true, $result);

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, try to authenticate, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
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
     * Test the login function with login authentication.
     *
     * @depends testCommand
     * @depends testSuccessfulResponse
     */
    public function testLoginLogin()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10007);
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10007, 10);

                // Open a connection.
                $connection->open();

                // Try to login.
                $result = $connection->login('testuser', 'testpass', 'login');

                // Check whether the login was successful.
                $this->assertEquals(true, $result);

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, try to authenticate, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
                $this->assertEquals('USER testuser', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
                $this->assertEquals('PASS testpass', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
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
     * Test the login function with login authentication and an invalid
     * password format.
     *
     * @depends testCommand
     * @depends testSuccessfulResponse
     * @expectedException LogicException
     */
    public function testLoginLoginInvalidPasswordFormat()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10008);
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10008, 10);

                // Open a connection.
                $connection->open();

                // Try to login.
                $result = $connection->login('testuser', 'testpass', 'login',
                                             'md5');

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, try to authenticate, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
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
     * Test the login function with login authentication and an invalid user.
     *
     * @depends testCommand
     * @depends testSuccessfulResponse
     * @expectedException ConnectionException
     */
    public function testLoginLoginInvalidUser()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10009);
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10009, 10);

                // Open a connection.
                $connection->open();

                // Try to login.
                $result = $connection->login('testuser', 'testpass', 'login');

                // Check whether the login was successful.
                $this->assertEquals(false, $result);

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, try to authenticate, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
                $this->assertEquals('USER testuser', $mockServer->read());
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
     * Test the login function with login authentication and an invalid
     * password.
     *
     * @depends testCommand
     * @depends testSuccessfulResponse
     * @expectedException ConnectionException
     */
    public function testLoginLoginInvalidPassword()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10010);
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10010, 10);

                // Open a connection.
                $connection->open();

                // Try to login.
                $result = $connection->login('testuser', 'testpass', 'login');

                // Check whether the login was successful.
                $this->assertEquals(false, $result);

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, try to authenticate, then close the
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
     * Test the login function with (unimplemented) NTLM authentication.
     *
     * @depends testCommand
     * @depends testSuccessfulResponse
     * @expectedException RuntimeException
     */
    public function testLoginNTLM()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10011);
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10011, 10);

                // Open a connection.
                $connection->open();

                // Try to login.
                $result = $connection->login('testuser', 'testpass',
                                             'NTLM');

                // Check whether the login was successful.
                $this->assertEquals(true, $result);

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, try to authenticate, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
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
     * Test the login function with plain authentication.
     *
     * @depends testCommand
     * @depends testSuccessfulResponse
     */
    public function testLoginPlain()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10012);
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10012, 10);

                // Open a connection.
                $connection->open();

                // Try to login.
                $result = $connection->login('testuser', 'testpass', 'plain');

                // Check whether the login was successful.
                $this->assertEquals(true, $result);

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, try to authenticate, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
                $this->assertEquals('USER testuser', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
                $this->assertEquals('PASS testpass', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
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
     * Test the login function with plain authentication and an invalid
     * password format.
     *
     * @depends testCommand
     * @depends testSuccessfulResponse
     * @expectedException LogicException
     */
    public function testLoginPlainInvalidPasswordFormat()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10013);
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10013, 10);

                // Open a connection.
                $connection->open();

                // Try to login.
                $result = $connection->login('testuser', 'testpass', 'plain',
                                             'md5');

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, try to authenticate, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
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
     * Test the login function with login authentication and an invalid user.
     *
     * @depends testCommand
     * @depends testSuccessfulResponse
     * @expectedException ConnectionException
     */
    public function testLoginPlainInvalidUser()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10014);
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10014, 10);

                // Open a connection.
                $connection->open();

                // Try to login.
                $result = $connection->login('testuser', 'testpass', 'plain');

                // Check whether the login was successful.
                $this->assertEquals(false, $result);

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, try to authenticate, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
                $this->assertEquals('USER testuser', $mockServer->read());
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
     * Test the messageCount function.
     *
     * @depends testLoginPlain
     */
    public function testMessageCount()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10015);
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10015, 10);

                // Open a connection.
                $connection->open();

                // Try to login.
                $result = $connection->login('testuser', 'testpass', 'plain',
                                             'plain');

                // Check whether the login was successful.
                $this->assertEquals(true, $result);

                // Get the message count.
                $this->assertEquals(123, $connection->messageCount());

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, try to authenticate, then close the
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
     * Test the messageCount function unauthenticated.
     *
     * @depends testLoginPlain
     * @expectedException LogicException
     */
    public function testMessageCountUnauthenticated()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10016);
        $mockServer->start();

        $storedException = null;

        // Make sure the mock server is stopped on error.
        try
        {
            // Fork the current test process in order to create a
            // client to the server.
            $pid = pcntl_fork();

            // Proceed whether we're the child or the parent.
            if ($pid)
            {
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10016, 10);

                // Open a connection.
                $connection->open();

                // Try to get the message count.
                $connection->messageCount();

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, try to authenticate, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
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
     * Test the messageCount function with a server error.
     *
     * @depends testMessageCount
     * @expectedException ConnectionException
     */
    public function testMessageCountError()
    {
        // Make a mock server to test against.
        $mockServer = new POP3MockMailServer('127.0.0.1', 10017);
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
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new POP3ServerConnection('127.0.0.1', 10017, 10);

                // Open a connection.
                $connection->open();

                // Try to login.
                $result = $connection->login('testuser', 'testpass', 'plain',
                                             'plain');

                // Check whether the login was successful.
                $this->assertEquals(true, $result);

                // Try to get the message count.
                $connection->messageCount();

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, try to authenticate, then close the
                // server.
                $mockServer->startCommunication();
                $this->assertEquals(true, $mockServer->emit("Welcome!"));
                $this->assertEquals('USER testuser', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
                $this->assertEquals('PASS testpass', $mockServer->read());
                $this->assertEquals(true, $mockServer->emit('+OK'));
                $this->assertEquals('STAT', $mockServer->read());
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
}
?>
