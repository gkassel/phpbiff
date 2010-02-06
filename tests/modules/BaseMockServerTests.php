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
require_once(APPLICATION_PATH . '/modules/mockserver.php');

class BaseMockServerTests extends PHPUnit_Framework_TestCase
{
    /**
     * Test the constructor method.
     */
    public function testConstructor()
    {
        $mockServer = new BaseMockServer();
    }

    /**
     * Test a server start-stop cycle.
     *
     * @depends testConstructor
     */
    public function testServerStartStop()
    {
        $mockServer = new BaseMockServer('127.0.0.1', 10000);
        $mockServer->start();
        $mockServer->stop();
    }

    /**
     * Test accessor methods.
     *
     * @depends testConstructor
     */
    public function testAccessors()
    {
        $mockServer = new BaseMockServer('127.0.0.1', 10001);
        // Check the get methods.
        $this->assertEquals('127.0.0.1', $mockServer->getAddress());
        $this->assertEquals(10001, $mockServer->getPort());
        // Check the set methods.
        $mockServer->setAddress('192.168.0.1');
        $mockServer->setPort(10002);
        // Verify the sets were successful.
        $this->assertEquals('192.168.0.1', $mockServer->getAddress());
        $this->assertEquals(10002, $mockServer->getPort());
    }        

    /**
     * Test establishing a connection with the mock server.
     *
     * @depends testConstructor
     */
    public function testServerConnection()
    {
        $mockServer = new BaseMockServer('127.0.0.1', 10002);
        $mockServer->start();

        // Make sure the server is stopped on error.
        try
        {
            // Fork the current test process in order to create a
            // client to the server.
            $pid = pcntl_fork();

            // Proceed whether we're the child or the parent.
            if ($pid)
            {
                // We're the parent. Wait for a connection, then close it, and
                // stop the server.
                $mockServer->startCommunication();
                $mockServer->endCommunication();
                $mockServer->stop();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child. Try to establish a connection.
                if(!($socket = fsockopen('127.0.0.1', 10002)))
                {
                    $this->fail("Could not open connection.");
                    exit(0);
                }

                // Close the connection.
                fclose($socket);

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
     * Test a single server to client communication.
     *
     * @depends testServerConnection
     */
    public function testServerToClientCommunication()
    {
        $mockServer = new BaseMockServer('127.0.0.1', 10003);
        $mockServer->start();

        // Make sure the server is stopped on error.
        try
        {
            // Fork the current test process in order to create a
            // client to the server.
            $pid = pcntl_fork();

            // Proceed whether we're the child or the parent.
            if ($pid)
            {
                // We're the parent. Wait for a connection, send the test
                // string, close the connection, and stop the server.
                $mockServer->startCommunication();
                $result = $mockServer->emit("Welcome to the mock server!");
                $this->assertEquals(true, $result);
                $mockServer->endCommunication();
                $mockServer->stop();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child. Try to establish a connection.
                if(!($socket = fsockopen('127.0.0.1', 10003)))
                {
                    $this->fail("Could not open connection.");
                    exit(0);
                }

                // Read the string sent by the server.
                $readMessage = fgets($socket);

                // Close the connection.
                fclose($socket);

                // Check that there was a message read.
                if(!$readMessage)
                {
                    $this->fail("Could not read any message from the server.");
                }

                // Check that the message read is correct.
                $this->assertEquals("Welcome to the mock server!\r\n",
                                    $readMessage);

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
     * Test a single client to server communication.
     *
     * @depends testServerConnection
     */
    public function testClientToServerCommunication()
    {
        $mockServer = new BaseMockServer('127.0.0.1', 10004);
        $mockServer->start();

        // Make sure the server is stopped on error.
        try
        {
            // Fork the current test process in order to create a
            // client to the server.
            $pid = pcntl_fork();

            // Proceed whether we're the child or the parent.
            if ($pid)
            {
                // We're the parent. Wait for a connection, send the test
                // string, close the connection, and stop the server.
                $mockServer->startCommunication();
                $result = $mockServer->read();
                $mockServer->endCommunication();
                $mockServer->stop();

                // Check the read string.
                $this->assertEquals("Client says hi!", $result);

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child. Try to establish a connection.
                if(!($socket = fsockopen('127.0.0.1', 10004)))
                {
                    $this->fail("Could not open connection.");
                    exit(0);
                }

                // Send a string to the server.
                if (!fwrite($socket, "Client says hi!\r\n"))
                {
                    $this->fail("Could not write message.");
                }

                // Close the connection.
                fclose($socket);

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
     * Test a two-way communication.
     *
     * @depends testClientToServerCommunication
     * @depends testServerToClientCommunication
     */
    public function testTwoWayCommunication()
    {
        $mockServer = new BaseMockServer('127.0.0.1', 10005);
        $mockServer->start();

        // Make sure the server is stopped on error.
        try
        {
            // Fork the current test process in order to create a
            // client to the server.
            $pid = pcntl_fork();

            // Proceed whether we're the child or the parent.
            if ($pid)
            {
                // We're the parent. Wait for a connection, send the test
                // string, close the connection, and stop the server.
                $mockServer->startCommunication();
                $result = $mockServer->emit("Welcome to the mock server!");                
                $this->assertEquals(true, $result);
                $result = $mockServer->read();
                $this->assertEquals("Client says hi!", $result);
                $result = $mockServer->emit("Server says goodbye!");
                $this->assertEquals(true, $result);
                $result = $mockServer->read();
                $this->assertEquals("Client says goodbye!", $result);
                $mockServer->endCommunication();
                $mockServer->stop();

                // Check if there was a problem with the child process.
                if (!pcntl_waitpid($pid, $status, WUNTRACED))
                {
                    $this->fail("There was a problem with the child test " .
                                "process.");
                }
            } else {
                // We're the child. Try to establish a connection.
                $childFailMessage = '';
                if(!($socket = fsockopen('127.0.0.1', 10005)))
                {
                    $this->fail("Could not open connection.");
                    exit(0);
                }

                // Read the greeting sent by the server.
                $serverGreeting = fgets($socket);
                $this->assertEquals("Welcome to the mock server!\r\n",
                                    $serverGreeting); 

                // Send a reply to the server.
                if (!fwrite($socket, "Client says hi!\r\n"))
                {
                    fclose($socket);
                    $this->fail("Could not send reply message.");
                }

                // Read the reply sent by the server.
                $reply = fgets($socket);
                $this->assertEquals("Server says goodbye!\r\n", $reply);

                // Send a final reply to the server.
                if (!fwrite($socket, "Client says goodbye!\r\n"))
                {
                    fclose($socket);
                    $this->fail("Could not send final message.");
                }

                // Close the connection.
                fclose($socket);

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
     * Test the no open socket failure case for emit.
     *
     * @depends testServerConnection
     */
    public function testEmitNoOpenSocket()
    {
        $mockServer = new BaseMockServer('127.0.0.1', 10005);
        $result = $mockServer->emit('test');
        $this->assertEquals(false, $result);
    }

    /**
     * Test the failed write failure case for emit.
     *
     * @depends testServerConnection
     */
    /**
     * Test a single server to client communication.
     *
     * @depends testServerConnection
     */
    public function testEmitFailedWrite()
    {
        $mockServer = new BaseMockServer('127.0.0.1', 10006);
        $mockServer->start();

        // Make sure the server is stopped on error.
        try
        {
            // Fork the current test process in order to create a
            // client to the server.
            $pid = pcntl_fork();

            // Proceed whether we're the child or the parent.
            if ($pid)
            {
                // We're the parent. Wait for a connection, and try to send the
                // test string.
                $mockServer->startCommunication();
                $result = $mockServer->emit("Welcome to the mock server!");
                // This emit should fail.
                $result = $mockServer->emit("Welcome to the mock server!");
                $this->assertEquals(false, $result);
                $mockServer->endCommunication();
                $mockServer->stop();
            } else {
                // We're the child. Try to establish a connection.
                if(!($socket = fsockopen('127.0.0.1', 10006)))
                {
                    $this->fail("Could not open connection.");
                    exit(0);
                }

                // Immediately close the connection.
                fclose($socket);

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
     * Test the no open socket failure case for read.
     *
     * @depends testServerConnection
     */
    public function testReadNoOpenSocket()
    {
        $mockServer = new BaseMockServer('127.0.0.1', 10007);
        $result = $mockServer->read();
        $this->assertEquals(false, $result);
    }

    /**
     * Test the socket_accept failure case for startCommunication.
     *
     * @depends testServerConnection
     * @expectedException ServerException
     */
    public function testStartCommunicationFailedSocketAccept()
    {
        $mockServer = new BaseMockServer('127.0.0.1', 10008);
        $mockServer->startCommunication();
        $this->assertEquals(false, $result);
    }
}
?>
