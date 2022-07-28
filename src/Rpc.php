<?php

namespace He426100\FilecoinTx;

class Rpc
{
    protected $apiAddress;
    protected $token;
    protected $id = 0;

    public function __construct(array $config)
    {
        if (empty($config)) {
            throw new \Exception('Must pass a config object to the LotusRpcEngine constructor.');
        }
        $this->apiAddress = $config['apiAddress'] ?? 'http://127.0.0.1:1234/rpc/v0';
        $this->token = $config['token'] ?? '';
    }
    
    public function request($method, ...$params)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiAddress,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'jsonrpc' => '2.0',
                'method' => "Filecoin.{$method}",
                'params' => $params,
                'id' => ++$this->id,
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        
        $data = json_decode($response, true);
        if (isset($data['error'])) {
            throw new \Exception($data['error']['message']);
        }
        return $data['result'];
    }
}