<?php
/**
 * Test suite for the server connection factory class.
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
require_once(APPLICATION_PATH . '/modules/serverconnection.php');

/**
 * Test suite for the server connection factory class.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class ServerConnectionFactoryTests extends PHPUnit_Framework_TestCase {
    /**
     * Test the constructor method.
     *
     * @return NULL
     */
    public function testConstructor() {
        $connection = new ServerConnectionFactory();
    }

    /**
     * Test createConnection method to create a POP3 server connection.
     *
     * @depends testConstructor
     *
     * @return NULL
     */
    public function testCreateConnectionPOP3() {
        $serverFactory = new ServerConnectionFactory();

        // Try to create a new server connection.
        $connection = $serverFactory->createConnection('pop3', '127.0.0.1',
                                                       110, 10);

        // Check that there was a server connection created.
        $this->assertEquals(TRUE, isset($connection));
    }

    /**
     * Test createConnection method to attempt to create an IMAP server
     * connection. (Presently unimplemented.)
     *
     * @depends testConstructor
     *
     * @return NULL
     */
    public function testCreateConnectionIMAP() {
        $serverFactory = new ServerConnectionFactory();

        // Try to create a new server connection.
        $connection = $serverFactory->createConnection('imap', '127.0.0.1',
                                                       110, 10);

        // Check that there wasn't a server connection created.
        $this->assertEquals(FALSE, isset($connection));
    }
}
?>
