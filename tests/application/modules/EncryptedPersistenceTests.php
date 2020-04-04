<?php
/**
 * Test suite for the encrypted persistence mechanism.
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
 * Email: sg_dot_kassel_dot_au_at_gmail_dot_com
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
require_once(APPLICATION_PATH . '/modules/persistence.php');
require_once(APPLICATION_PATH . '/modules/rmr.php');

/**
 * Test suite for the encrypted persistence mechanism.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class EncryptedPersistenceTests extends PHPUnit_Framework_TestCase {
    /**
     * Test the constructor method.
     *
     * @return NULL
     */
    public function testConstructor() {
        $store = new EncryptedPersistence();
    }

    /**
     * Test the constructor method with a pre-hashed key.
     *
     * @return NULL
     */
    public function testConstructorPrehashedKey() {
        $hashedKey = hash('sha256', '', $raw_output = FALSE);
        $store = new EncryptedPersistence($hashedKey, TRUE);
    }

    /**
     * Test where a null string is given to encryptData.
     *
     * @depends testConstructor
     * @expectedException Exception
     *
     * @return NULL
     */
    public function testEncryptDataNull() {
        $store = new EncryptedPersistence('', FALSE);
        $result = $store->encryptData('');
    }

    /**
     * Test where a null string is given to decryptData.
     *
     * @depends testConstructor
     * @expectedException Exception
     *
     * @return NULL
     */
    public function testDecryptDataNull() {
        $store = new EncryptedPersistence('', FALSE);
        $result = $store->decryptData('');
    }

    /**
     * Test where a non-empty string is given to encryptData.
     *
     * @depends testEncryptDataNull()
     *
     * @return NULL
     */
    public function testEncryptData() {
        $store = new EncryptedPersistence('', FALSE);
        $result = $store->encryptData('abcd');
    }

    /**
     * Test a single encrypt-decrypt cycle.
     *
     * @depends testEncryptData
     *
     * @return NULL
     */
    public function testSingleEncryptDecryptCycle() {
        $store = new EncryptedPersistence('', FALSE);
        $encryptedData = $store->encryptData('abcd');
        $decryptedData = $store->decryptData($encryptedData);
        $this->assertEquals('abcd', $decryptedData);
    }

    /**
     * Test a double encrypt-decrypt cycle.
     *
     * @depends testSingleEncryptDecryptCycle
     *
     * @return NULL
     */
    public function testDoubleEncryptDecryptCycle() {
        $store = new EncryptedPersistence('', FALSE);
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
     *
     * @return NULL
     */
    public function testClear() {
        $store = new EncryptedPersistence('', FALSE);
        $store->clear('testkey');
    }

    /**
     * Test the unimplemented fetch function. (For coverage completeness.)
     *
     * @depends testConstructor
     * @expectedException RuntimeException
     *
     * @return NULL
     */
    public function testFetch() {
        $store = new EncryptedPersistence('', FALSE);
        $store->fetch('testkey');
    }

    /**
     * Test the unimplemented hasKey function. (For coverage completeness.)
     *
     * @depends testConstructor
     * @expectedException RuntimeException
     *
     * @return NULL
     */
    public function testHasKey() {
        $store = new EncryptedPersistence('', FALSE);
        $store->hasKey('testkey');
    }

    /**
     * Test the unimplemented store function. (For coverage completeness.)
     *
     * @depends testConstructor
     * @expectedException RuntimeException
     *
     * @return NULL
     */
    public function testStore() {
        $store = new EncryptedPersistence('', FALSE);
        $store->store('testkey', 'testvalue');
    }
}
?>
