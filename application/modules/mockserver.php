<?php
/**
 * Mock server classes, for use in testing mail server connection code.
 *
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
 *
 * PHP Version 5.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright 2010 Geoff Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */

/** Import the application settings. */
require_once(dirname(__FILE__) . '/../Bootstrap.php');

/**
 * Exception to indicate problems with the server.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright 2010 Geoff Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class ServerException extends RuntimeException {}

/**
 * Interface class for mock servers.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright 2010 Geoff Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
interface MockServer {
    // Instance attribute accessors.

    /**
     * Returns the current address
     *
     * @return string The current address.
     */
    public function getAddress();

    /**
     * Returns the current port.
     *
     * @return int The current port.
     */
    public function getPort();

    /**
     * Sets the address to that given.
     *
     * @param string $address The new address.
     *
     * @return NULL
     */
    public function setAddress($address);

    /**
     * Sets the port to that given.
     *
     * @param string $port The new port.
     *
     * @return NULL
     */
    public function setPort($port);

    // Instance methods.

    /**
     * Method to emit a string (terminated with '\r\n') on the current
     * connection. Returns TRUE on success; FALSE otherwise.
     *
     * @param string $string String to emit.
     *
     * @return bool          Whether the emit operation succeeded.
     */
    public function emit($string);

    /**
     * Method to end any communication on the current connection,
     * and close the socket.
     *
     * @return NULL
     */
    public function endCommunication();

    /**
     * Function to read a line (terminated by \r\n or \n) from the current
     * connection. Returns the read string, stripped of the terminator.
     *
     * @return string String read from the connection.
     */
    public function read();

    /**
     * Method to start the server using the stored server details.
     *
     * @return NULL
     */
    public function start();

    /**
     * Method to accept incoming connections and start communication.
     *
     * @return NULL
     */
    public function startCommunication();

    /**
     * Method to stop the server.
     *
     * @return NULL
     */
    public function stop();
}

/**
 * Concrete class that defines attributes and methods common to all mock
 * servers.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright 2010 Geoff Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class BaseMockServer implements MockServer {
    // Instance attributes.

    /**
     * Server bind address.
     *
     * @var string
     */
    protected $address;

    /**
     * Server port.
     *
     * @var int
     */
    protected $port;

    /**
     * Current connection socket.
     *
     * @var socket
     */
    protected $connectionSocket;

    /**
     * Socket upon which the server listens for new connections.
     *
     * @var socket
     */
    protected $listeningSocket;

    /**
     * Whether there is a connection socket open or not.
     *
     * @var bool
     */
    protected $connectionSocketOpen;

    /**
     * Whether the listening socket is open or not.
     *
     * @var bool
     */
    protected $listeningSocketOpen;

    // Instance attribute accessors.

    /**
     * Returns the current address
     *
     * @return string The current address.
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * Returns the current port.
     *
     * @return int The current port.
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * Sets the address to that given.
     *
     * @param string $address The new address.
     *
     * @return NULL
     */
    public function setAddress($address) {
        $this->address = $address;
    }

    /**
     * Sets the port to that given.
     *
     * @param string $port The new port.
     *
     * @return NULL
     */
    public function setPort($port) {
        $this->port = $port;
    }

    // Instance methods.

    /**
     * Creates a new mock server from the given parameters.
     *
     * @param string $address The address to which the server should bind.
     * @param string $port    The port of which the server should bind.
     *
     * @return NULL
     */
    public function __construct($address = '127.0.0.1', $port = 10000) {
        // Set attributes from the given parameters.
        $this->address = $address;

        $this->port = $port;

        // The connection and listening sockets are closed by default.
        $this->connectionSocketOpen = FALSE;
        $this->listeningSocketOpen = FALSE;
    }

    /**
     * Handles cleanup for a mock server instance.
     *
     * @return NULL
     */
    public function __destruct() {
        // Make sure the server has stopped.
        $this->stop();
    }

    /**
     * Method to emit a string (terminated with '\r\n') on the current
     * connection. Returns TRUE on success; FALSE otherwise.
     *
     * @param string $string String to emit.
     *
     * @return bool          Whether the emit operation succeeded.
     */
    public function emit($string) {
        // Check that there is a connection open.
        if ($this->connectionSocketOpen) {
            // Make sure that the string is terminated correctly.
            if (!strstr($string, "\r\n")) {
                $string .= "\r\n";
            }
            // Write the string on the connection socket.
            try {
                socket_write($this->connectionSocket, $string);
                return TRUE;
            } catch (Exception $e) {
                // Just return FALSE on error.
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * Method to end any communication on the current connection,
     * and close the socket.
     *
     * @return NULL
     */
    public function endCommunication() {
        // Check if there's a current connection open.
        if ($this->connectionSocketOpen) {
            // Close the connection socket.
            socket_close($this->connectionSocket);

            // Set flags appropriately.
            $this->connectionSocketOpen = FALSE;
        }
    }

    /**
     * Function to read a line (terminated by \r\n or \n) from the current
     * connection. Returns the read string, stripped of the terminator.
     *
     * @return string String read from the connection.
     */
    public function read() {
        if ($this->connectionSocketOpen) {
            $buffer = '';
            while (!strstr($buffer, "\n")) {
                $buffer .= socket_read($this->connectionSocket, 1,
                                       PHP_BINARY_READ);
            }
            // Strip the linefeed/carriage return characters from the string.
            return rtrim($buffer, "\r\n\0");             
        } else {
            return '';
        }
    }

    /**
     * Method to start the server using the stored server details.
     *
     * @return NULL
     */
    public function start() {
        // Create the TCP socket upon which this server will listen.
        try {
            $this->listeningSocket = socket_create(AF_INET, SOCK_STREAM,
                                                   SOL_TCP);
        } catch (Exception $e) {
            $message = "Could not create the socket.";
            throw new ServerException($message);
        }

        // Fix a problem where test cases re-run quickly cause
        // 'address already in use' errors by allowing address reuse.
        try {
            socket_set_option($this->listeningSocket, SOL_SOCKET,
                              SO_REUSEADDR, 1);
        } catch (Exception $e) {
            $message = "Could not set the socket reuse option.";
            throw new ServerException($message);
        }

        // Bind the server's socket to the port on the given address.
        try {
            socket_bind($this->listeningSocket, $this->address, $this->port);
        } catch (Exception $e) {
            $message = "Could not bind to port '" . $this->port . "'" .
                       " on address '" . $this->address . "'";
            throw new ServerException($message);
        }

        // Set the socket to listen for only one connection at a time.
        // (Any more will be refused.)
        try {
            socket_listen($this->listeningSocket, 0);
        } catch (Exception $e) {
            $message = "Could not start socket listening for incoming " .
                       "connections.";
            throw new ServerException($message);
        }

        // The listening socket should be open now - set the
        // listeningSocketOpen flag.
        $this->listeningSocketOpen = TRUE;
    }

    /**
     * Method to accept incoming connections and start communication.
     *
     * @return NULL
     */
    public function startCommunication() {
        // Wait until we have an incoming connection.
        try {
            $this->connectionSocket = socket_accept($this->listeningSocket);
        } catch (Exception $e) {
            $message = "Could not accept incoming connection.";
            throw new ServerException($message);
        }

        // Fix a problem where test cases re-run quickly cause
        // 'address already in use' errors by allowing address reuse.
        try {
            socket_set_option($this->listeningSocket, SOL_SOCKET,
                              SO_REUSEADDR, 1);
        } catch (Exception $e) {
            $message = "Could not set the socket reuse option.";
            throw new ServerException($message);
        }

        // If we're here, the connection is open.
        $this->connectionSocketOpen = TRUE;
    }

    /**
     * Method to stop the server.
     *
     * @return NULL
     */
    public function stop() {
        // Make sure the server has stopped communicating.
        $this->endCommunication();

        // Check if the connection listening socket is still open.
        if ($this->listeningSocketOpen) {
            // Close the connection listening socket.
            socket_close($this->listeningSocket);

            // Set flags appropriately.
            $this->listeningSocketOpen = FALSE;
        }
    }
}

/**
 * Concrete class that defines a mock POP3 mail server.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright 2010 Geoff Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class POP3MockMailServer extends BaseMockServer {}

?>
