<?php

namespace He426100\FilecoinTx;

use deemru\Blake2b;
use InvalidArgumentException;
use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;

class Address
{
    const MAINNET = 0;

    const TESTNET = 1;

    const MAINNETPREFIX = "f";

    const TESTNETPREFIX = "t";

    public $currentNetwork = self::MAINNET;

    /**
     * @var PrivateKeyInterface
     */
    private $privateKey;

    public function __construct(string $privateKey = '')
    {
        $generator = EccFactory::getSecgCurves()->generator256k1();
        if (empty($privateKey)) {
            $this->privateKey = $generator->createPrivateKey();
        } else {
            if (!ctype_xdigit($privateKey)) {
                throw new InvalidArgumentException('Private key must be a hexadecimal number');
            }
            if (strlen($privateKey) != 64) {
                throw new InvalidArgumentException('Private key should be exactly 64 chars long');
            }

            $key = gmp_init($privateKey, 16);
            $this->privateKey = $generator->getPrivateKeyFrom($key);
        }
    }

    public function getPrivateKey(): string
    {
        return str_pad(gmp_strval($this->privateKey->getSecret(), 16), 64, '0', STR_PAD_LEFT);
    }

    public function getJsonLotusKey(): string
    {
        return '{"Type":"secp256k1","PrivateKey":"' . base64_encode(hex2bin($this->getPrivateKey())) . '"}';
    }

    public function getHexLotusKey(): string
    {
        return bin2hex($this->getJsonLotusKey());
    }

    public function getPublicKey(): string
    {
        $publicKey = $this->privateKey->getPublicKey();
        $publicKeySerializer = new DerPublicKeySerializer(EccFactory::getAdapter());
        return $publicKeySerializer->getUncompressedKey($publicKey);
    }

    public function get(): string
    {
        $blake2b = new Blake2b(20);
        $hash = $blake2b->hash(hex2bin($this->getPublicKey()));
        $pubhash = "01" . bin2hex($hash);
        $blake2b2 = new Blake2b(4);
        $checksum = bin2hex($blake2b2->hash(hex2bin($pubhash)));
        $hex_str = bin2hex($hash) . $checksum;
        $prefix = $this->currentNetwork == self::MAINNET ? self::MAINNETPREFIX : self::TESTNETPREFIX;
        return $prefix . "1" . strtolower(\SKleeschulte\Base32::encodeByteStr(hex2bin($hex_str), true));
    }

    public static function fromHexLotus(string $hex): self
    {
        return new static(bin2hex(base64_decode(json_decode(hex2bin($hex), true)['PrivateKey'])));
    }
}
