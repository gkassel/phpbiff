<?php
/**
 * Hex digit string ('hexits') to binary string decoder.
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
 * Email: sg_dot_kassel_dot_au_at_gmail_dot_com
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

/**
 * Function to decode a string containing hex digits ('hexits') to the
 * original binary string representation. This is the inverse operation
 * for the built-in bin2hex function.
 * Raises a LogicException if an invalid hex digit string is given.
 *
 * @param string $hex The hex digit string to decode.
 *
 * @return string     The decoded binary string representation.
 */
function hex2bin($hex) {
    $binary = '';
    $len = strlen($hex);
    if (($len % 2) == 1) {
        $message = "Hex string of an invalid length ($len) given.";
        throw new LogicException($message);
    }
    $stepLimit = $len - 1;
    for ($index = 0; $index < $stepLimit; $index += 2) {
        $hexit = substr($hex, $index, 2);
        if (!preg_match("/^[0-9ABCDEF]+$/i", $hexit)) {
            // Calculate the ASCII values of the components of the hexit digit.
            $asciiValue = ord($hexit[0]) . '/' . ord($hexit[1]);
            // Raise a LogicException listing the raw and ASCII value of the
            // invalid hexit.
            $message = "Invalid hex digit '$asciiValue'/$hexit' given,";
            throw new LogicException($message);
        }
        $binary .= chr(hexdec($hexit));
    }
    return $binary;
}

?>
