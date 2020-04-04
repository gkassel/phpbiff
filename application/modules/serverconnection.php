<?php
/**
 * Server connections.
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

/** Import the application settings. */
require_once(dirname(__FILE__) . '/../Bootstrap.php');

/**
 * Exception to indicate problems with server connections.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class ConnectionException extends RuntimeException {}

/**
 * Interface class for server connections.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
interface ServerConnection {
    // Instance attribute accessors.

    /**
     * Returns the current hostname
     *
     * @return string The current hostname.
     */
    public function getHostname();

    /**
     * Returns the current port.
     *
     * @return int The current port.
     */
    public function getPort();

    /**
     * Returns the current timeout.
     *
     * @return int The current timeout.
     */
    public function getTimeout();

    /**
     * Sets the hostname to that given.
     *
     * @param string $hostname The new hostname.
     *
     * @return NULL
     */
    public function setHostname($hostname);

    /**
     * Sets the port to that given.
     *
     * @param string $port The new port.
     *
     * @return NULL
     */
    public function setPort($port);

    /**
     * Sets the timeout to that given.
     *
     * @param string $timeout The new timeout.
     *
     * @return NULL
     */
    public function setTimeout($timeout);

    // Instance methods.

    /**
     * Method to close a server connection.
     *
     * @return NULL
     */
    public function close();

    /**
     * Function to determine whether a connection is still alive.
     *
     * @return bool Whether a connection is still alive.
     */
    public function isAlive();

    /**
     * Method to log into the server using the given authentication details.
     *
     * @param string $username             Username to pass to the server.
     * @param string $password             Plaintext password to pass to the
     *                                     server.
     * @param string $authenticationMethod Authentication method to use.
     * @param string $passwordFormat       Format of the given password.
     *
     * @return NULL
     */
    public function login($username, $password, $authenticationMethod = '',
                          $passwordFormat = '');

    /**
     * Function to return the current message count.
     *
     * @return int Current count of messages stored on the server.
     */
    public function messageCount();

    /**
     * Method to open a connection to the server, using the stored
     * connection details.
     *
     * @return NULL
     */
    public function open();
}

/**
 * Concrete class that defines attributes and methods common to all server
 * connections.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class BaseServerConnection implements ServerConnection {
    // Instance attributes.

    /**
     * Whether the connection has been authenticated.
     *
     * @var bool
     */
    protected $authenticated;

    /**
     * Server host name.
     *
     * @var string
     */
    protected $hostname;

    /**
     * Server port.
     *
     * @var int
     */
    protected $port;

    /**
     * Protocol used in connections.
     *
     * @var string
     */
    const PROTOCOL = '';

    /**
     * Connection socket.
     *
     * @var socket
     */
    protected $socket;

    /**
     * Whether the socket is open or not.
     *
     * @var bool
     */
    protected $socketOpen;

    /**
     * Connection timeout.
     *
     * @var int
     */
    protected $timeout;

    // Instance attribute accessors.

    /**
     * Returns the current hostname
     *
     * @return string The current hostname.
     */
    public function getHostname() {
        return $this->hostname;
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
     * Returns the current timeout.
     *
     * @return int The current timeout.
     */
    public function getTimeout() {
        return $this->timeout;
    }

    /**
     * Sets the hostname to that given.
     *
     * @param string $hostname The new hostname.
     *
     * @return NULL
     */
    public function setHostname($hostname) {
        $this->hostname = $hostname;
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

    /**
     * Sets the timeout to that given.
     *
     * @param string $timeout The new timeout.
     *
     * @return NULL
     */
    public function setTimeout($timeout) {
        $this->timeout = $timeout;
        if ($this->socketOpen) {
            // Set the stream timeout.
            stream_set_timeout($this->socket, $this->timeout);
        }
    }

    // Instance methods.

    /**
     * Destroys the current connection.
     *
     * @return NULL
     */
    function __destruct() {
        // Make sure the connection is closed.
        if ($this->socketOpen) {
            $this->close();
        }
    }

    /**
     * Function to determine whether a connection is still alive.
     *
     * @return bool Whether a connection is still alive.
     *
     * @abstract
     */
    function isAlive() {
        throw new RuntimeException("Not implemented by this class.");
    }

    /**
     * Method to close a server connection.
     *
     * @return NULL
     */
    function close() {
        if ($this->socketOpen) {
            fclose($this->socket);

            // The connection should be closed now - set the socketOpen flag.
            $this->socketOpen = FALSE;
        }
    }

    /**
     * Method to log into the server using the given authentication details.
     * Implementation to be supplied by inheriting classes.
     *
     * @param string $username             Username to pass to the server.
     * @param string $password             Plaintext password to pass to the
     *                                     server.
     * @param string $authenticationMethod Authentication method to use.
     * @param string $passwordFormat       Format of the given password.
     *
     * @return NULL
     *
     * @abstract
     */
    public function login($username, $password, $authenticationMethod = '',
                          $passwordFormat = '') {
        throw new RuntimeException("Not implemented by this class.");
    }

    /**
     * Function to return the current message count.
     * Implementation to be supplied by inheriting classes.
     *
     * @return int Current count of messages stored on the server.
     *
     * @abstract
     */
    public function messageCount() {
        throw new RuntimeException("Not implemented by this class.");
    }

    /**
     * Method to open a connection to the server, using the stored
     * connection details.
     *
     * @return NULL
     */
    public function open() {
        try {
            $this->socket = fsockopen($this->hostname, $this->port,
                                      &$errorno, &$errorstr, $this->timeout);
        } catch (Exception $e) {
            $message = self::PROTOCOL . " connection error: $errorno $errorstr";
            throw new ConnectionException($message);
        }

        // Set the stream timeout.
        stream_set_timeout($this->socket, $this->timeout);

        // The connection should be open now - set the socketOpen flag.
        $this->socketOpen = TRUE;
    }
}

/**
 * Concrete class to handle POP3 server connections.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class POP3ServerConnection extends BaseServerConnection {

    // Instance attributes.

    /**
     * The recorded server description string.
     *
     * @var string
     */
    protected $serverString;

    /**
     * The implemented protocol.
     *
     * @var string
     */
    const PROTOCOL = 'pop3';

    // Instance methods.

    /**
     * Creates a new POP3 server connection from the given parameters.
     *
     * @param string $hostname The hostname of the server.
     * @param string $port     The port of the server.
     * @param string $timeout  The connection timeout to use.
     *
     * @return NULL
     */
    public function __construct($hostname = 'localhost', $port = 110,
                                $timeout = 10) {
        // Set attributes from the given parameters.
        $this->hostname = $hostname;

        $this->port = $port;

        $this->timeout = $timeout;

        // The server is not authenticated by default.
        $this->authenticated = FALSE;

        // The connection is closed by default.
        $this->socketOpen = FALSE;
    }

    /**
     * Function to determine whether a connection is still alive.
     *
     * @return bool Whether a connection is still alive.
     */
    public function isAlive() {

        $result = FALSE;

        try {
            $noopresult = $this->command("NOOP");
            $result = TRUE;
        } catch (Exception $e) {
            $result = FALSE;
        }

        return $result;
    }

    /**
     * Function to issue the given command to the server.
     * Returns the server response.
     *
     * @param string $command The command to issue to the server.
     *
     * @return string The response returned by the server.
     */
    public function command($command) {
        // Check that the socket is open.
        if (!$this->socketOpen) {
            $this->open();
        }

        try {
            $reply = '';
            fwrite($this->socket, "$command\r\n");
            $reply = fgets($this->socket);
        } catch (Exception $e) {
            $message = self::PROTOCOL . " command: Connection to server lost.";
            throw new ConnectionException($message);
        }

        return $reply;
    }

    /**
     * Function to login to the server with the given details,
     * using the given authentication method.
     * Throws errors on different failure modes; returns True on success.
     *
     * @param string $username             Username to pass to the server.
     * @param string $password             Plaintext password to pass to the
     *                                     server.
     * @param string $authenticationMethod Authentication method to use.
     * @param string $passwordFormat       Format of the given password.
     *
     * @return bool Whether the login was successful.
     */
    public function login($username, $password,
                          $authenticationMethod = 'plain',
                          $passwordFormat = 'plain') {
        switch($authenticationMethod) {
            case 'APOP':
                return $this->loginAPOP($username, $password,
                                        $passwordFormat);
                break;
            case 'CRAM-MD5':
                return $this->loginCRAMMD5($username, $password,
                                           $passwordFormat);
                break;
            case 'DIGEST-MD5':
                return $this->loginDigestMD5($username, $password,
                                             $passwordFormat);
                break;
            case 'NTLM':
                return $this->loginNTLM($username, $password,
                                        $passwordFormat);
                break;
            default:
                // Use the default of USER/PASS authentication.
                break;
        }
        // If we're here, we're using USER/PASS authentication.

        // Check that the password format is appropriate for this method.
        if ($passwordFormat != 'plain') {
            $message = "Invalid password format '$passwordFormat' used with" .
                       " plain/login authentication method.";
            throw new LogicException($message);
        }

        // Send the username.
        $response = $this->command("USER $username");
        if (!$this->successfulResponse($response)) {
            $message = "Server reports: unknown username '$username'";
            throw new ConnectionException($message);
        }

        // Send the password.
        $response = $this->command("PASS $password");
        if (!$this->successfulResponse($response)) {
            $message = "Server reports: invalid password.";
            throw new ConnectionException($message);
        }

        $this->authenticated = TRUE;

        return TRUE;
    }

    /**
     * loginAPOP. FIXME: Implementation incomplete.
     *
     * @param string $username       Username to pass to the server.
     * @param string $password       Plaintext password to pass to the server.
     * @param string $passwordFormat Format of the given password.
     *
     * @return bool Whether the login was successful.
     */
    protected function loginAPOP($username, $password,
                                 $passwordFormat = 'plain') {
        $message = self::PROTOCOL . " error - APOP authentication not " .
                   "currently supported.";
        throw new RuntimeException($message);
    }

    /**
     * loginCRAMMD5. FIXME: Implementation incomplete.
     *
     * @param string $username       Username to pass to the server.
     * @param string $password       Plaintext password to pass to the server.
     * @param string $passwordFormat Format of the given password.
     *
     * @return bool Whether the login was successful.
     */
    protected function loginCRAMMD5($username, $password,
                                    $passwordFormat = 'plain') {
        $message = self::PROTOCOL . " error - CRAM-MD5 authentication not " .
                   "currently supported.";
        throw new RuntimeException($message);
    }

    /**
     * loginDigestMD5. FIXME: Implementation incomplete.
     *
     * @param string $username       Username to pass to the server.
     * @param string $password       Plaintext password to pass to the server.
     * @param string $passwordFormat Format of the given password.
     *
     * @return bool Whether the login was successful.
     */
    protected function loginDigestMD5($username, $password,
                                      $passwordFormat = 'plain') {
        $message = self::PROTOCOL . " error - DIGEST-MD5 authentication " .
                   "not currently supported.";
        throw new RuntimeException($message);
    }

    /**
     * loginNTLM. FIXME: Implementation incomplete.
     *
     * @param string $username       Username to pass to the server.
     * @param string $password       Plaintext password to pass to the server.
     * @param string $passwordFormat Format of the given password.
     *
     * @return bool Whether the login was successful.
     */
    protected function loginNTLM($username, $password,
                                 $passwordFormat = 'plain') {
        $message = self::PROTOCOL . " error - NTLM authentication not " .
                   "currently supported.";
        throw new RuntimeException($message);
    }

    /**
     * Function to return the current message count on the server.
     * Throws an exception on error; returns the count otherwise.
     *
     * @return int Current count of messages stored on the server.
     */
    public function messageCount() {
        // Make sure we're authenticated first.
        if (!$this->authenticated) {
            $message = self::PROTOCOL . " programming error - messageCount " .
                       "called before login.";
            throw new LogicException($message);
        }

        $response = $this->command("STAT");

        if (!$this->successfulResponse($response)) {
            $message = self::PROTOCOL . " error - bad response '$response' to " .
                       "STAT command";
            throw new ConnectionException($message);
        }

        // Break down the response into its components.
        $responseParts = explode(" ", $response);

        // The count is the first part.
        $count = $responseParts[1];
        settype($count, "integer");

        return $count;
    }

    /**
     * Method to open a connection to the POP3 server, using the stored
     * connection details.
     *
     * @return NULL
     */
    public function open() {
        // Use the superclass open method to open the raw connection.
        parent::open();

        try {
            // Retrieve the first response from the server, which contains the
            // server description string.
            // (This is needed for APOP authentication.)
            $this->serverString = fgets($this->socket);
        } catch (Exception $e) {
            $message = self::PROTOCOL . " command: Connection to server lost.";
            throw new ConnectionException($message);
        }
    }

    /**
     * Function to determine whether the given response indicates
     * success or failure. Returns TRUE on success; FALSE otherwise.
     *
     * @param string $response Server response string, as returned from
     *                         command().
     *
     * @return bool     Whether the server response indicates success.
     */
    protected function successfulResponse($response) {
        if (ereg("^\+", $response)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

/**
 * Factory class used to generate server connections, dispatching by protocol.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class ServerConnectionFactory {

    /**
     * Function to create a new server connection, dispatching to
     * the construction methods of the various server connection types on the
     * basis of the given protocol.
     * Returns a server connection.
     *
     * @param string $protocol Protocol type of connection to be created.
     * @param string $hostname Hostname of the server.
     * @param int    $port     Port of the server.
     * @param int    $timeout  Connection timeout.
     *
     * @return ServerConnection Created server connection object.
     */
    public function createConnection($protocol = 'pop3',
                                     $hostname = 'localhost',
                                     $port = 110, $timeout = 10) {
        if ($protocol == 'pop3') {
            return new POP3ServerConnection($hostname, $port, $timeout);
        }
    }
}

/**
 * Global server connection factory object.
 *
 * @global Global server connection factory object.
 * @name $serverConnectionFactory
 */
$serverConnectionFactory = new ServerConnectionFactory();

?>
