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
 * Test case suite for the hex2bin function.
 *
 * @author Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright Copyright (c) Geoff Kassel, 2010. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GPL-2
 * @package phpbiff
 */

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../testsettings.php');
require_once(APPLICATION_PATH . '/modules/hex2bin.php');

class hex2binTests extends PHPUnit_Framework_TestCase
{
    /**
     * Test the empty input case.
     */
    public function testNullInput()
    {
        $result = hex2bin('');
        $this->assertEquals('', $result);
    }

    /**
     * Test an invalid length string.
     *
     * @expectedException LogicException
     */
    public function testInvalidLength()
    {
        $result = hex2bin('1');
    }

    /**
     * Test a single invalid character.
     *
     * @depends testNullInput
     * @expectedException LogicException
     */
    public function testInvalidCharacter()
    {
        $result = hex2bin('6z');
    }

    /**
     * Test a string with invalid characters.
     *
     * @depends testInvalidCharacter
     * @expectedException LogicException
     */
    public function testInvalidString()
    {
        $result = hex2bin('65656z6z656565');
    }

    /**
     * Test a single valid character.
     *
     * @depends testNullInput
     */
    public function testValidCharacter()
    {
        $result = hex2bin('65');
        $this->assertEquals('e', $result);
    }

    /**
     * Test a string of valid characters.
     *
     * @depends testValidCharacter()
     */
    public function testValidString()
    {
        $result = hex2bin('68656C6c6F0a746865726500');
        $this->assertEquals("hello\nthere\0", $result);
    }
}

?>
