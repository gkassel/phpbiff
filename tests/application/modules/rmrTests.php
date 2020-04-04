<?php
/**
 * Test suite for the rmr function.
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
require_once(APPLICATION_PATH . '/modules/rmr.php');

/**
 * Test suite for the rmr function.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class RmrTests extends PHPUnit_Framework_TestCase {
    /**
     * Test the non-existant file case.
     *
     * @return NULL
     */
    public function testNonExistant() {
        $result = rmr(dirname(__FILE__) . '/tmpdata/');
        $this->assertEquals(TRUE, $result);
    }

    /**
     * Test the empty directory case.
     *
     * @depends testNonExistant
     *
     * @return NULL
     */
    public function testEmptyDirectory() {
        // Create a directory.
        $testPath = dirname(__FILE__) . '/tmpdata';
        mkdir($testPath);

        // Try to delete the directory.
        $result = rmr($testPath);

        // Test continued existance and clean up after.
        $this->assertEquals(TRUE, $result);
        if (is_dir($testPath)) {
            rmdir($testPath);
            $this->fail("Directory $testPath still exist.");
        }
    }

    /**
     * Test the single file case.
     *
     * @depends testSingleFile
     *
     * @return NULL
     */
    public function testSingleFile() {
        // Create a file.
        $testPath = dirname(__FILE__) . '/tmpdata';
        $fileDescriptor = fopen($testPath, 'w+');
        fwrite($fileDescriptor, 'test');
        fclose($fileDescriptor);

        // Try to delete the file.
        $result = rmr($testPath);
        $this->assertEquals(TRUE, $result);
        if (file_exists($testPath)) {
            unlink($testPath);
            $this->fail("File $testPath still exist.");
        }
    }

    /**
     * Test the directory with single file case.
     *
     * @depends testSingleFile
     *
     * @return NULL
     */
    public function testDirectoryWithSingleFile() {
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
        $this->assertEquals(TRUE, $result);
        if (file_exists($testPathA)) {
            unlink($testPathA);
            if (file_exists($testPath)) {
                rmdir($testPath);
                $this->fail("Directory $testPath and contents still exist.");
            } else {
                $this->fail("File $testPathA still exist.");
            }
        }
    }

    /**
     * Test the directory with single file failed delete case.
     *
     * @depends testSingleFile
     *
     * @return NULL
     */
    public function testDirectoryWithSingleFileFailedDelete() {
        // Create a directory.
        $testPath = dirname(__FILE__) . '/tmpdata';
        mkdir($testPath);

        // Create a file 'a' within the directory.
        $testPathA = dirname(__FILE__) . '/tmpdata/a';
        $fileDescriptor = fopen($testPathA, 'w+');
        fwrite($fileDescriptor, 'test');
        fclose($fileDescriptor);

        // Remove the write permission from the directory.
        chmod($testPath, 0500);

        // Try to delete the directory.
        $result = rmr($testPath);

        // Test continued existance and clean up after.
        $this->assertEquals(FALSE, $result);
        if (file_exists($testPathA)) {
            // Replace the write permission on the directory.
            chmod($testPath, 0700);
            unlink($testPathA);
            if (file_exists($testPath)) {
                rmdir($testPath);
            }
        }
    }

    /**
     * Test the directory with single file failed delete case.
     *
     * @depends testDirectoryWithSingleFileFailedDelete
     *
     * @return NULL
     */
    public function testDirectoryWithSingleDirectoryFailedDelete() {
        // Create a directory.
        $testPath = dirname(__FILE__) . '/tmpdata';
        mkdir($testPath);

        // Create a directory 'a' within the top directory.
        $testPathA = dirname(__FILE__) . '/tmpdata/a';
        mkdir($testPathA);

        // Remove the write permission from the top directory.
        chmod($testPath, 0500);

        // Remove the write permission from the 'a' directory.
        chmod($testPathA, 0500);

        // Try to delete the directory.
        $result = rmr($testPath);

        // Test continued existance and clean up after.
        $this->assertEquals(FALSE, $result);
        if (file_exists($testPathA)) {
            // Replace the write permission on the directories.
            chmod($testPath, 0700);
            chmod($testPathA, 0700);
            rmdir($testPathA);
            if (file_exists($testPath)) {
                rmdir($testPath);
            }
        }
    }

    /**
     * Test the directory with multiple files case.
     *
     * @depends testDirectoryWithSingleFile
     *
     * @return NULL
     */
    public function testDirectoryWithMultipleFiles() {
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
        $this->assertEquals(TRUE, $result);

        $failure = FALSE;
        if (file_exists($testPathA)) {
            unlink($testPathA);
            $failure = TRUE;
        }
        if (file_exists($testPathB)) {
            unlink($testPathB);
            $failure = TRUE;
        }
        if (file_exists($testPathC)) {
            unlink($testPathC);
            $failure = TRUE;
        }

        if (file_exists($testPath)) {
            rmdir($testPath);
            $failure = TRUE;
        }

        if ($failure) {
            $this->fail("Some files/directories could not be deleted.");
        }
    }

    /**
     * Test the directory with multiple subdirectories case.
     *
     * @depends testDirectoryWithMultipleFiles
     *
     * @return NULL
     */
    public function testDirectoryWithMultipleSubDirectories() {
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
        $this->assertEquals(TRUE, $result);

        $failure = FALSE;
        if (file_exists($testPathA)) {
            rmdir($testPathA);
            $failure = TRUE;
        }
        if (file_exists($testPathB)) {
            rmdir($testPathB);
            $failure = TRUE;
        }
        if (file_exists($testPathC)) {
            rmdir($testPathC);
            $failure = TRUE;
        }

        if (file_exists($testPath)) {
            rmdir($testPath);
            $failure = TRUE;
        }

        if ($failure) {
            $this->fail("Some files/directories could not be deleted.");
        }
    }

    /**
     * Test the directory with complex nesting case.
     *
     * @depends testDirectoryComplexNesting
     *
     * @return NULL
     */
    public function testDirectoryComplexNesting() {
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
        $this->assertEquals(TRUE, $result);

        $failure = FALSE;
        if (file_exists($testPathD)) {
            unlink($testPathD);
            $failure = TRUE;
        }
        if (file_exists($testPathC)) {
            rmdir($testPathC);
            $failure = TRUE;
        }
        if (file_exists($testPathE)) {
            unlink($testPathE);
            $failure = TRUE;
        }
        if (file_exists($testPathB)) {
            rmdir($testPathB);
            $failure = TRUE;
        }
        if (file_exists($testPathF)) {
            unlink($testPathF);
            $failure = TRUE;
        }
        if (file_exists($testPathA)) {
            rmdir($testPathA);
            $failure = TRUE;
        }
        if (file_exists($testPathH)) {
            unlink($testPathH);
            $failure = TRUE;
        }
        if (file_exists($testPathG)) {
            rmdir($testPathG);
            $failure = TRUE;
        }

        if (file_exists($testPath)) {
            rmdir($testPath);
            $failure = TRUE;
        }

        if ($failure) {
            $this->fail("Some files/directories could not be deleted.");
        }
    }
}

?>
