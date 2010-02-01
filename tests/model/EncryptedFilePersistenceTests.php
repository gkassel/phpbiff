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
 * Test case suite for the encrypted file persistence mechanism.
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

class EncryptedFilePersistenceTests extends PHPUnit_Framework_TestCase
{
    /**
     * Store path used during the tests.
     *
     * @var string
     */
    protected $storePath;

    /**
     * Create an empty store path before each test.
     */
    protected function setUp()
    {
        $this->storePath = dirname(__FILE__) . '/tmpdata/';
        rmr($this->storePath);
        mkdir($this->storePath);
    }

    /**
     * Tear down the store after each test.
     */
    protected function tearDown()
    {
        rmr($this->storePath);
    }
 
    /**
     * Test the constructor method.
     */
    public function testConstructor()
    {
        $store = new EncryptedFilePersistence();
    }

    /**
     * Test where a null key is given to hasKey.
     *
     * @depends testConstructor
     */
    public function testHasKeyNullKey()
    {
        $store = new EncryptedFilePersistence('', false, $this->storePath);
        $result = $store->hasKey('');
        $this->assertEquals(false, $result);
    }

    /**
     * Test a clear on an empty store.
     *
     * @depends testConstructor
     */
    public function testClearEmptyStore()
    {
        $store = new EncryptedFilePersistence('', false, $this->storePath);
        // The key shouldn't already exist.
        $this->assertEquals(false, $store->hasKey('testkey'));
        $result = $store->clear('testkey');
        // The key should have been cleared successfully.
        $this->assertEquals(true, $result);
        // The key should still not exist.
        $this->assertEquals(false, $store->hasKey('testkey'));
    }

    /**
     * Test a fetch on an empty store.
     *
     * @depends testConstructor
     */
    public function testFetchEmptyStore()
    {
        $store = new EncryptedFilePersistence('', false, $this->storePath);
        $result = $store->fetch('testkey');
        $this->assertEquals(null, $result);
    }

    /**
     * Test a hasKey on an empty store.
     *
     * @depends testHasKeyNullKey
     */
    public function testHasKeyEmptyStore()
    {
        $store = new EncryptedFilePersistence('', false, $this->storePath);
        $result = $store->hasKey('testkey');
        $this->assertEquals(false, $result);
    }

    /**
     * Test a single store on an empty store.
     *
     * @depends testConstructor
     */
    public function testStoreSingle()
    {
        $store = new EncryptedFilePersistence('', false, $this->storePath);
        $result = $store->store('testkey', 'testvalue');
        $this->assertEquals(true, $result);
    }

    /**
     * Test a single store and hasKey on an empty store.
     *
     * @depends testStoreSingle
     * @depends testHasKeyNullKey
     */
    public function testStoreSingleHasKey()
    {
        $store = new EncryptedFilePersistence('', false, $this->storePath);
        $this->assertEquals(true, $store->store('testkey', 'testvalue'));
        $result = $store->hasKey('testkey');
        $this->assertEquals(true, $result);
    }

    /**
     * Test a clear after a single store on an empty store.
     *
     * @depends testFetchEmptyStore
     */
    public function testStoreSingleClear()
    {
        $store = new EncryptedFilePersistence('', false, $this->storePath);
        // Store a value first.
        $this->assertEquals(true, $store->store('testkey', 'testvalue'));
        // The key should exist.
        $this->assertEquals(true, $store->hasKey('testkey'));
        // Clear the key.
        $result = $store->clear('testkey');
        // The key should have been cleared successfully.
        $this->assertEquals(true, $result);
        // The key should now not exist.
        $this->assertEquals(false, $store->hasKey('testkey'));
    }

    /**
     * Test a single store and fetch on an empty store.
     *
     * @depends testStoreSingleHasKey
     */
    public function testStoreSingleFetchSingle()
    {
        $store = new EncryptedFilePersistence('', false, $this->storePath);
        $this->assertEquals(true, $store->store('testkey', 'testvalue'));
        $result = $store->fetch('testkey');
        $this->assertEquals('testvalue', $result);
    }

    /**
     * Test a single store, fetch, and has key test on an empty store.
     *
     * @depends testStoreSingleFetchSingle
     */
    public function testStoreSingleFetchSingleHasKey()
    {
        $store = new EncryptedFilePersistence('', false, $this->storePath);
        $this->assertEquals(true, $store->store('testkey', 'testvalue'));
        $result = $store->fetch('testkey');
        $this->assertEquals('testvalue', $result);
        $result = $store->hasKey('testkey');
        $this->assertEquals(true, $result);
    }

    /**
     * Test multiple stores, fetches, and has key tests on an empty store.
     *
     * @depends testStoreSingleFetchSingleHasKey
     */
    public function testMultipleStoreFetchHasKey()
    {
        $store = new EncryptedFilePersistence('', false, $this->storePath);
        // Store, and fetch key 1.
        $this->assertEquals(true, $store->store('testkey1', 'testvalue1'));
        $result = $store->fetch('testkey1');
        $this->assertEquals('testvalue1', $result);
        // Store, and fetch key 2.
        $this->assertEquals(true, $store->store('testkey2', 'testvalue2'));
        $result = $store->fetch('testkey2');
        $this->assertEquals('testvalue2', $result);
        // Store, and fetch key 3.
        $this->assertEquals(true, $store->store('testkey3', 'testvalue3'));
        $result = $store->fetch('testkey3');
        $this->assertEquals('testvalue3', $result);
        // Test all the keys.
        $result = $store->hasKey('testkey1');
        $this->assertEquals(true, $result);
        $result = $store->hasKey('testkey2');
        $this->assertEquals(true, $result);
        $result = $store->hasKey('testkey3');
        $this->assertEquals(true, $result);
        // Test a non-existant key.
        $result = $store->hasKey('testkey4');
        $this->assertEquals(false, $result);
    }

    /**
     * Test multiple stores, fetches, clears, and has key tests on an empty
     * store.
     *
     * @depends testMultipleStoreFetchHasKey
     */
    public function testMultipleStoreClearFetchHasKey()
    {
        $store = new EncryptedFilePersistence('', false, $this->storePath);
        // Store, and fetch key 1.
        $this->assertEquals(true, $store->store('testkey1', 'testvalue1'));
        $result = $store->fetch('testkey1');
        $this->assertEquals('testvalue1', $result);
        // Store, fetch, and clear key 2.
        $this->assertEquals(true, $store->store('testkey2', 'testvalue2'));
        $result = $store->fetch('testkey2');
        $this->assertEquals('testvalue2', $result);
        $this->assertEquals(true, $store->clear('testkey2'));
        // Store and fetch key 2 again.
        $this->assertEquals(true, $store->store('testkey2', 'testvalue2'));
        $result = $store->fetch('testkey2');
        $this->assertEquals('testvalue2', $result);
        // Store, fetch, and clear key 3.
        $this->assertEquals(true, $store->store('testkey3', 'testvalue3'));
        $result = $store->fetch('testkey3');
        $this->assertEquals('testvalue3', $result);
        $this->assertEquals(true, $store->clear('testkey3'));
        // Test all the keys.
        $result = $store->hasKey('testkey1');
        $this->assertEquals(true, $result);
        $result = $store->hasKey('testkey2');
        $this->assertEquals(true, $result);
        $result = $store->hasKey('testkey3');
        $this->assertEquals(false, $result);
    }
}

?>
