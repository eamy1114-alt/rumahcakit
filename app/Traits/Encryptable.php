<?php

namespace App\Traits;

use App\Helpers\AES256Encryption;

trait Encryptable
{
    public function setAttribute($key, $value)
    {
        if ($this->isEncryptable($key) && $value !== null && !$this->isAlreadyEncrypted($value)) {
            try {
                $value = 'AES:' . AES256Encryption::encrypt((string)$value);
            } catch (\Exception $e) {
                // Silent fail
            }
        }

        return parent::setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($this->isEncryptable($key) && $value !== null && $this->isAlreadyEncrypted($value)) {
            try {
                $value = AES256Encryption::decrypt($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

    protected function isEncryptable($key)
    {
        return isset($this->encryptable) && in_array($key, $this->encryptable);
    }

    /**
     * Cek apakah data sudah terenkripsi (memiliki prefix AES:)
     */
    protected function isAlreadyEncrypted($value)
    {
        return is_string($value) && str_starts_with($value, 'AES:');
    }

    /**
     * Mendapatkan daftar field yang dienkripsi
     */
    public function getEncryptable()
    {
        return $this->encryptable ?? [];
    }
}