# filecoin-tx 
此项目源自 [https://github.com/yuminuo/filecoin-tx](https://github.com/yuminuo/filecoin-tx)

Filecoin transaction library in PHP(Only for secp256k1).

# Install

```
composer require he426100/filecoin-tx
```

# Usage

#### sign

Returns signed of transaction data.

`sign(array $message, string $privateKey)`

###### Example

* Sign the transaction data.

```php
use He426100\FilecoinTx\Sign;

$fromAddress = 't1hb4737umuzzbcfd3xxk3bdtwezgistj7dycypvi';
$rpc = new Rpc(...);
$nonce = $rpc->request('MpoolGetNonce', $fromAddress);

$message = [
    'Version' => 0,
    'From' => $fromAddress,
    'To' => 't1qkqqbmrbhsvjdturbalnyb3tudqxtmbp6x7ohry',
    'Value' => '100000000000000000', // 此参数必须是字符串 0.1 FIL
    'Method' => 0, // 表示send
    'Nonce' => $nonce, // 交易序号，用接口 MpoolGetNonce 获取
    'Params' => '', // base64 编码数据
    'GasLimit' => 0, // 可用接口估算 GasEstimateGasLimit
    'GasPremium' => "0", // 此参数必须是字符串，可用接口估算 GasEstimateGasPremium
    'GasFeeCap' => "0" // 此参数必须是字符串，可用接口估算 GasEstimateFeeCap
];

$gas = $rpc->request('GasEstimateMessageGas', $message, ['MaxFee' => bcmul('0.1', bcpow(10, 18))], null);
$message['GasPremium'] = $gas['GasPremium'];
$message['GasFeeCap'] = $gas['GasFeeCap'];
$message['GasLimit'] = $gas['GasLimit'];

$sign = new Sign();
$signData = $sign->sign($message, "ee2868ca9485673b36c38ba4f18551be25d08dd9be9bd24c44cd626b37cadae4");
//获取messageID
$message['cid'] = ['/' => $sign->getMessageId()];
//获取离线计算发送返回CID
$sign->getCid();
$signMessageData = [
    'message' => $message,
    'signature' => [
        'data' => $signData,
        'type' => 1 //SECP256K1=1
    ]
];
```

# License
MIT
