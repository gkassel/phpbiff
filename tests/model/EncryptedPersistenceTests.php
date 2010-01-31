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
 * Test case suite for the encrypted persistence mechanism.
 *
 * @author Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright Copyright (c) Geoff Kassel, 2010. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GPL-2
 * @package phpbiff
 */

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../testsettings.php');
require_once(APPLICATION_PATH . '/modules/rmr.php');
require_once(APPLICATION_PATH . '/model/persistence.php');

class EncryptedPersistenceTests extends PHPUnit_Framework_TestCase
{
    /**
     * Test the constructor method.
     */
    public function testConstructor()
    {
        $store = new EncryptedPersistence();
    }

    /**
     * Test where a null string is given to encryptData.
     *
     * @depends testConstructor
     * @expectedException Exception
     */
    public function testEncryptDataNull()
    {
        $store = new EncryptedPersistence('', false);
        $result = $store->encryptData('');
    }

    /**
     * Test where a null string is given to decryptData.
     *
     * @depends testConstructor
     * @expectedException Exception
     */
    public function testDecryptDataNull()
    {
        $store = new EncryptedPersistence('', false);
        $result = $store->decryptData('');
    }

    /**
     * Test where a non-empty string is given to encryptData.
     *
     * @depends testEncryptDataNull()
     */
    public function testEncryptData()
    {
        $store = new EncryptedPersistence('', false);
        $result = $store->encryptData('abcd');
    }

    /**
     * Test where a single encrypt-decrypt cycle.
     *
     * @depends testEncryptData
     */
    public function testEncryptDecryptCycle()
    {
        $store = new EncryptedPersistence('', false);
        $encryptedData = $store->encryptData('abcd');
        $decryptedData = $store->decryptData($encryptedData);
        $this->assertEquals('abcd', $decryptedData);
    }        
}

?>
