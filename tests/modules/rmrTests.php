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
 * Test case suite for the rmr function.
 *
 * @author Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright Copyright (c) Geoff Kassel, 2010. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GPL-2
 * @package phpbiff
 */

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../testsettings.php');
require_once(APPLICATION_PATH . '/modules/rmr.php');

class rmrTests extends PHPUnit_Framework_TestCase
{
    /**
     * Test the non-existant file case.
     */
    public function testNonExistant()
    {
        $result = rmr(dirname(__FILE__) . '/tmpdata/');
        $this->assertEquals(true, $result);
    }

    /**
     * Test the empty directory case.
     *
     * @depends testNonExistant
     */
    public function testEmptyDirectory()
    {
        // Create a directory.
        $testPath = dirname(__FILE__) . '/tmpdata';
        mkdir($testPath);

        // Try to delete the directory.
        $result = rmr($testPath);

        // Test continued existance and clean up after.
        $this->assertEquals(true, $result);
        if (is_dir($testPath))
        {
            rmdir($testPath);
            $this->fail("Directory $testPath still exist.");
        }
    }

    /**
     * Test the single file case.
     *
     * @depends testSingleFile
     */
    public function testSingleFile()
    {
        // Create a file.
        $testPath = dirname(__FILE__) . '/tmpdata';
        $fileDescriptor = fopen($testPath, 'w+');
        fwrite($fileDescriptor, 'test');
        fclose($fileDescriptor);

        // Try to delete the file.
        $result = rmr($testPath);
        $this->assertEquals(true, $result);
        if (file_exists($testPath))
        {
            unlink($testPath);
            $this->fail("File $testPath still exist.");
        }
    }

    /**
     * Test the directory with single file case.
     *
     * @depends testSingleFile
     */
    public function testDirectoryWithSingleFile()
    {
        // Create a directory.
        $testPath = dirname(__FILE__) . '/tmpdata';
        mkdir($testPath);

        // Create a file 'a' within the directory.
        $testPathA = dirname(__FILE__) . '/tmpdata/a';
        $fileDescriptor = fopen($testPathA, 'w+');
        fwrite($fileDescriptor, 'test');
        fclose($fileDescriptor);

        // Try to delete the directory.
        $result = rmr($testPath);

        // Test continued existance and clean up after.
        $this->assertEquals(true, $result);
        if (file_exists($testPathA))
        {
            unlink($testPathA);
            if (file_exists($testPath))
            {
                rmdir($testPath);
                $this->fail("Directory $testPath and contents still exist.");
            } else {
                $this->fail("File $testPathA still exist.");
            }
        }
    }

    /**
     * Test the directory with multiple files case.
     *
     * @depends testDirectoryWithSingleFile
     */
    public function testDirectoryWithMultipleFiles()
    {
        // Create a directory.
        $testPath = dirname(__FILE__) . '/tmpdata';
        mkdir($testPath);

        // Create a file 'a' within the directory.
        $testPathA = dirname(__FILE__) . '/tmpdata/a';
        $fileDescriptor = fopen($testPathA, 'w+');
        fwrite($fileDescriptor, 'test');
        fclose($fileDescriptor);

        // Create another file 'b' within the directory.
        $testPathB = dirname(__FILE__) . '/tmpdata/b';
        $fileDescriptor = fopen($testPathB, 'w+');
        fwrite($fileDescriptor, 'test');
        fclose($fileDescriptor);

        // Create another file 'c' within the directory.
        $testPathC = dirname(__FILE__) . '/tmpdata/c';
        $fileDescriptor = fopen($testPathC, 'w+');
        fwrite($fileDescriptor, 'test');
        fclose($fileDescriptor);

        // Test continued existance and clean up after.
        $result = rmr($testPath);
        $this->assertEquals(true, $result);

        $failure = false;
        if (file_exists($testPathA))
        {
            unlink($testPathA);
            $failure = true;
        }
        if (file_exists($testPathB))
        {
            unlink($testPathB);
            $failure = true;
        }
        if (file_exists($testPathC))
        {
            unlink($testPathC);
            $failure = true;
        }

        if (file_exists($testPath))
        {
            rmdir($testPath);
            $failure = true;
        }

        if ($failure)
        {
            $this->fail("Some files/directories could not be deleted.");
        }
    }

    /**
     * Test the directory with multiple subdirectories case.
     *
     * @depends testDirectoryWithMultipleFiles
     */
    public function testDirectoryWithMultipleSubDirectories()
    {
        // Create a directory.
        $testPath = dirname(__FILE__) . '/tmpdata';
        mkdir($testPath);

        // Create a directory 'a' within the directory.
        $testPathA = dirname(__FILE__) . '/tmpdata/a';
        mkdir($testPathA);

        // Create another directory 'b' within the directory.
        $testPathB = dirname(__FILE__) . '/tmpdata/b';
        mkdir($testPathB);

        // Create another directory 'c' within the directory.
        $testPathC = dirname(__FILE__) . '/tmpdata/c';
        mkdir($testPathC);

        // Test continued existance and clean up after.
        $result = rmr($testPath);
        $this->assertEquals(true, $result);

        $failure = false;
        if (file_exists($testPathA))
        {
            rmdir($testPathA);
            $failure = true;
        }
        if (file_exists($testPathB))
        {
            rmdir($testPathB);
            $failure = true;
        }
        if (file_exists($testPathC))
        {
            rmdir($testPathC);
            $failure = true;
        }

        if (file_exists($testPath))
        {
            rmdir($testPath);
            $failure = true;
        }

        if ($failure)
        {
            $this->fail("Some files/directories could not be deleted.");
        }
    }

    /**
     * Test the directory with complex nesting case.
     *
     * @depends testDirectoryComplexNesting
     */
    public function testDirectoryComplexNesting()
    {
        // Create a directory.
        $testPath = dirname(__FILE__) . '/tmpdata';
        mkdir($testPath);

        // Create a directory 'a' within the directory.
        $testPathA = dirname(__FILE__) . '/tmpdata/a';
        mkdir($testPathA);

        // Create another file 'b' within this directory.
        $testPathB = dirname(__FILE__) . '/tmpdata/a/b';
        mkdir($testPathB);

        // Create another file 'c' within this directory.
        $testPathC = dirname(__FILE__) . '/tmpdata/a/b/c';
        mkdir($testPathC);

        // Create a file within 'c'.
        $testPathD = dirname(__FILE__) . '/tmpdata/a/b/c/d';
        $fileDescriptor = fopen($testPathD, 'w+');
        fwrite($fileDescriptor, 'test');
        fclose($fileDescriptor);

        // Create a file within 'b'.
        $testPathE = dirname(__FILE__) . '/tmpdata/a/b/e';
        $fileDescriptor = fopen($testPathE, 'w+');
        fwrite($fileDescriptor, 'test');
        fclose($fileDescriptor);

        // Create a file within 'a'.
        $testPathF = dirname(__FILE__) . '/tmpdata/f';
        $fileDescriptor = fopen($testPathF, 'w+');
        fwrite($fileDescriptor, 'test');
        fclose($fileDescriptor);

        // Create another directory at the base level.
        $testPathG = dirname(__FILE__) . '/tmpdata/g';
        mkdir($testPathG);

        // Create a file within 'g';
        $testPathH = dirname(__FILE__) . '/tmpdata/g/h';
        $fileDescriptor = fopen($testPathH, 'w+');
        fwrite($fileDescriptor, 'test');
        fclose($fileDescriptor);

        // Test continued existance and clean up after.
        $result = rmr($testPath);
        $this->assertEquals(true, $result);

        $failure = false;
        if (file_exists($testPathD))
        {
            unlink($testPathD);
            $failure = true;
        }
        if (file_exists($testPathC))
        {
            rmdir($testPathC);
            $failure = true;
        }
        if (file_exists($testPathE))
        {
            unlink($testPathE);
            $failure = true;
        }
        if (file_exists($testPathB))
        {
            rmdir($testPathB);
            $failure = true;
        }
        if (file_exists($testPathF))
        {
            unlink($testPathF);
            $failure = true;
        }
        if (file_exists($testPathA))
        {
            rmdir($testPathA);
            $failure = true;
        }
        if (file_exists($testPathH))
        {
            unlink($testPathH);
            $failure = true;
        }
        if (file_exists($testPathG))
        {
            rmdir($testPathG);
            $failure = true;
        }

        if (file_exists($testPath))
        {
            rmdir($testPath);
            $failure = true;
        }

        if ($failure)
        {
            $this->fail("Some files/directories could not be deleted.");
        }
    }
}

?>
