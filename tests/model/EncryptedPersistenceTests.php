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
 * Test case suite for the encrypted persistence mechanism.
 *
 * @author Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright Copyright (c) Geoff Kassel, 2010. All rights reserved.
 * @license http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
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
     * Test a single encrypt-decrypt cycle.
     *
     * @depends testEncryptData
     */
    public function testSingleEncryptDecryptCycle()
    {
        $store = new EncryptedPersistence('', false);        
        $encryptedData = $store->encryptData('abcd');
        $decryptedData = $store->decryptData($encryptedData);
        $this->assertEquals('abcd', $decryptedData);
    }        

    /**
     * Test a double encrypt-decrypt cycle.
     *
     * @depends testSingleEncryptDecryptCycle
     */
    public function testDoubleEncryptDecryptCycle()
    {
        $store = new EncryptedPersistence('', false);        
        $encryptedData = $store->encryptData('abcd');
        $decryptedData = $store->decryptData($encryptedData);
        $encryptedData = $store->encryptData($decryptedData);
        $decryptedData = $store->decryptData($encryptedData);
        $this->assertEquals('abcd', $decryptedData);
    }

    /**
     * Test the unimplemented clear function. (For coverage completeness.)
     *
     * @depends testConstructor
     * @expectedException RuntimeException
     */
    public function testClear()
    {
        $store = new EncryptedPersistence('', false);
        $store->clear('testkey');
    }

    /**
     * Test the unimplemented fetch function. (For coverage completeness.)
     *
     * @depends testConstructor
     * @expectedException RuntimeException
     */
    public function testFetch()
    {
        $store = new EncryptedPersistence('', false);
        $store->fetch('testkey');
    }

    /**
     * Test the unimplemented hasKey function. (For coverage completeness.)
     *
     * @depends testConstructor
     * @expectedException RuntimeException
     */
    public function testHasKey()
    {
        $store = new EncryptedPersistence('', false);
        $store->hasKey('testkey');
    }
    /**
     * Test the unimplemented store function. (For coverage completeness.)
     *
     * @depends testConstructor
     * @expectedException RuntimeException
     */
    public function testStore()
    {
        $store = new EncryptedPersistence('', false);
        $store->store('testkey', 'testvalue');
    }
}
?>
