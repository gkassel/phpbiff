<?php
/**
 * Recursive deletion tool. ('rm -r')
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

/**
 * Function to recursively delete the given path.
 * Returns TRUE on success; FALSE otherwise.
 *
 * @param string $path The path to delete.
 *
 * @return bool  Whether the recursive delete was successful.
 */
function rmr($path) {
    // If the path has already been deleted, or doesn't exist, just
    // return TRUE.
    if (!file_exists($path)) {
        return TRUE;
    }
    // If we have a file or a link, unlink it.
    if (is_file($path) || is_link($path)) {
        try {
            return unlink($path);
        } catch (Exception $e) {
            return FALSE;
        }
    } else {
        // If we have a directory, recurse into it.
        foreach (scandir($path) as $subpath) {
            // Ignore the current directory and parent directory entries.
            if (($subpath == '.') || ($subpath == '..')) {
                continue;
            }
            // If rmr called recursively on this subpath fails, indicate
            // failure up the chain.
            if (!rmr($path . '/' . $subpath)) {
                return FALSE;
            }
        }
        // Now that the directory has been emptied, delete it.
        try {
            return rmdir($path);
        } catch (Exception $e) {
            return FALSE;
        }
    }
}
?>
