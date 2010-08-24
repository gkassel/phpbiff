<?php
/**
 * Combined application test suite for PHPBiff.
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
require_once(dirname(__FILE__) . '/../bootstrap.php');
require_once(dirname(__FILE__) . '/models/ModelTests.php');
require_once(dirname(__FILE__) . '/modules/ModuleTests.php');

/**
 * Combined application test suite for PHPBiff.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright 2010 Geoff Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class ApplicationTests {
    /**
     * Main call point for test suite, per PHPUnit convention.
     *
     * @return NULL
     */
    public static function main() {
        $parameters = array();
        PHPUnit_TextUI_TestRunner::run(self::suite(), $parameters);
    }

    /**
     * Suite contained by this class, per PHPUnit convention.
     *
     * @return NULL
     */
    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('phpbiff');
        $suite->addTest(ModuleTests::suite());
        $suite->addTest(ModelTests::suite());
        return $suite;
    }
}
?>
