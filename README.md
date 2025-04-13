# 发票SDK

发票SDK php 数电发票/电子发票/数电票/全电发票/开票 
支持发票开具、红冲、查询等功能。
基础

[中文文档](https://github.com/fapiaoapi/invoice "文档")

* 获取授权
* 登录数电发票平台
* 获取人脸二维码
* 获取人脸二维码认证状态
* 获取认证状态

发票开具

* 数电蓝票开具接口
* 获取销项数电版式文件

发票红冲

* 申请红字前查蓝票信息接口
* 申请红字信息表
* 开负数发票

## 安装

通过Composer安装:

```bash
composer require tax/invoice
```

```bash
use Tax\Invoice\Client;
use Tax\Invoice\InvoiceFactory;
use Tax\Invoice\Constants;

// 配置信息
$appKey = 'your_app_key';
$appSecret = 'your_app_secret';
$nsrsbh = '915101820724315989'; // 纳税人识别号

// 创建客户端
$client = new Client($appKey, $appSecret);

 // 获取授权
try {
   
    $authResult = $client->getAuthorization($nsrsbh);
    if ($authResult['code'] == 200) {
        $token = $authResult['data']['token'];
        $client->setToken($token);
        echo "授权成功，Token: " . $token . PHP_EOL;
            $loginResult = $client->queryFaceAuthState($nsrsbh, $username);
            switch ($loginResult['code']) {
                case 420:
                    // 短信超时
                    echo "请先完成短信验证\n";
                    exit;
                case 430:
                    // 获取人脸二维码 再用税务app或者个税app扫描二维码完成人脸验证
                    echo "请先完成人脸验证(通过税务app或者个税app)\n";
                    exit; 
                case 200:
                        echo "可以开发票了\n";
                    break;
            }
    } else {
        echo "授权失败: " . $authResult['msg'] . PHP_EOL;
        exit;
    }
} catch (\Tax\Invoice\Exception\InvoiceException $e) {
    echo "错误: " . $e->getMessage() . PHP_EOL;
    exit;
}


```
