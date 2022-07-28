<?php

namespace Tests;

use He426100\FilecoinTx\Sign;
use He426100\FilecoinTx\Rpc;
use PHPUnit\Framework\TestCase as BaseTestCase;

class SignTest extends BaseTestCase
{
    public function testAddress()
    {
        $fromAddress = 't1hb4737umuzzbcfd3xxk3bdtwezgistj7dycypvi';
        $fromPrivateKey = 'ee2868ca9485673b36c38ba4f18551be25d08dd9be9bd24c44cd626b37cadae4';
        $toAddress = 't1qkqqbmrbhsvjdturbalnyb3tudqxtmbp6x7ohry';

        $rpc = new Rpc(['apiAddress' => 'https://calibration.filscan.io:8700/rpc/v1']);
        $res = $rpc->request('WalletBalance', $fromAddress);
        // get privateKey from hex-lotus
        $this->assertTrue($res > bcpow(10, 18));

        $nonce = $rpc->request('MpoolGetNonce', $fromAddress);
        $this->assertTrue(is_numeric($nonce) && $nonce >= 0);

        $message = [
            'Version' => 0,
            'To' => $toAddress,
            'From' => $fromAddress,
            'Value' => bcmul('0.1', bcpow(10, 18)),
            'Method' => 0,
            'Nonce' => $nonce,
            'Params' => ''
        ];
          
        $gas = $rpc->request('GasEstimateMessageGas', $message, ['MaxFee' => bcmul('0.1', bcpow(10, 18))], null);
        $this->assertTrue(isset($gas['GasPremium']));

        $message['GasPremium'] = $gas['GasPremium'];
        $message['GasFeeCap'] = $gas['GasFeeCap'];
        $message['GasLimit'] = $gas['GasLimit'];
        
        $sign = new Sign();
        $signature = $sign->sign($message, $fromPrivateKey);
        $this->assertNotEmpty($signature);

        echo json_encode([
            'jsonrpc' => '2.0',
            'method' => "Filecoin.MpoolPush",
            'params' => [[
                'Message' => $message,
                'Signature' => [
                    'Data' => $signature,
                    'Type' => 1
                ]
            ]],
            'id' => 1,
        ]);
        try {
            $rpc->request('MpoolPush', [ 'Message' => $message, 'Signature' => [ 'Data' => $signature, 'Type' => 1]]);
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), "missing permission to invoke 'MpoolPush' (need 'write')");
        }
    }
}
