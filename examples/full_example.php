<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tax\Invoice\Client;
use Tax\Invoice\InvoiceFactory;
use Tax\Invoice\Constants;
use Tax\Invoice\Exception\InvoiceException;
use Tax\Invoice\Utils;

// 配置信息
$appKey = 'your_app_key';
$appSecret = 'your_app_secret';
$nsrsbh = '915101820724315989'; // 纳税人识别号
$username = 'your_username';
$password = 'your_password';

try {
    // 创建客户端
    $client = new Client($appKey, $appSecret);
    
    // 获取授权
    echo "正在获取授权...\n";
    $authResult = $client->getAuthorization($nsrsbh);
    if ($authResult['code'] == 200) {
        $token = $authResult['data']['token'];
        $client->setToken($token);
        echo "授权成功，Token: " . $token . "\n";
    } else {
        echo "授权失败: " . $authResult['msg'] . "\n";
        exit;
    }
    
    // 登录数电发票平台
    echo "正在登录数电发票平台...\n";
    $loginResult = $client->loginDppt($nsrsbh, $username, $password);
    
    // 如果需要验证码
    if ($loginResult['code'] == 310) {
        echo "请输入验证码: ";
        $sms = trim(fgets(STDIN));
        $loginResult = $client->loginDppt($nsrsbh, $username, $password, $sms);
    }
    
    if ($loginResult['code'] == 200) {
        echo "登录成功\n";
    } else {
        echo "登录失败: " . $loginResult['msg'] . "\n";
        exit;
    }
    // 如果需要人脸验证
    $loginResult = $client->queryFaceAuthState($nsrsbh, $username);
    if ($loginResult['code'] == 430) {
        // 获取人脸二维码 再用税务app或者个税app扫描二维码完成人脸验证
        echo "请先完成人脸验证(通过税务app或者个税app)\n";
        exit;
    }
    
    
    // 创建发票工厂
    $invoiceFactory = new InvoiceFactory($client);
    
    // 开具蓝票示例
    echo "正在开具蓝票...\n";
    $blueInvoiceData = [
        'fplxdm' => Constants::FPLXDM_NORMAL, // 发票类型代码
        'kplx' => Constants::KPLX_NORMAL, // 开票类型
        'xhdwsbh' => $nsrsbh, // 销方识别号
        'xhdwmc' => '测试企业', // 销方名称
        'xhdwdzdh' => '测试地址 12345678', // 销方地址电话
        'xhdwyhzh' => '测试银行 123456789', // 销方银行账户
        'ghdwsbh' => '91510113MA6739XPX2', // 购方税号
        'ghdwmc' => '购方企业', // 购方名称
        'ghdwdzdh' => '购方地址 87654321', // 购方地址电话
        'ghdwyhzh' => '购方银行 987654321', // 购方银行账号
        'zsfs' => Constants::ZSFS_NORMAL, // 征收方式
        'fyxm' => [
            [
                'fphxz' => Constants::FPHXZ_NORMAL, // 发票行性质
                'spmc' => '测试商品', // 商品名称
                'ggxh' => 'TEST001', // 规格型号
                'dw' => '个', // 单位
                'spsl' => '1', // 商品数量
                'dj' => '100', // 单价
                'je' => '100', // 金额
                'sl' => '0.13', // 税率
                'se' => '13', // 税额
                'hsbz' => Constants::HSBZ_NO, // 含税标志
                'spbm' => '1090413010300000000', // 商品编码
            ]
        ],
        'hjje' => '100', // 合计金额
        'hjse' => '13', // 合计税额
        'jshj' => '113', // 价税合计
        'bz' => '测试备注', // 备注
        'fpqqlsh' => Utils::generateInvoiceSerialNumber(), // 发票请求流水号
    ];
    
    $blueResult = $invoiceFactory->createBlueInvoice($blueInvoiceData);
    if ($blueResult['code'] == 200) {
        $fphm = $blueResult['data']['fphm'];
        echo "开具蓝票成功，发票号码: " . $fphm . "\n";
        
        // 获取发票PDF
        echo "正在获取发票PDF...\n";
        $pdfResult = $invoiceFactory->getInvoicePdf($nsrsbh, $fphm);
        if ($pdfResult['code'] == 200) {
            $pdfBase64 = $pdfResult['data']['pdf'];
            $pdfPath = __DIR__ . '/invoice_' . $fphm . '.pdf';
            if (Utils::saveBase64File($pdfBase64, $pdfPath)) {
                echo "发票PDF已保存为: " . $pdfPath . "\n";
            } else {
                echo "保存发票PDF失败\n";
            }
        } else {
            echo "获取发票PDF失败: " . $pdfResult['msg'] . "\n";
        }
        
        // 申请红字信息表
        echo "正在申请红字信息表...\n";
        $redInfoData = [
            'nsrsbh' => $nsrsbh,
            'yfphm' => $fphm,
            'sqyy' => Constants::SQYY_SELLER_FULL,
        ];
        
        $redInfoResult = $invoiceFactory->applyRedInfo($redInfoData);
        if ($redInfoResult['code'] == 200) {
            $hzxxbbh = $redInfoResult['data']['hzxxbbh'];
            echo "申请红字信息表成功，红字信息表编号: " . $hzxxbbh . "\n";
            
            // 开具红票
            echo "正在开具红票...\n";
            $redInvoiceData = $blueInvoiceData;
            $redInvoiceData['kplx'] = Constants::KPLX_NEGATIVE;
            $redInvoiceData['yfphm'] = $fphm;
            $redInvoiceData['hzxxbbh'] = $hzxxbbh;
            $redInvoiceData['fpqqlsh'] = Utils::generateInvoiceSerialNumber();
            
            $redResult = $invoiceFactory->createRedInvoice($redInvoiceData);
            if ($redResult['code'] == 200) {
                $redFphm = $redResult['data']['fphm'];
                echo "开具红票成功，发票号码: " . $redFphm . "\n";
                
                // 获取红票PDF
                echo "正在获取红票PDF...\n";
                $redPdfResult = $invoiceFactory->getInvoicePdf($nsrsbh, $redFphm);
                if ($redPdfResult['code'] == 200) {
                    $redPdfBase64 = $redPdfResult['data']['pdf'];
                    $redPdfPath = __DIR__ . '/red_invoice_' . $redFphm . '.pdf';
                    if (Utils::saveBase64File($redPdfBase64, $redPdfPath)) {
                        echo "红票PDF已保存为: " . $redPdfPath . "\n";
                    } else {
                        echo "保存红票PDF失败\n";
                    }
                } else {
                    echo "获取红票PDF失败: " . $redPdfResult['msg'] . "\n";
                }
            } else {
                echo "开具红票失败: " . $redResult['msg'] . "\n";
            }
        } else {
            echo "申请红字信息表失败: " . $redInfoResult['msg'] . "\n";
        }
    } else {
        echo "开具蓝票失败: " . $blueResult['msg'] . "\n";
    }
} catch (InvoiceException $e) {
    echo "错误: [" . $e->getErrorCode() . "] " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "系统错误: " . $e->getMessage() . "\n";
}