<?php
/**
 * Test suite for the encrypted file persistence mechanism.
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
 * Test suite for the encrypted file persistence mechanism.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class EncryptedFilePersistenceTests extends PHPUnit_Framework_TestCase {
    /**
     * Store path used during the tests.
     *
     * @var string
     */
    protected $storePath;

    /**
     * Create an empty store path before each test.
     *
     * @return NULL
     */
    protected function setUp() {
        $this->storePath = dirname(__FILE__) . '/tmpdata/';
        rmr($this->storePath);
        mkdir($this->storePath);
    }

    /**
     * Tear down the store after each test.
     *
     * @return NULL
     */
    protected function tearDown() {
        rmr($this->storePath);
    }

    /**
     * Test the constructor method.
     *
     * @return NULL
     */
    public function testConstructor() {
        $store = new EncryptedFilePersistence();
    }

    /**
     * Test the constructor method with an invalid store path.
     *
     * @return NULL
     */
    public function testConstructorInvalidStorePath() {
        $madeUpPath = APPLICATION_PATH . '/madeuppath/';
        $store = new EncryptedFilePersistence('', FALSE, $madeUpPath);
    }

    /**
     * Test where a null key is given to hasKey.
     *
     * @depends testConstructor
     *
     * @return NULL
     */
    public function testHasKeyNullKey() {
        $store = new EncryptedFilePersistence('', FALSE, $this->storePath);
        $result = $store->hasKey('');
        $this->assertEquals(FALSE, $result);
    }

    /**
     * Test a clear on an empty store.
     *
     * @depends testConstructor
     *
     * @return NULL
     */
    public function testClearEmptyStore() {
        $store = new EncryptedFilePersistence('', FALSE, $this->storePath);
        // The key shouldn't already exist.
        $this->assertEquals(FALSE, $store->hasKey('testkey'));
        $result = $store->clear('testkey');
        // The key should have been cleared successfully.
        $this->assertEquals(TRUE, $result);
        // The key should still not exist.
        $this->assertEquals(FALSE, $store->hasKey('testkey'));
    }

    /**
     * Test a fetch on an empty store.
     *
     * @depends testConstructor
     *
     * @return NULL
     */
    public function testFetchEmptyStore() {
        $store = new EncryptedFilePersistence('', FALSE, $this->storePath);
        $result = $store->fetch('testkey');
        $this->assertEquals(NULL, $result);
    }

    /**
     * Test a hasKey on an empty store.
     *
     * @depends testHasKeyNullKey
     *
     * @return NULL
     */
    public function testHasKeyEmptyStore() {
        $store = new EncryptedFilePersistence('', FALSE, $this->storePath);
        $result = $store->hasKey('testkey');
        $this->assertEquals(FALSE, $result);
    }

    /**
     * Test a single store on an empty store.
     *
     * @depends testConstructor
     *
     * @return NULL
     */
    public function testStoreSingle() {
        $store = new EncryptedFilePersistence('', FALSE, $this->storePath);
        $result = $store->store('testkey', 'testvalue');
        $this->assertEquals(TRUE, $result);
    }

    /**
     * Test a single store and hasKey on an empty store.
     *
     * @depends testStoreSingle
     * @depends testHasKeyNullKey
     *
     * @return NULL
     */
    public function testStoreSingleHasKey() {
        $store = new EncryptedFilePersistence('', FALSE, $this->storePath);
        $this->assertEquals(TRUE, $store->store('testkey', 'testvalue'));
        $result = $store->hasKey('testkey');
        $this->assertEquals(TRUE, $result);
    }

    /**
     * Test a clear after a single store on an empty store.
     *
     * @depends testFetchEmptyStore
     *
     * @return NULL
     */
    public function testStoreSingleClear() {
        $store = new EncryptedFilePersistence('', FALSE, $this->storePath);
        // Store a value first.
        $this->assertEquals(TRUE, $store->store('testkey', 'testvalue'));
        // The key should exist.
        $this->assertEquals(TRUE, $store->hasKey('testkey'));
        // Clear the key.
        $result = $store->clear('testkey');
        // The key should have been cleared successfully.
        $this->assertEquals(TRUE, $result);
        // The key should now not exist.
        $this->assertEquals(FALSE, $store->hasKey('testkey'));
    }

    /**
     * Test a single store and fetch on an empty store.
     *
     * @depends testStoreSingleHasKey
     *
     * @return NULL
     */
    public function testStoreSingleFetchSingle() {
        $store = new EncryptedFilePersistence('', FALSE, $this->storePath);
        $this->assertEquals(TRUE, $store->store('testkey', 'testvalue'));
        $result = $store->fetch('testkey');
        $this->assertEquals('testvalue', $result);
    }

    /**
     * Test a single store, fetch, and has key test on an empty store.
     *
     * @depends testStoreSingleFetchSingle
     *
     * @return NULL
     */
    public function testStoreSingleFetchSingleHasKey() {
        $store = new EncryptedFilePersistence('', FALSE, $this->storePath);
        $this->assertEquals(TRUE, $store->store('testkey', 'testvalue'));
        $result = $store->fetch('testkey');
        $this->assertEquals('testvalue', $result);
        $result = $store->hasKey('testkey');
        $this->assertEquals(TRUE, $result);
    }

    /**
     * Test multiple stores, fetches, and has key tests on an empty store.
     *
     * @depends testStoreSingleFetchSingleHasKey
     *
     * @return NULL
     */
    public function testMultipleStoreFetchHasKey() {
        $store = new EncryptedFilePersistence('', FALSE, $this->storePath);
        // Store, and fetch key 1.
        $this->assertEquals(TRUE, $store->store('testkey1', 'testvalue1'));
        $result = $store->fetch('testkey1');
        $this->assertEquals('testvalue1', $result);
        // Store, and fetch key 2.
        $this->assertEquals(TRUE, $store->store('testkey2', 'testvalue2'));
        $result = $store->fetch('testkey2');
        $this->assertEquals('testvalue2', $result);
        // Store, and fetch key 3.
        $this->assertEquals(TRUE, $store->store('testkey3', 'testvalue3'));
        $result = $store->fetch('testkey3');
        $this->assertEquals('testvalue3', $result);
        // Test all the keys.
        $result = $store->hasKey('testkey1');
        $this->assertEquals(TRUE, $result);
        $result = $store->hasKey('testkey2');
        $this->assertEquals(TRUE, $result);
        $result = $store->hasKey('testkey3');
        $this->assertEquals(TRUE, $result);
        // Test a non-existant key.
        $result = $store->hasKey('testkey4');
        $this->assertEquals(FALSE, $result);
    }

    /**
     * Test multiple stores, fetches, clears, and has key tests on an empty
     * store.
     *
     * @depends testMultipleStoreFetchHasKey
     *
     * @return NULL
     */
    public function testMultipleStoreClearFetchHasKey() {
        $store = new EncryptedFilePersistence('', FALSE, $this->storePath);
        // Store, and fetch key 1.
        $this->assertEquals(TRUE, $store->store('testkey1', 'testvalue1'));
        $result = $store->fetch('testkey1');
        $this->assertEquals('testvalue1', $result);
        // Store, fetch, and clear key 2.
        $this->assertEquals(TRUE, $store->store('testkey2', 'testvalue2'));
        $result = $store->fetch('testkey2');
        $this->assertEquals('testvalue2', $result);
        $this->assertEquals(TRUE, $store->clear('testkey2'));
        // Store and fetch key 2 again.
        $this->assertEquals(TRUE, $store->store('testkey2', 'testvalue2'));
        $result = $store->fetch('testkey2');
        $this->assertEquals('testvalue2', $result);
        // Store, fetch, and clear key 3.
        $this->assertEquals(TRUE, $store->store('testkey3', 'testvalue3'));
        $result = $store->fetch('testkey3');
        $this->assertEquals('testvalue3', $result);
        $this->assertEquals(TRUE, $store->clear('testkey3'));
        // Test all the keys.
        $result = $store->hasKey('testkey1');
        $this->assertEquals(TRUE, $result);
        $result = $store->hasKey('testkey2');
        $this->assertEquals(TRUE, $result);
        $result = $store->hasKey('testkey3');
        $this->assertEquals(FALSE, $result);
    }
}

?>
