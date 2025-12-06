<?php

namespace App\Services;

use RuntimeException;

/**
 * Centralizes encryption and decryption logic for backup payloads.
 * AES-256-GCM with PBKDF2 key derivation (150k iterations) keeps us
 * aligned with the existing controller behaviour while making the
 * workflow reusable for HTTP and CLI entry points.
 */
class BackupEncryptionService
{
    private const ALGORITHM = 'aes-256-gcm';
    private const DEFAULT_ITERATIONS = 150_000;
    private const KEY_LENGTH = 32;
    private const SALT_LENGTH = 16;
    private const IV_LENGTH = 12;

    /**
     * Encrypts the given backup structure with the supplied password.
     *
     * @param  array<string,mixed>  $structure
     * @return array<string,mixed>
     */
    public function encrypt(array $structure, string $password): array
    {
        $plainJson = json_encode($structure, JSON_UNESCAPED_UNICODE);

        if ($plainJson === false) {
            throw new RuntimeException('Yedek ÅŸifrelenemedi: JSON kodlama hatasÄ±.');
        }

        $salt = random_bytes(self::SALT_LENGTH);
        $iv = random_bytes(self::IV_LENGTH);
        $iterations = self::DEFAULT_ITERATIONS;
        $key = $this->deriveKey($password, $salt, $iterations);

        $cipher = openssl_encrypt($plainJson, self::ALGORITHM, $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($cipher === false) {
            throw new RuntimeException('Yedek ÅŸifrelenemedi.');
        }

        return [
            'meta' => [
                'version' => $structure['meta']['version'] ?? '1.1',
                'encrypted' => true,
                'algorithm' => strtoupper(self::ALGORITHM),
                'iterations' => $iterations,
                'salt' => base64_encode($salt),
                'iv' => base64_encode($iv),
                'tag' => base64_encode($tag),
                'checksum' => hash('sha256', $plainJson),
                'generated_at' => $structure['meta']['generated_at'] ?? now()->toIso8601String(),
            ],
            'payload' => base64_encode($cipher),
        ];
    }

    /**
     * Decrypts an encrypted backup structure using the supplied password.
     *
     * @param  array<string,mixed>  $encrypted
     * @return array<string,mixed>
     */
    public function decrypt(array $encrypted, string $password): array
    {
        $meta = $encrypted['meta'] ?? [];

        $salt = $this->decodeBase64($meta['salt'] ?? '', 'salt');
        $iv = $this->decodeBase64($meta['iv'] ?? '', 'iv');
        $tag = $this->decodeBase64($meta['tag'] ?? '', 'tag');
        $cipher = $this->decodeBase64($encrypted['payload'] ?? '', 'payload');

        $iterations = (int) ($meta['iterations'] ?? 0);

        if ($iterations <= 0) {
            throw new RuntimeException('Åifreli yedekte gerekli meta bilgisi eksik.');
        }

        $key = $this->deriveKey($password, $salt, $iterations);

        $plain = openssl_decrypt($cipher, self::ALGORITHM, $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($plain === false) {
            throw new RuntimeException('Şifre çözme işlemi başarısız. Şifreyi kontrol edin.');
        }

        if (! empty($meta['checksum']) && hash('sha256', $plain) !== $meta['checksum']) {
            throw new RuntimeException('Şifreli yedek bozulmuş görünüyor (checksum uyuşmuyor).');
        }

        $plainStructure = json_decode($plain, true);

        if (! is_array($plainStructure)) {
            throw new RuntimeException('Çözülen yedek beklenen yapıda değil.');
        }

        return $plainStructure;
    }

    private function deriveKey(string $password, string $salt, int $iterations): string
    {
        $key = hash_pbkdf2('sha256', $password, $salt, $iterations, self::KEY_LENGTH, true);

        if ($key === false) {
            throw new RuntimeException('Anahtar türetilirken hata oluştu.');
        }

        return $key;
    }

    private function decodeBase64(string $value, string $field): string
    {
        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            throw new RuntimeException(sprintf('Åifreli yedekte gerekli meta bilgisi eksik (%s).', $field));
        }

        return $decoded;
    }
}
