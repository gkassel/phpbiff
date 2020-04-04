<?php
/**
 * Combined test suite for the PHPBiff utility modules.
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
require_once(dirname(__FILE__) . '/BaseMockServerTests.php');
require_once(dirname(__FILE__) . '/BaseServerConnectionTests.php');
require_once(dirname(__FILE__) . '/EncryptedPersistenceTests.php');
require_once(dirname(__FILE__) . '/EncryptedFilePersistenceTests.php');
require_once(dirname(__FILE__) . '/POP3MockMailServerTests.php');
require_once(dirname(__FILE__) . '/POP3ServerConnectionTests.php');
require_once(dirname(__FILE__) . '/ServerConnectionFactoryTests.php');
require_once(dirname(__FILE__) . '/hex2binTests.php');
require_once(dirname(__FILE__) . '/rmrTests.php');

/**
 * Combined test suite for the PHPBiff utility modules.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class ModuleTests {
    /**
     * Main call point for test suite, per PHPUnit convention.
     *
     * @return NULL
     */
    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * Suite contained by this class, per PHPUnit convention.
     *
     * @return NULL
     */
    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('phpbiff - module tests');
        $suite->addTestSuite('BaseMockServerTests');
        $suite->addTestSuite('BaseServerConnectionTests');
        $suite->addTestSuite('EncryptedPersistenceTests');
        $suite->addTestSuite('EncryptedFilePersistenceTests');
        $suite->addTestSuite('POP3MockMailServerTests');
        $suite->addTestSuite('POP3ServerConnectionTests');
        $suite->addTestSuite('ServerConnectionFactoryTests');
        $suite->addTestSuite('Hex2binTests');
        $suite->addTestSuite('RmrTests');
        return $suite;
    }
}
?>
