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
 * Persistence mechanisms.
 *
 * @author Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright Copyright (c) Geoff Kassel, 2010. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GPL-2
 * @package phpbiff
 */

/**
 * Import the application settings.
 */
require_once(dirname(__FILE__) . '/../settings.php');

/**
 * Import the hex2bin function.
 */
require_once(APPLICATION_PATH . '/modules/hex2bin.php');

/**
 * Interface class for key-value persistence.
 *
 * @package phpbiff
 */
interface Persistence
{
    // Instance methods.

    /**
     * Function to retrieve the stored value for the given key.
     * Returns the stored object, or null if the value can't be retrieved.
     *
     * @param string $key The key under which the value is stored.
     * @return mixed      The stored value.
     */
    public function fetch($key);

    /**
     * Function to return whether or not the store contains the given key.
     * Returns true if the key exists in the store; false otherwise.
     *
     * @param string $key The key to be tested.
     * @return bool       Whether the key exists in the store.
     */
    public function hasKey($key);

    /**
     * Function to store the given value under the given key.
     * Returns true on success; false otherwise.
     *
     * @param string $key   The key under which the value is to be stored.
     * @param mixed  $value The value to be stored.
     * @return bool         Whether the store operation was successful.
     */
    public function store($key, $value);
}

/**
 * Concrete class that defines attributes and methods common to all 
 * encrypting persistence methods.
 *
 * @package phpbiff
 */
class EncryptedPersistence implements Persistence
{
    // Instance attributes.

    /**
     * Cipher type used to encrypt data.
     *
     * @var int
     */
    const cipherType = 'serpent';

    /**
     * Encryption resource (for use with mcrypt.)
     *
     * @var mixed
     */
    protected $encryptionResource;

    /**
     * Encryption key to use with this store.
     *
     * @var string
     */
    protected $encryptionKey;

    /**
     * Encryption initialization vector to use with the store.
     *
     * @var int
     */
    protected $encryptionIV;

    /**
     * Encryption mode.
     *
     * @var int
     */
    //const encryptionMode = 'cfb';
    const encryptionMode = 'ecb';

    /**
     * Whether the encryption key is already hashed.
     *
     * @var bool
     */
    protected $isEncryptionKeyHashed;

    // Instance methods.

    /**
     * Creates a new encrypted store from the given parameters.
     *
     * @param string $key The key to use while encrypting data.
     * @param string $isEncryptionKeyHashed Whether the given key is hashed.
     */
    public function __construct($key = '', $isEncryptionKeyHashed = false)
    {
        // Set attributes from the given parameters.
        $this->isEncryptionKeyHashed = $isEncryptionKeyHashed;

        // Open the mcrypt module for the current cipher type and mode.
        $this->encryptionResource =
            mcrypt_module_open(self::cipherType, '',
                               self::encryptionMode, '');

        // Get an appropriate initialization vector for the current cipher
        // type.
        $ivsize = mcrypt_enc_get_iv_size($this->encryptionResource);
        $this->encryptionIV = mcrypt_create_iv($ivsize, MCRYPT_RAND);

        // Get the key size for the current cipher type.
        $keysize = mcrypt_enc_get_key_size($this->encryptionResource);

        // If necessary, convert the key to a SHA-256 hash for use.
        if (!$this->isEncryptionKeyHashed)
        {
            $hashedKey = hash('sha256', $key,
                              $raw_output = false);
            // The key will now be stored in its hashed form.
            $this->isEncryptionKeyHashed = true;
        } else {
            $hashedKey = $key;
        }

        // Mangle the hashed key down to the correct keysize, and store it for
        //later.
        $this->encryptionKey = substr($hashedKey, 0, $keysize);

        // Initialize the encryption resource.
        $this->initializeEncryptionResource();
    }

    /**
     * Destroys the current encrypted store.
     */
    public function __destruct()
    {
        // Close the encryption resource.
        mcrypt_module_close($this->encryptionResource);
    }

    /**
     * Function to decode and decrypt the string returned from encryptData (and
     * stored in an untrusted backend) returning the original binary string.
     *
     * @param string $data The data to decode and decrypt.
     * @return string      The decoded and decrypted binary data.
     */
    public function decryptData($data)
    {
        // Hex decode the incoming data, as the original should have been
        // hex encoded and encrypted.
        $decodedData = hex2bin($data);

        // Initialize the encryption resource for this pass.
        $this->initializeEncryptionResource();

        // Now decrypt the decoded data.
        $decryptedData = mdecrypt_generic($this->encryptionResource, $decodedData);

        // Deinitialize the encryption resource for this pass.
        $this->deinitializeEncryptionResource();

        // Give the decrypted data another hex decode, as the encoding process
        // should have hex encoded the input binary string before passing it to be
        // encrypted.
        $finalDecryptedData = hex2bin($decryptedData);

        // Return the final decoded and decrypted data.
        return $finalDecryptedData;
    }

    /**
     * Function to deinitialize the encryption resource.
     */
    private function deinitializeEncryptionResource()
    {
        // Deinitialize the encryption resource.
        mcrypt_generic_deinit($this->encryptionResource);
    }

    /**
     * Function to encode and encrypt the given potentially untrusted binary
     * data, returning values safe to store in an untrusted backend.
     *
     * @param string $data The binary data to encode and encrypt.
     * @return string      The encoded and encrypted binary data.
     */
    public function encryptData($data)
    {
        // Hex encode the incoming data, so that we don't hit any problems with
        // null characters.
        $encodedData = bin2hex($data);

        // Initialize the encryption resource for this pass.
        $this->initializeEncryptionResource();

        // Now encrypt the encoded data.
        $encryptedData = mcrypt_generic($this->encryptionResource, $encodedData);

        // Deinitialize the encryption resource for this pass.
        $this->deinitializeEncryptionResource();

        // Give the encrypted data a hex encode, to ensure that any null padding
        // is preserved in the underlying store.
        $finalEncryptedData = bin2hex($encryptedData);

        // Return the final encoded and encrypted data.
        return $finalEncryptedData;
    }

    /**
     * Function to initialize the encryption resource.
     */
    private function initializeEncryptionResource()
    {
        // Initialize the module with the store key and the initialization
        //vector.
        mcrypt_generic_init($this->encryptionResource, $this->encryptionKey,
                            $this->encryptionIV);
    }

    /**
     * Function to retrieve the stored value for the given key.
     * Returns the stored object, or null if the value can't be retrieved.
     * Implementation to be supplied by inheriting classes.
     *
     * @abstract
     * @param string $key The key under which the value is stored.
     * @return mixed      The stored value.
     */
    public function fetch($key)
    {
        throw new RuntimeException("Not implemented by this class.");
    }

    /**
     * Function to return whether or not the store contains the given key.
     * Returns true if the key exists in the store; false otherwise.
     * Implementation to be supplied by inheriting classes.
     *
     * @abstract
     * @param string $key The key to be tested.
     * @return bool       Whether the key exists in the store.
     */
    public function hasKey($key)
    {
        throw new RuntimeException("Not implemented by this class.");
    }

    /**
     * Function to store the given value under the given key.
     * Returns true on success; false otherwise.
     * Implementation to be supplied by inheriting classes.
     *
     * @abstract
     * @param string $key   The key under which the value is to be stored.
     * @param mixed  $value The value to be stored.
     * @return bool         Whether the store operation was successful.
     */
    public function store($key, $value)
    {
        throw new RuntimeException("Not implemented by this class.");
    }
}

/**
 * Concrete class implementing encrypted file-based persistence.
 *
 * Key-value pairs are stored as individual files under the store path.
 * This permits file locking to be used during read and write activities,
 * potentially allowing many simultaneous processes to safely access the store.
 *
 * File names are the hashed keys - file contents are the encrypted
 * serialized version of the stored value.
 *
 * @package phpbiff
 */
class EncryptedFilePersistence extends EncryptedPersistence
{
    // Instance attributes.

    /**
     * Cache of keys and their filenames.
     *
     * @var array
     */
    protected $filenameCache;

    /**
     * Path to the file store directory.
     *
     * @var store
     */
    protected $storePath;

    // Instance methods.

    /**
     * Creates a new encrypted file store from the given parameters.
     *
     * @param string $key       The key to use to encrypt the file store.
     * @param string $isEncryptionKeyHashed Whether the given key is hashed.
     * @param string $storePath The path to the file store.
     */
    public function __construct($key = '', $isEncryptionKeyHashed = false,
                                $storePath = '')
    {
        // Call the superclass construction method with the given key.
        EncryptedPersistence::__construct($key, $isEncryptionKeyHashed);

        // Set attributes from the given parameters, setting a safe default if
        // not given a valid value.
        if (!realpath($storePath))
        {
            $storePath = APPLICATION_PATH . '/data/';
        }

        $this->storePath = $storePath;

        // Set up the filename cache.
        $this->filenameCache = array();
    }

    /**
     * Function to retrieve the stored value for the given key.
     * Returns the stored object, or null if the value can't be retrieved.
     *
     * Accesses a file with name equal to the hash of the given key, and
     * retrieves the encrypted, encoded, serialized value from the file.
     * Decodes, decrypts, and deserializes the value from the read data,
     * and returns it. Returns null on any error.
     *
     * @param string $key The key under which the value is stored.
     * @return mixed      The stored value.
     */
    public function fetch($key)
    {
        // Get the filename that maps to the given key.
        $filename = $this->generateFilenameFromKey($key);

        // Construct the full path to the file storing the value.
        $valuePath = $this->storePath + '/' + $filename;

        // Check whether a file exists under the generated filename.
        if (!is_readable($valuePath))
        {
            // No such file exists. Return null.
            return null;
        }

        // Open the file for reading binary data.
        try
        {
            $fileDescriptor = fopen($valuePath, "r");
        } catch (Exception $e) {
            // Something went wrong during the open process. Just return null.
            return null;
        }

        // Attempt to lock the file in shared mode to perform the read.
        if (!flock($fileDescriptor, LOCK_SH))
        {
            // Could not obtain the lock. Just return null.
            return null;
        }

        // Read the data from the file.
        if (!$encryptedData = file_get_contents($valuePath, FILE_TEXT))
        {
            // No data could be read from the file. Return null.
            return null;
        }

        try {
            // Unlock and close the file.
            flock($fileDescriptor, LOCK_UN);
            fclose($fileDescriptor);

            // Decode and decrypt the data read from the file.
            $decryptedData = $this->decryptData($encryptedData);

            // Now deserialize the data.
            $value = unserialize($decryptedData);
        } catch (Exception $e) {
            // Something went wrong, so just return null;
            return null;
        }

        // Return the deserialized value.
        return $value;
    }

    // Instance methods.

    /**
     * Function to return the base filename used to store the value data under
     * the given key.
     *
     * @param string $key The key under which the value is stored.
     * @return string     The base filename (without path).
     */
    private function generateFilenameFromKey($key)
    {
        // Check the filename cache for the existance of this key first.
        if (!isset($this->filenameCache))
        {
            $this->filenameCache = array();
        }

        if (isset($this->filenameCache[$key]))
        {
            // A cached filename was found for this key - return it.
            return $this->filenameCache[$key];
        }

        // If no cached filename could be found for this key, generate one.

        // Use a hash function without a salt that is still collision-resistant
        // enough to take the given key and convert it to a reasonably unique
        // filename in a repeatable (but not reversible) fashion.
        $filename = hash('sha256', $key, $raw_output = false);

        // Cache the generated filename for later.
        $this->filenameCache[$key] = $filename;

        // Now return the generated filename.
        return $filename;
    }

    /**
     * Function to return whether or not the store contains the given key.
     * Returns true if the key exists in the store; false otherwise.
     *
     * @param string $key The key to be tested.
     * @return bool       Whether the key exists in the store.
     */
    public function hasKey($key)
    {
        // Get the filename that maps to the given key.
        $filename = $this->generateFilenameFromKey($key);

        // Construct the full path to the file storing the value.
        $valuePath = $this->storePath + '/' + $filename;

        // Check whether a file exists under the generated filename.
        if (realpath($valuePath))
        {
            // There does, so return true, indicating this.
            return true;
        } else {
            // There does not, so return false, indicating this.
            return false;
        }
    }

    /**
     * Function to store the given value under the given key.
     * Returns true on success; false otherwise.
     *
     * @param string $key   The key under which the value is to be stored.
     * @param mixed  $value The value to be stored.
     * @return bool         Whether the store operation was successful.
     */
    public function store($key, $value)
    {
        // Serialize the given value, in preparation for encrypting and
        // encoding it for storage.
        $serializedValue = serialize($value);

        // Encode and encrypt the serialized value.
        $encryptedData = $this->encryptData($serializedValue);        

        // Generate the filename of a file to store the value from the given key.
        $filename = $this->generateFilenameFromKey($key);

        // Construct the full path to the file that will store the value from
        // the store path.
        $valuePath = $this->storePath + '/' + $filename;

        // Open the value store file for writing binary data.
        try
        {
            $fileDescriptor = fopen($valuePath, "w");
        } catch (Exception $e) {
            // Something went wrong during the open process. Just return false.
            return false;
        }

        // Attempt to lock the file in exclusive mode to perform the write.
        if (!flock($fileDescriptor, LOCK_EX))
        {
            // Could not obtain the lock. Just return false.
            return false;
        }
      
        try
        {
            // Truncate any existing data, then write the encrypted, encoded,
            // serialized value.
            ftruncate($fileDescriptor, 0);
            fwrite($fileDescriptor, $encryptedData);

            // Unlock and close the file.
            flock($fileDescriptor, LOCK_UN);
            fclose($fileDescriptor);
        } catch (Exception $e) {
            // Something went wrong, so just return false.
            return false;
        }

        // If we're here, the value was successfully stored.
        return true;
    }
}

?>
