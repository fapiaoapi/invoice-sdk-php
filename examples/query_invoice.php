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
$fphm = '12345678'; // 发票号码

try {
    // 创建客户端
    $client = new Client($appKey, $appSecret);
    
    // 获取授权
    echo "正在获取授权...\n";
    $authResult = $client->getAuthorization($nsrsbh);
    if ($authResult['code'] == 200) {
        $token = $authResult['data']['token'];
        $client->setToken($token);
        echo "授权成功\n";
    } else {
        echo "授权失败: " . $authResult['msg'] . "\n";
        exit;
    }
    
    // 创建发票工厂
    $invoiceFactory = new InvoiceFactory($client);
    
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
    
    // 获取发票OFD
    echo "正在获取发票OFD...\n";
    $ofdResult = $invoiceFactory->getInvoiceOfd($nsrsbh, $fphm);
    if ($ofdResult['code'] == 200) {
        $ofdBase64 = $ofdResult['data']['ofd'];
        $ofdPath = __DIR__ . '/invoice_' . $fphm . '.ofd';
        if (Utils::saveBase64File($ofdBase64, $ofdPath)) {
            echo "发票OFD已保存为: " . $ofdPath . "\n";
        } else {
            echo "保存发票OFD失败\n";
        }
    } else {
        echo "获取发票OFD失败: " . $ofdResult['msg'] . "\n";
    }
    
    // 获取发票XML
    echo "正在获取发票XML...\n";
    $xmlResult = $invoiceFactory->getInvoiceXml($nsrsbh, $fphm);
    if ($xmlResult['code'] == 200) {
        $xmlBase64 = $xmlResult['data']['xml'];
        $xmlPath = __DIR__ . '/invoice_' . $fphm . '.xml';
        if (Utils::saveBase64File($xmlBase64, $xmlPath)) {
            echo "发票XML已保存为: " . $xmlPath . "\n";
        } else {
            echo "保存发票XML失败\n";
        }
    } else {
        echo "获取发票XML失败: " . $xmlResult['msg'] . "\n";
    }
    
    // 获取发票下载链接
    echo "正在获取发票下载链接...\n";
    $urlResult = $invoiceFactory->getInvoiceDownloadUrls($nsrsbh, $fphm);
    if ($urlResult['code'] == 200) {
        echo "发票下载链接:\n";
        echo "PDF: " . $urlResult['data']['pdfUrl'] . "\n";
        echo "OFD: " . $urlResult['data']['ofdUrl'] . "\n";
        echo "XML: " . $urlResult['data']['xmlUrl'] . "\n";
    } else {
        echo "获取发票下载链接失败: " . $urlResult['msg'] . "\n";
    }
} catch (InvoiceException $e) {
    echo "错误: [" . $e->getErrorCode() . "] " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "系统错误: " . $e->getMessage() . "\n";
}