<?php
/*
 * Copyright (c) Geoff Kassel, 2010. All rights reserved.
 *
 * This file may be distributed and/or modified under the terms of the
 * "GNU General Public License" version 2 as published by the Free
 * Software Foundation and appearing in the file LICENSE.GPL included in
 * the packaging of this file.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/copyleft/gpl.html.
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
 * Server connections.
 *
 * @author Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright Copyright (c) Geoff Kassel, 2010. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GPL-2
 * @package phpbiff
 */

/**
 * Import the application settings.
 */
require_once(dirname(__FILE__) . '/../settings.php');

/**
 * Exception to indicate problems with server connections.
 *
 * @package phpbiff
 */
class ConnectionException extends RuntimeException {}

/**
 * Interface class for server connections.
 *
 * @package phpbiff
 */
interface ServerConnection
{
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
     */
    public function setHostname($hostname);

    /**
     * Sets the port to that given.
     *
     * @param string $port The new port.
     */
    public function setPort($port);

    /**
     * Sets the timeout to that given.
     *
     * @param string $timeout The new timeout.
     */
    public function setTimeout($timeout);

    // Instance methods.

    /**
     * Method to close a server connection.
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
     * @param string $username Username to pass to the server.
     * @param string $password Plaintext password to pass to the server.
     * @param string $authenticationMethod Authentication method to use.
     * @param string $passwordFormat Format of the given password.
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
     */
    public function open();
}

/**
 * Concrete class that defines attributes and methods common to all server
 * connections.
 *
 * @package phpbiff
 */
class BaseServerConnection implements ServerConnection
{
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
    const protocol = '';

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
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Returns the current port.
     *
     * @return int The current port.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Returns the current timeout.
     *
     * @return int The current timeout.
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Sets the hostname to that given.
     *
     * @param string $hostname The new hostname.
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * Sets the port to that given.
     *
     * @param string $port The new port.
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Sets the timeout to that given.
     *
     * @param string $timeout The new timeout.
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        if ($this->socketOpen)
        {
            // Set the stream timeout.
            stream_set_timeout($this->socket, $this->timeout);
        }        
    }

    // Instance methods.
    
    function __destruct()
    {
        // Make sure the connection is closed.
        if ($this->socketOpen)
        {
            $this->close();
        }
    }

    /**
     * Function to determine whether a connection is still alive.
     *
     * @abstract
     * @return bool Whether a connection is still alive.
     */
    function isAlive()
    {
        throw new RuntimeException("Not implemented by this class.");
    }

    /**
     * Method to close a server connection.
     */
    function close()
    {
        if ($this->socketOpen)
        {
            fclose($this->socket);

            // The connection should be closed now - set the socketOpen flag.
            $this->socketOpen = false;
        }
    }

    /**
     * Method to log into the server using the given authentication details.
     * Implementation to be supplied by inheriting classes.
     *
     * @abstract
     * @param string $username Username to pass to the server.
     * @param string $password Plaintext password to pass to the server.
     * @param string $authenticationMethod Authentication method to use.
     * @param string $passwordFormat Format of the given password.
     */
    function login($username, $password, $authenticationMethod = '',
                   $passwordFormat = '')
    {
        throw new RuntimeException("Not implemented by this class.");
    }

    /**
     * Function to return the current message count.
     * Implementation to be supplied by inheriting classes.
     *
     * @abstract
     * @return int Current count of messages stored on the server.
     */
    function messageCount()
    {
        throw new RuntimeException("Not implemented by this class.");
    }

    /**
     * Method to open a connection to the server, using the stored
     * connection details.
     */
    function open()
    {
        if (!$this->socket = fsockopen($this->hostname, $this->port, 
                                       &$errorno, &$errorstr, $this->timeout))
        {
            $message = self::protocol . " connection error: $errorno $errorstr";
            throw new ConnectionException($message);
        }

        // Set the stream timeout.
        stream_set_timeout($this->socket, $this->timeout);

        // The connection should be open now - set the socketOpen flag.
        $this->socketOpen = true;
    }
}

/**
 * Concrete class to handle POP3 server connections.
 *
 * @package phpbiff
 */
class POP3ServerConnection extends BaseServerConnection
{

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
    const protocol = 'pop3';

    // Instance methods.
    
    /**
     * Creates a new POP3 server connection from the given parameters.
     *
     * @param string $hostname The hostname of the server.
     * @param string $port     The port of the server.
     * @param string $timeout  The connection timeout to use.
     */
    function __construct($hostname = 'localhost', $port = 110, $timeout = 10)
    {
        // Set attributes from the given parameters.
        $this->hostname = $hostname;

        $this->port = $port;

        $this->timeout = $timeout;

        // The server is not authenticated by default.
        $this->authenticated = false;

        // The connection is closed by default.
        $this->socketOpen = false;
    }

    /**
     * Function to determine whether a connection is still alive.
     *
     * @return bool Whether a connection is still alive.
     */
    function isAlive()
    {

        $result = false;

        try
        {
            $noopresult = $this->command("NOOP");
            $result = true;
        } catch (ConnectionException $error) {
            $result = false;
        }

        return $result;
    }

    /**
     * Function to issue the given command to the server.
     * Returns the server response.
     *
     * @return string The response returned by the server.
     */
    function command($command)
    {
        // Check that the socket is open.
        if (!$this->socketOpen)
        {
            $this->open();
        }
        
        if(!isset($this->socket))
        {
            $message = self::protocol . " command: Connection to server lost.";
            throw new ConnectionException($message);
        }

        fwrite($this->socket, "$command\r\n");        
        return fgets($this->socket);
    }

    /**
     * Function to login to the server with the given details,
     * using the given authentication method.
     * Throws errors on different failure modes; returns True on success.
     *
     * @param string $username Username to pass to the server.
     * @param string $password Plaintext password to pass to the server.
     * @param string $authenticationMethod Authentication method to use.
     * @param string $passwordFormat       Format of the given password.
     */
    function login($username, $password, $authenticationMethod = 'plain',
                   $passwordFormat = 'plain')
    {
        switch($authenticationMethod)
        {
            case 'APOP':
                return $this->loginAPOP($username, $password);
                break;
            case 'CRAM-MD5':
                return $this->loginCRAMMD5($username, $password);
                break;
            case 'DIGEST-MD5':
                return $this->loginDigestMD5($username, $password);
                break;
            case 'NTLM':
                return $this->loginNTLM($username, $password);
                break;
            default:
                // Use the default of USER/PASS authentication.
                break;
        }
        // If we're here, we're using USER/PASS authentication.

        // Check that the password format is appropriate for this method.
        if ($passwordFormat != 'plain')
        {
            $message = "Invalid password format '$passwordFormat' used with" .
                       " plain/login authentication method.";
            throw new LogicException($message);
        }

        // Send the username.
        $response = $this->command("USER $username");
        if (!$this->successfulResponse($response))
        {
            $message = "Server reports: unknown username '$username'";
            throw new ConnectionException($message);
        }

        // Send the password.
        $response = $this->command("PASS $password");
        if (!$this->successfulResponse($response))
        {
            $message = "Server reports: invalid password.";
            throw new ConnectionException($message);
        }

        $this->authenticated = true;

        return true;
    }
        
    /**
     * loginAPOP. FIXME: Implementation incomplete.
     */
    function loginAPOP($username, $password)
    {
        $message = self::protocol . " error - APOP authentication not " .
                   "currently supported.";
        throw new RuntimeError($message);
    }

    /**
     * loginCRAMMD5. FIXME: Implementation incomplete.
     */
    function loginCRAMMD5($username, $password)
    {
        $message = self::protocol . " error - CRAM-MD5 authentication not " .
                   "currently supported.";
        throw new RuntimeError($message);
    }

    /**
     * loginDigestMD5. FIXME: Implementation incomplete.
     */
    function loginDigestMD5($username, $password)
    {
        $message = self::protocol . " error - DIGEST-MD5 authentication " .
                   "not currently supported.";
        throw new RuntimeError($message);
    }

    /**
     * loginNTLM. FIXME: Implementation incomplete.
     */
    function loginNTLM($username, $password)
    {
        $message = self::protocol . " error - NTLM authentication not " .
                   "currently supported.";
        throw new RuntimeError($message);
    }

    /**
     * Function to return the current message count on the server.
     * Throws an exception on error; returns the count otherwise.
     *
     * @return int Current count of messages stored on the server.
     */
    function messageCount()
    {
        // Make sure we're authenticated first.
        if (!$this->authenticated)
        {
            $message = self::protocol . " programming error - messageCount " .
                       "called before login.";
            throw new LogicException($message);
        }

        $response = $this->command("STAT");

        if (!$this->successfulResponse($response))
        {
            $message = self::protocol . " error - bad response '$response' to " .
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
     */
    function open()
    {
        // Use the superclass open method to open the raw connection.
        parent::open();

        // Check that the socket is open.
        if (!$this->socketOpen)
        {
            $this->open();
        }
        
        if(!isset($this->socket))
        {
            $message = self::protocol . " command: Connection to server lost.";
            throw new ConnectionException($message);
        }

        // Retrieve the first response from the server, which contains the
        // server description string. (This is needed for APOP authentication.)
        $this->serverString = fgets($this->socket);
    }

    /**
     * Function to determine whether the given response indicates
     * success or failure. Returns true on success; false otherwise.
     *
     * @param $response Server response string, as returned from command().
     * @return bool     Whether the server response indicates success.
     */
    function successfulResponse($response)
    {
        if (ereg("^\+", $response))
        {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * Factory class used to generate server connections, dispatching by protocol.
 *
 * @package phpbiff
 */
class ServerConnectionFactory
{

    /**
     * Function to create a new server connection, dispatching to
     * the construction methods of the various server connection types on the
     * basis of the given protocol.
     * Returns a server connection.
     *
     * @param  string $protocol Protocol type of connection to be created.
     * @param  string $hostname Hostname of the server.
     * @param  int    $port     Port of the server.
     * @param  int    $timeout  Connection timeout.
     * @return ServerConnection Created server connection object.
     */
    public function createConnection($protocol = 'pop3',
                                     $hostname = 'localhost', 
                                     $port = 110, $timeout = 10)
    {
        if ($protocol == 'pop3')
        {
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
