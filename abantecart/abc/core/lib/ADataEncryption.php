<?php

namespace abc\core\lib;

use abc\core\ABC;
use abc\core\engine\Registry;
use H;

/**
 * This class is managing encryption/description of data in AbanteCart database tables
 * configured in $this->enc_data array
 * These tables need to have specific postfix in the name like '_enc'
 *
 * Configuration:
 * Add below configs to abc/config/config.php file.
 *  'DATA_ENCRYPTION_ENABLED' => true);
 *  'ENCRYPTED_POSTFIX' => '_enc');
 * 'DATA_ENCRYPTION_KEYPAIR' => 'data_enc_key');
 *
 * NOTE: DATA_ENCRYPTION_KEYPAIR is a default key and it needs to be a files' name
 *          portion for public and private keys stored in ENCRYPTION_KEYS_DIR
 * Keys can be generated by ASSLEncryption class (see ASSLEncryption class) or by any other openSSL script
 * Example of keys: data_enc_key for data_enc_key.pub and data_enc_key.prv
 * This is also generated in encryption_data_manager extension
 *
 * Tables SQL:
 * New tables needs to be created with provided SQL.
 * Encryption Data Manager extension runs SQL on install
 *
 * Limitation: pass-phrase is not supported to data encryption.
 **/
final class ADataEncryption
{
    private $key_name;
    private $keys;
    private $pass_phrase;
    private $enc_data;
    private $postfix = '';
    public $active = false;
    private $registry;
    private $message;
    private $log;

    function __construct($key_name = null, $pass_phrase = null)
    {
        //if not enabled exit
        if (!ABC::env('DATA_ENCRYPTION_ENABLED')) {
            return null;
        }
        $this->registry = Registry::getInstance();
        $this->log = $this->registry->get('log');
        $this->message = $this->registry->get('messages');

        //load default key
        if ($key_name) {
            $this->key_name = $key_name;
        } else {
            $this->key_name = ABC::env('DATA_ENCRYPTION_KEYPAIR');
        }
        if ($pass_phrase) {
            $this->pass_phrase = $pass_phrase;
        }

        //load keys from database
        $this->loadKeys();

        //set tables/fields encrypted
        $this->enc_data['orders'] = [
            'id'     => 'order_id',
            'fields' => [
                'telephone',
                'fax',
                'email',
                'shipping_company',
                'shipping_address_1',
                'shipping_address_2',
                'shipping_city',
                'shipping_postcode',
                'shipping_country',
                'payment_company',
                'payment_address_1',
                'payment_address_2',
                'payment_city',
                'payment_postcode',
                'payment_country',
            ],
        ];
        $this->enc_data['customers'] = [
            'id'     => 'customer_id',
            'fields' => [
                'telephone',
                'fax',
                'email',
            ],
        ];
        $this->enc_data['addresses'] = [
            'id'     => 'address_id',
            'fields' => [
                'company',
                'address_1',
                'address_2',
                'postcode',
                'city',
            ],
        ];

        if (ABC::env('ENCRYPTED_POSTFIX')) {
            $this->postfix = ABC::env('ENCRYPTED_POSTFIX');
        } else {
            $this->postfix = '_enc';
        }

        $this->active = true;
    }

    /**
     * Get postfix used to extend tables storing encrypted data
     * This is only for tables that require encryption
     * This is set in ENCRYPTED_POSTFIX configuration
     *
     * @param string $table
     *
     * @return string
     */
    public function postfix($table)
    {
        //check if table requires encryption and there is a postfix
        if ($this->getEncryptedTableID($table)) {
            return $this->postfix;
        } else {
            return '';
        }
    }

    /**
     * Get list of tables containing encrypted data
     *
     * @return array
     */
    public function getEncryptedTables()
    {
        if (H::has_value($this->enc_data)) {
            return array_keys($this->enc_data);
        }
        return [];
    }

    /**
     * Get ID field name for table containing encrypted data
     *
     * @param string
     *
     * @return string
     */
    public function getEncryptedTableID($table)
    {
        if (H::has_value($this->enc_data)) {
            return $this->enc_data[$table]['id'];
        }
        return '';
    }

    /**
     * Get list of encrypted fields in table containing encrypted data
     *
     * @param string
     *
     * @return array
     */
    public function getEncryptedFields($table)
    {
        if (H::has_value($this->enc_data)) {
            return (array)$this->enc_data[$table]['fields'];
        }
        return [];
    }

    /**
     * Add to the list of encrypted tables/fields containing encrypted data
     *
     * @param array $table_data
     *
     * @return null
     */
    public function addEncryptedTables($table_data)
    {
        foreach ($table_data as $table => $data) {
            if (in_array($table, $this->getEncryptedTables())) {
                $error = "ADataEncryption Error: Can't add existing table ".$table."! Table already Exists";
                $this->log->write($error);
            } else {
                $this->enc_data[$table] = $data;
            }
        }
        return null;
    }

    /**
     * Add to the list of fields to existing tables containing encrypted data
     *
     * @since 1.2.7
     *
     * @param string $table
     * @param array $fields
     *
     * @return null
     */
    public function addEncryptedFields($table, $fields)
    {
        if (empty($table)) {
            return null;
        }

        foreach ($fields as $field) {
            if (!in_array($field, $this->getEncryptedFields($table))) {
                $this->enc_data[$table][] = $field;
            }
        }
        return null;
    }

    /**
     * Decrypt 1 row of data in table for fields that are encrypted
     *
     * @param array $crypt_data_arr
     * @param string $table
     * @param null|string $pass
     *
     * @return array
     * @throws AException
     */
    public function decrypt_data($crypt_data_arr, $table, $pass = null)
    {
        if (empty($pass)) {
            $pass = $this->pass_phrase;
        }
        if (empty($table)) {
            return [];
        }
        //if encryption off return pure data
        if (!$this->active) {
            return $crypt_data_arr;
        }

        //detect key to use
        $key_name = $this->detectDecryptKey($crypt_data_arr['key_id']);

        $open_data_arr = $crypt_data_arr;
        $enc = new ASSLEncryption('', $key_name, $pass);
        $fields = $this->getEncryptedFields($table);
        foreach ($crypt_data_arr as $key => $data) {
            if (in_array($key, $fields)) {
                $open_data_arr[$key] = $enc->decrypt($data);
            }
        }
        return $open_data_arr;
    }

    /**
     * Encrypt 1 row of data in table for fields that are encrypted
     *
     * @param array $open_data_arr
     * @param string $table
     *
     * @return array
     * @throws AException
     */
    public function encrypt_data($open_data_arr, $table)
    {
        if (empty($table)) {
            return [];
        }
        //if encryption off return pure data
        if (!$this->active) {
            return $open_data_arr;
        }

        $key_name = $this->detectEncryptKey($open_data_arr['key_id']);
        $open_data_arr['key_id'] = $this->getKeyIdByName($key_name);

        $crypt_data_arr = $open_data_arr;
        $enc = new ASSLEncryption($key_name);
        $fields = $this->getEncryptedFields($table);
        foreach ($open_data_arr as $key => $data) {
            if (in_array($key, $fields)) {
                $crypt_data_arr[$key] = $enc->encrypt($data);
            }
        }
        return $crypt_data_arr;
    }

    /**
     * Encrypt 1 field of data
     *
     * @param string $open_data
     * @param int $key_id
     *
     * @return string
     * @throws AException
     */
    public function encrypt_field($open_data, $key_id = 0)
    {
        //if encryption off return pure data
        if (!$this->active) {
            return $open_data;
        }

        //detect key to use
        $key_name = $this->detectEncryptKey($key_id);
        $enc = new ASSLEncryption($key_name);
        return $enc->encrypt($open_data);
    }

    /**
     * Decrypt 1 field of data
     *
     * @param string $crypt_data
     * @param int $key_id
     * @param null|string $pass
     *
     * @return string
     * @throws AException
     */
    public function decrypt_field($crypt_data, $key_id = 0, $pass = null)
    {
        if (empty($pass)) {
            $pass = $this->pass_phrase;
        }
        //if encryption off return pure data
        if (!$this->active) {
            return $crypt_data;
        }

        //detect key to use
        $key_name = $this->detectDecryptKey($key_id);

        $enc = new ASSLEncryption('', $key_name, $pass);
        return $enc->decrypt($crypt_data);
    }

    private function loadKeys()
    {
        $config = $this->registry->get('config');
        $cache = $this->registry->get('cache');

        $this->keys = [];
        $cache_key = 'encryption.keys.store_'.(int)$config->get('config_store_id');
        $this->keys = $cache->pull($cache_key);
        if (empty($this->keys)) {
            $db = Registry::db();
            $query = $db->table('encryption_keys')->where("status", '=', 1)->get();
            if (!$query->count()) {
                return null;
            }
            foreach ($query->toArray() as $row) {
                $this->keys[$row['key_id']] = $row['key_name'];
            }
            $cache->push($cache_key, $this->keys);
        }
    }

    /**
     * @param int $key_id
     *
     * @return null|string
     * @throws AException
     */
    private function detectEncryptKey($key_id)
    {
        //detect key to use (set default first)
        $key_name = $this->key_name;
        $key_id = (int)$key_id;
        if ($key_id > 0) {
            //we have specific key set for record
            if ($this->keys[$key_id]) {
                $key_name = $this->keys[$key_id];
            } else {
                //something happened. we do nto have a key. Report incident.
                $error = "Error: Can not locate key ID: ".$key_id
                    ." in the encryption_keys table. Attempt to locate default keys! ";
                $this->log->write($error);
                $this->message->saveError('Data decryption error', $error);
                throw new AException (AC_ERR_LOAD, $error);
            }
        }

        if ($key_name == ABC::env('DATA_ENCRYPTION_KEYPAIR') && !ABC::env('DATA_ENCRYPTION_KEYPAIR')) {
            $error =
                "Error: Can not locate default key in configuration file. Refer to data encryption configuration help!";
            $this->log->write($error);
            $this->message->saveError('Data encryption error', $error);
            throw new AException (AC_ERR_LOAD, $error);
        }

        return $key_name;
    }

    /**
     * @param int $key_id
     *
     * @return null|string
     * @throws AException
     */
    private function detectDecryptKey($key_id)
    {
        $key_name = $this->key_name;
        $key_id = (int)$key_id;
        if ($key_id > 0) {
            //we have key set for record
            if ($this->keys[$key_id]) {
                $key_name = $this->keys[$key_id];
            } else {
                //something happened. we do nto have a key. Report incident.
                $error = "Error: Can not locate key ID: ".$key_id
                    ." in the encryption_keys table. Record data might not be decrypted! ";
                $this->log->write($error);
                $this->message->saveError('Data decryption error', $error);
                throw new AException (AC_ERR_LOAD, $error);
            }
        }

        /** @noinspection PhpUndefinedConstantInspection */
        if ($key_name == ABC::env('DATA_ENCRYPTION_KEYPAIR') && !ABC::env('DATA_ENCRYPTION_KEYPAIR')) {
            $error =
                "Error: Can not locate default key in configuration file. Refer to data encryption configuration help!";
            $this->log->write($error);
            $this->message->saveError('Data decryption error', $error);
        }

        return $key_name;
    }

    private function getKeyIdByName($key_name)
    {
        if (!count($this->keys)) {
            return 0;
        }
        foreach ($this->keys as $id => $name) {
            if ($key_name == $name) {
                return $id;
            }
        }
        return 0;
    }
}