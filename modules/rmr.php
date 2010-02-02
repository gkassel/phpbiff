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
 * Recursive deletion tool. ('rm -r')
 *
 * @author Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright Copyright (c) Geoff Kassel, 2010. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GPL-2
 * @package phpbiff
 */

/**
 * Function to recursively delete the given path.
 * Returns true on success; false otherwise.
 *
 * @param string $path The path to delete.
 * @return bool  Whether the recursive delete was successful.
 */
function rmr($path)
{
    // If the path has already been deleted, or doesn't exist, just
    // return true.
    if (!file_exists($path))
    {
        return true;
    }
    // If we have a file or a link, unlink it.
    if (is_file($path) || is_link($path))
    {
        try
        {
            return unlink($path);
        } catch (Exception $e) {
            return false;
        }
    } else {
        // If we have a directory, recurse into it.
        foreach (scandir($path) as $subpath)
        {
            // Ignore the current directory and parent directory entries.
            if (($subpath == '.') || ($subpath == '..'))
            {
                continue;
            }
            // If rmr called recursively on this subpath fails, indicate
            // failure up the chain.
            if (!rmr($path . '/' . $subpath)) {
                return false;
            }
        }
        // Now that the directory has been emptied, delete it.
        try
        {
            return rmdir($path);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
