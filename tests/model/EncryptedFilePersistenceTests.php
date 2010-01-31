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
     */
    public function testStoreSingleHasKey()
    {
        $store = new EncryptedFilePersistence('', false, $this->storePath);
        $this->assertEquals(true, $store->store('testkey', 'testvalue'));
        $result = $store->hasKey('testkey');
        $this->assertEquals(true, $result);
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
        $this->assertEquals(true, $store->store('testkey', 'testvalue'));
    }
}

?>
