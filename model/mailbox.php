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
 * User mailbox definitions.
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
 * Import the server connection classes.
 */
require(APPLICATION_PATH . '/model/serverconnection.php');

/**
 * Class to define and manipulate mailboxes.
 *
 * @package phpbiff
 */
class Mailbox
{
    // Instance attributes.

    /**
     * Mail box account name.
     *
     * @var string
     */
    protected $accountName;

    /**
     * Authentication method (currently only 'plain' is supported.)
     *
     * @var string
     */
    protected $authenticationMethod;

    /**
     * Connection to the server.
     *
     * @var ServerConnection
     */
    protected $connection;

    /**
     * Check frequency in seconds.
     *
     * @var int
     */
    protected $checkFrequency;

    /**
     * Display order of mailbox in mailbox view.
     *
     * @var int
     **/
    protected $displayOrder;

    /**
     * Hostname of the server the mailbox resides upon.
     *
     * @var string
     */
    protected $hostname;

    /**
     * Time of the last mailbox status check.
     *
     * @var int
     */
    protected $lastChecked;

    /**
     * Message count as at the last check.
     *
     * @var int
     */
    protected $messageCount;

    /**
     * Mail box access password. (Plaintext.)
     *
     * @var string
     */
    protected $password;

    /**
     * Mail box access protocol (currently only 'pop3' is supported.)
     *
     * @var string
     */
    protected $protocol;

    /**
     * Mail box access port.
     *
     * @var int
     */
    protected $port;

    /**
     * Read message count (for tracking old and new mail.)
     *
     * @var int
     */
    protected $readMessageCount;

    /**
     * Current mail box status ('error', 'no mail', 'old mail', 'new mail')
     *
     * @var string
     */
    protected $status;

    /**
     * Current connection timeout.
     *
     * @var int
     */
    protected $timeout;

    /**
     * Mail box access username.
     *
     * @var string
     */
    protected $username;

    // Instance attribute accessors.

    /**
     * Returns the current account name.
     *
     * @return string The current account name.
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * Returns the current authentication method.
     *
     * @return string The current authentication method.
     */
    public function getAuthenticationMethod()
    {
        return $this->authenticationMethod;
    }

    /**
     * Returns the current check frequency.
     *
     * @return int The current check frequency.
     */
    public function getCheckFrequency()
    {
        return $this->checkFrequency;
    }

    /**
     * Returns the current display order.
     *
     * @return int The current display order.
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * Returns the current server hostname.
     *
     * @return string The current server hostname.
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Returns the current last checked time.
     *
     * @return int The current last checked time.
     */
    public function getLastChecked()
    {
        return $this->lastChecked;
    }

    /**
     * Returns the current message count.
     *
     * @return int The current message count.
     */
    public function getMessageCount()
    {
        return $this->messageCount;
    }

    /**
     * Returns the current server port.
     *
     * @return int The current server port.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Returns the current server protocol.
     *
     * @return string The current server protocol.
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Returns the current read message count.
     *
     * @return int The current read message count.
     */
    public function getReadMessageCount()
    {
        return $this->readMessageCount;
    }

    /**
     * Returns the current mailbox status.
     *
     * @return string The current mailbox status.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns the current server connection timeout.
     *
     * @return int The current server connection timeout.
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Returns the current username.
     *
     * @return string The current username.
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets the account name to that given.
     *
     * @param string $accountName The new account name.
     */
    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;
    }

    /**
     * Sets the authentication method to that given.
     *
     * @param string $authenticationMethod The new authentication method.
     */
    public function setAuthenticationMethod($authenticationMethod)
    {
        $this->authenticationMethod = $authenticationMethod;
    }

    /**
     * Sets the check frequency to that given.
     *
     * @param int $checkFrequency The new check frequency.
     */
    public function setCheckFrequency($checkFrequency)
    {
        $this->checkFrequency = $checkFrequency;
    }

    /**
     * Sets the mailbox display order to that given.
     *
     * @param int $checkFrequency The new mailbox check frequency.
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;
    }

    /**
     * Sets the password to that given.
     *
     * @param string $checkFrequency The new password.
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Sets the server port to that given.
     *
     * @param int $port The new server port.
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Sets the server protocol to that given.
     *
     * @param string $protocol The new server protocol.
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * Sets the server connection timeout to that given.
     *
     * @param int $timeout The new server connection timeout.
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * Sets the username to that given.
     *
     * @param string $username The new username.
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    // Instance methods.

    /**
     * Creates a new mailbox from the given parameters.
     *
     * @param string $accountName The new account's name.
     * @param string $authenticationMethod The authentication method to use.
     * @param string $checkFrequency The mail check frequency.
     * @param string $displayOrder The display order of the mailbox in the UI.
     * @param string $hostname The server hostname.
     * @param string $password The password to use with the server.
     * @param string $port     The port upon which to connect to the server.
     * @param string $protocol The protocol to use to connect to the server.
     * @param string $timeout  The server connection timeout to use.
     * @param string $username The username to user with the server.
     */
    public function __construct($accountName = 'New account',
                                $authenticationMethod = 'plain',
                                $checkFrequency = 60, $displayOrder = 1,
                                $hostname = 'localhost', $password = '',
                                $port = 110, $protocol = 'pop3',
                                $timeout = 10, $username = '')
    {
        // Set attributes from the given parameters.
        $this->accountName = $accountName;
        $this->authenticationMethod = $authenticationMethod;
        $this->checkFrequency = $checkFrequency;     
        $this->displayOrder = $displayOrder;
        $this->hostname = $hostname;
        $this->password = $password;
        $this->port = $port;
        $this->protocol = $protocol;
        $this->timeout = $timeout;
        $this->username = $username;

        // Make sure the internal counters are set correctly.
        $this->connection = NULL;
        $this->lastChecked = NULL;
        $this->messageCount = 0;
        $this->readMessageCount = 0;
        $this->status = 'error';
    }

    /**
     * Function to check the mailbox, updating the mail box status and
     * message count.
     */
    public function check()
    {
        global $serverConnectionFactory;

        try
        {
            // Establish a server connection with the given settings for this
            // mailbox.
            $this->connection =
                $serverConnectionFactory->createConnection($this->protocol,
                                                           $this->hostname,
                                                           $this->port,
                                                           $this->timeout);
            // Log into the server.
            $this->connection->login($this->username, $this->password,
                                     $this->authenticationMethod);

            // Get and store the current message count.
            $this->messageCount = $this->connection->messageCount();

            // Work out the status of the mailbox from the old and new message counts.
            if ($this->readMessageCount < $this->messageCount)
            {
                $this->status = 'new mail';
            } else {
                $this->status = 'old mail';
            }

            if ($this->messageCount == 0) {
                $this->status = 'no mail';
            }

            // Set the last check time.
            $this->lastChecked = time();

        } catch (ConnectionException $error) {
            // Indicate that the mailbox status is in error, and that
            // no messages could be retrieved.
            $this->status = 'error';
            $this->messageCount = 0;
        }
    }
    
    /**
     * Function to mark the current mailbox's messages as read.
     */
    public function markAsRead()
    {
        $this->readMessageCount = $this->messageCount;

        if ($this->status == 'new mail')
        {
            $this->status = 'old mail';
        }
    }
}
?>
