<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tax\Invoice\Client;
use Tax\Invoice\Exception\InvoiceException;
use Tax\Invoice\InvoiceFactory;
use Tax\Invoice\Constants;
use Tax\Invoice\Utils;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

// 配置信息
$appKey = '';
$appSecret = '';

$nsrsbh = '91500112MADFXXXX'; // 统一社会信用代码
$title = "XXX有限公司";//名称（营业执照）
$username = "";//手机号码（电子税务局）
$password = "";//个人用户密码（电子税务局）
$sf = "01";//身份（电子税务局）
$fphm = "";
$kprq = "";
$token = "";



// 创建客户端
$client = new Client($appKey, $appSecret);

try {

    $redis = new Redis();
    //一 获取授权

    // 从缓存redis中获取Token
    $redis->connect('127.0.0.1', 6380);
    $redis->auth('123456');
    $key = $nsrsbh ."@".$username. "@TOKEN";
    $token = $redis->get($key);
    if ($token) {
        $client->setToken($token);
        echo "Token From Redis: " . PHP_EOL;
    } else {
        /*
         * 获取授权Token文档
         * @link https://fa-piao.com/doc.html#api1?source=github
         */
        $authResult = $client->getAuthorization($nsrsbh);
        if ($authResult['code'] == 200) {
            $token = $authResult['data']['token'];
            $client->setToken($token);
            $redis->set($key, $token, 3600 * 24 * 30); // 设置过期时间为30天
            echo "授权成功，Token: " . $token . PHP_EOL;
        } else {
            echo "授权失败: " . $authResult['msg'] . PHP_EOL;
            exit;
        }
    }

    /*
     * 前端模拟数电发票/电子发票开具 (蓝字发票)
     * @link https://fa-piao.com/fapiao.html?source=github
     *
     * json2php在线工具可以将前端json数据转换为php数组格式，方便后端开发调试
     * @link https://uutool.cn/json2php/
     */
    //二 开具蓝票
    /*
     * 开具数电发票文档
     * @link https://fa-piao.com/doc.html#api6?source=github
     *
     * 开票参数说明demo
     * @link https://github.com/fapiaoapi/invoice-sdk-php/blob/master/examples/tax_example.php
     */
    $invoiceParams = [
        "fpqqlsh" => $appKey . time(),
        "username" => $username,
        "kplx" =>0,
        "fplxdm" =>"82",
        "gfzrrbs" =>"N",
        "ghdwmc" =>"客户公司",
        "ghdwsbh" =>"91370100MA23333333",
        "ghdwdzdh" =>"",
        "sfzsgmfdzdh" =>"",
        "ghdwyhzh" =>"",
        "sfzsgmfyhzh" =>"",
        "xhdwmc" =>$title,
        "xhdwsbh" =>$nsrsbh,
        "xhdwdzdh" =>"北京市海淀区巴沟路2号 010-88888888",
        "sfzsxsfdzdh" =>"",
        "xhdwyhzh" =>"北京银行中关村科技园区支行 17585654545",
        "sfzsxsfyhzh" =>"",
        "hjje" =>198.02,
        "hjse" =>1.98,
        "jshj" =>200,
        "fyxm" =>[
            [
                "fphxz" =>0,
                "hsbz" =>1,
                "spmc" =>"*软件维护服务*接口服务费",
                "spbm" =>"3040201030000000000",
                "je" =>200,
                "sl" =>0.01,
                "se" =>1.98
            ]
        ],
        "bz" =>"",
        "kpr" =>"张三"
    ];
    /*
     * 数电蓝票开具接口 文档
     * @link https://fa-piao.com/doc.html#api6?source=github
     */
    $invoiceResponse = $client->blueTicket($invoiceParams);
    if ($invoiceResponse['code'] == 200) {
        $fphm = $invoiceResponse['data']['Fphm'];
        $kprq = $invoiceResponse['data']['Kprq'];
        echo "发票号码: " . $fphm . PHP_EOL;
        echo "开票日期: " . $invoiceResponse['data']['Kprq'] . PHP_EOL;
    }

    /*
     * 获取销项数电版式文件 文档 PDF/OFD/XML
     * @link https://fa-piao.com/doc.html#api7?source=github
     */
    $pdfResponse = $client->getPdfOfdXml($nsrsbh, $fphm, '4', $kprq, $username);
    if ($pdfResponse['code'] == 200) {
       echo "发票下载成功".PHP_EOL;
       echo json_encode($pdfResponse['data']) . PHP_EOL;

    }
} catch (InvoiceException $e) {
    switch ($e->getErrorCode()) {
        case 420:
            echo "登录(短信认证)". PHP_EOL;
            /*
             * 前端模拟短信认证弹窗
             * @link https://fa-piao.com/fapiao.html?action=sms&source=github
             */
             // 1. 发短信验证码
            /*
             * @link https://fa-piao.com/doc.html#api2?source=github
             */
//             $loginResponse = $client->loginDppt($nsrsbh, $username, $password, "");
//             if ($loginResponse['code'] == 200) {
//                 echo $loginResponse['msg'] . PHP_EOL;
//                 echo "请" . $username . "接收验证码". PHP_EOL;
//                 sleep(60); // 等待60秒
//             }
             // 2. 输入验证码
            /*
             * @link https://fa-piao.com/doc.html#api2?source=github
             */
//             echo "请输入验证码". PHP_EOL;
//             $smsCode = ""; // 这里应该获取用户输入的验证码
//             $loginResponse2 = $client->loginDppt($nsrsbh, $username, $password, $smsCode);
//             if ($loginResponse2['code'] == 200) {
//                 echo $loginResponse2['data'] . PHP_EOL;
//                 echo "验证成功". PHP_EOL;
//             }
            break;
        case 430:
            echo "人脸认证". PHP_EOL;
            /*
             * 前端模拟人脸认证弹窗
             * @link https://fa-piao.com/fapiao.html?action=face&source=github
             */
            // 1. 获取人脸二维码
            /*
             * @link https://fa-piao.com/doc.html#api3?source=github
             */
//            $qrCodeResponse = $client->getFaceImg($nsrsbh, $username, "1");
//            echo $qrCodeResponse['data']['ewmly'] == 'swj' ? "请使用税务局app扫码". PHP_EOL : "个人所得税app扫码". PHP_EOL;
//            if (isset($qrCodeResponse['data']['ewm']) && strlen($qrCodeResponse['data']['ewm']) < 500) {
//                //composer require endroid/qr-code 构建二维码并获取 Base64
//                $base64 = Builder::create()
//                    ->data($qrCodeResponse['data']['ewm'])
//                    ->size(300) // 尺寸
//                    ->encoding(new Encoding('UTF-8'))
//                    ->errorCorrectionLevel(ErrorCorrectionLevel::High)
//                    ->build()
//                    ->getString(); // 获取 PNG 二进制数据
//                $qrCodeResponse['data']['ewm'] = base64_encode($base64); // 转换为 Base64 编码
//                /*
//                 * $base64Uri = 'data:image/png;base64,' . $qrCodeResponse['data']['ewm'];
/*                 * 前端使用示例: <img src="<?php echo $base64Uri; ?>" />*/
//                 */
//            }


            // 2. 认证完成后获取人脸二维码认证状态
            /*
             * @link https://fa-piao.com/doc.html#api4?source=github
             */
//            $rzid = $qrCodeResponse['data']['rzid'];
//            $faceStatusResponse = $client->getFaceState($nsrsbh, $rzid, $username, "1");
//            echo "code: " . $faceStatusResponse['code'] . PHP_EOL;
//            echo "data: " . print_r($faceStatusResponse['data'], true) . PHP_EOL;
//
//            if ($faceStatusResponse['data'] != null) {
//                echo "认证状态: " . $faceStatusResponse['data']['slzt'] == '1' ? "未认证" : ($faceStatusResponse['data']['slzt'] == '2' ? "成功" : "二维码过期") . PHP_EOL;
//            }
            break;
        case 401:
            //token过期 重新获取并缓存token
            echo $e->getErrorCode() . "授权失败:" . $e->getMessage() . PHP_EOL;
            break;
        case 503:
            //服务器繁忙 重新发起请求即可
            echo $e->getErrorCode() . "服务器繁忙:" . $e->getMessage() . PHP_EOL;
            break;
        default:
            echo "参数:".json_encode($invoiceParams ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
            echo $e->getErrorCode() . " " . $e->getMessage() . PHP_EOL;
            break;
    }
} catch (\Exception $e) {
    echo "系统错误: " . $e->getMessage() . PHP_EOL;
}

