<?php
/**
 * Test suite for the hex2bin function.
 *
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
 *
 * PHP Version 5.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright 2010 Geoff Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../../bootstrap.php');
require_once(APPLICATION_PATH . '/modules/hex2bin.php');

/**
 * Test suite for the hex2bin function.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright 2010 Geoff Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class Hex2binTests extends PHPUnit_Framework_TestCase {
    /**
     * Test the empty input case.
     *
     * @return NULL
     */
    public function testNullInput() {
        $result = hex2bin('');
        $this->assertEquals('', $result);
    }

    /**
     * Test an invalid length string.
     *
     * @expectedException LogicException
     *
     * @return NULL
     */
    public function testInvalidLength() {
        $result = hex2bin('1');
    }

    /**
     * Test a single invalid character.
     *
     * @depends testNullInput
     * @expectedException LogicException
     *
     * @return NULL
     */
    public function testInvalidCharacter() {
        $result = hex2bin('6z');
    }

    /**
     * Test a string with invalid characters.
     *
     * @depends testInvalidCharacter
     * @expectedException LogicException
     *
     * @return NULL
     */
    public function testInvalidString() {
        $result = hex2bin('65656z6z656565');
    }

    /**
     * Test a single valid character.
     *
     * @depends testNullInput
     *
     * @return NULL
     */
    public function testValidCharacter() {
        $result = hex2bin('65');
        $this->assertEquals('e', $result);
    }

    /**
     * Test a string of valid characters.
     *
     * @depends testValidCharacter()
     *
     * @return NULL
     */
    public function testValidString() {
        $result = hex2bin('68656C6c6F0a746865726500');
        $this->assertEquals("hello\nthere\0", $result);
    }
}

?>
