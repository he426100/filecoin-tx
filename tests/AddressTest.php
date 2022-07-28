<?php

namespace Tests;

use He426100\FilecoinTx\Address;
use PHPUnit\Framework\TestCase as BaseTestCase;

class AddressTest extends BaseTestCase
{
    public function testAddress()
    {
        $address = new Address('ee2868ca9485673b36c38ba4f18551be25d08dd9be9bd24c44cd626b37cadae4');

        // setting network type
        $address->currentNetwork = Address::TESTNET;

        // get address
        $this->assertEquals($address->get(), 't1hb4737umuzzbcfd3xxk3bdtwezgistj7dycypvi');
        
        // get private key
        $this->assertEquals($address->getPrivateKey(), 'ee2868ca9485673b36c38ba4f18551be25d08dd9be9bd24c44cd626b37cadae4');

        // get public key
        $this->assertEquals($address->getPublicKey(), '040c53eabf2389b06d36cb4e65a935c5473c0d2a0dd130b6a00bb1e3f987d7b7719079f5f1be4c3b35f3a12e31191632bf8fdd73de5cae553eadbd63d2cba41879');

        // get json-lotus key
        $this->assertEquals($address->getJsonLotusKey(), '{"Type":"secp256k1","PrivateKey":"7ihoypSFZzs2w4uk8YVRviXQjdm+m9JMRM1iazfK2uQ="}');

        // get hex-lotus key
        $this->assertEquals($address->getHexLotusKey(), '7b2254797065223a22736563703235366b31222c22507269766174654b6579223a223769686f797053465a7a73327734756b38595652766958516a646d2b6d394a4d524d3169617a664b3275513d227d');
    
        // get privateKey from hex-lotus
        $this->assertEquals($address->fromHexLotus('7b2254797065223a22736563703235366b31222c22507269766174654b6579223a223769686f797053465a7a73327734756b38595652766958516a646d2b6d394a4d524d3169617a664b3275513d227d')->getPrivateKey(), 'ee2868ca9485673b36c38ba4f18551be25d08dd9be9bd24c44cd626b37cadae4');
    }
}
