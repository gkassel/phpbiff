<?php
/**
 * Test suite for the base server connection class.
 *
 * Copyright (c) SG Kassel, 2010. All rights reserved.
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
 *
 * PHP Version 5.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../../bootstrap.php');
require_once(APPLICATION_PATH . '/modules/mockserver.php');
require_once(APPLICATION_PATH . '/modules/serverconnection.php');

/**
 * Test suite for the base server connection class.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class BaseServerConnectionTests extends PHPUnit_Framework_TestCase {
    /**
     * Test the constructor method.
     *
     * @return NULL
     */
    public function testConstructor() {
        $connection = new BaseServerConnection();
    }

    /**
     * Test accessor methods.
     *
     * @depends testConstructor
     *
     * @return NULL
     */
    public function testAccessors() {
        $connection = new BaseServerConnection();
        // Check the set methods.
        $connection->setHostname('localhost');
        $connection->setPort(110);
        $connection->setTimeout(10);
        // Verify the sets were successful.
        $this->assertEquals('localhost', $connection->getHostname());
        $this->assertEquals(110, $connection->getPort());
        $this->assertEquals(10, $connection->getTimeout());
    }

    /**
     * Test the unimplemented isAlive function. (For coverage completeness.)
     *
     * @depends testConstructor
     * @expectedException RuntimeException
     *
     * @return NULL
     */
    public function testIsAlive() {
        $connection = new BaseServerConnection();
        $connection->isAlive();
    }

    /**
     * Test the close function on a disconnected connection.
     *
     * @depends testConstructor
     *
     * @return NULL
     */
    public function testCloseDisconnected() {
        $connection = new BaseServerConnection();
        $connection->close();
    }

    /**
     * Test the unimplemented login function. (For coverage completeness.)
     *
     * @depends testConstructor
     * @expectedException RuntimeException
     *
     * @return NULL
     */
    public function testLogin() {
        $connection = new BaseServerConnection();
        $connection->login('testuser', 'testpassword');
    }

    /**
     * Test the unimplemented messageCount function.
     * (For coverage completeness.)
     *
     * @depends testConstructor
     * @expectedException RuntimeException
     *
     * @return NULL
     */
    public function testMessageCount() {
        $connection = new BaseServerConnection();
        $connection->messageCount();
    }

    /**
     * Test the open function.
     *
     * @depends testConstructor
     *
     * @return NULL
     */
    public function testOpen() {
        // Make a mock server to test against.
        $mockServer = new BaseMockServer('127.0.0.1', 10000);
        $mockServer->start();

        // Make sure the mock server is stopped on error.
        try {
            // Fork the current test process in order to create a
            // client to the server.
            $pid = pcntl_fork();

            // Proceed whether we're the child or the parent.
            if ($pid) {
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new BaseServerConnection();
                $connection->setHostname('127.0.0.1');
                $connection->setPort(10000);
                $connection->setTimeout(10);

                // Open a connection.
                $connection->open();

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED)) {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, then close it, and stop the
                // server.
                $mockServer->startCommunication();
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
     * Test the destructor close connection failsafe.
     *
     * @depends testOpen
     *
     * @return NULL
     */
    public function testDestructorClose() {
        // Make a mock server to test against.
        $mockServer = new BaseMockServer('127.0.0.1', 10000);
        $mockServer->start();

        // Make sure the mock server is stopped on error.
        try {
            // Fork the current test process in order to create a
            // client to the server.
            $pid = pcntl_fork();

            // Proceed whether we're the child or the parent.
            if ($pid) {
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new BaseServerConnection();
                $connection->setHostname('127.0.0.1');
                $connection->setPort(10000);
                $connection->setTimeout(10);

                // Open a connection.
                $connection->open();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED)) {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, then close it, and stop the
                // server.
                $mockServer->startCommunication();
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
     * Test the setTimeout function on an open connection.
     *
     * @depends testOpen
     *
     * @return NULL
     */
    public function testSetTimeoutOpenConnection() {
        // Make a mock server to test against.
        $mockServer = new BaseMockServer('127.0.0.1', 10000);
        $mockServer->start();

        // Make sure the mock server is stopped on error.
        try {
            // Fork the current test process in order to create a
            // client to the server.
            $pid = pcntl_fork();

            // Proceed whether we're the child or the parent.
            if ($pid) {
                // We're the parent process testing the connection.

                // Set up the connection to test.
                $connection = new BaseServerConnection();
                $connection->setHostname('127.0.0.1');
                $connection->setPort(10000);
                $connection->setTimeout(10);

                // Open a connection.
                $connection->open();

                // Set a new timeout.
                $connection->setTimeout(20);

                // Close the connection.
                $connection->close();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED)) {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child process running the mock server.

                // Wait for a connection, then close it, and stop the
                // server.
                $mockServer->startCommunication();
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
