<?php

namespace Tax\Invoice;

class InvoiceFactory
{
    /**
     * 客户端实例
     */
    protected $client;

    /**
     * 构造函数
     *
     * @param Client $client 客户端实例
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * 创建蓝票
     *
     * @param array $data 发票数据
     * @return array
     * @throws \Exception
     */
    public function createBlueInvoice(array $data)
    {
        // 验证必填字段
        $requiredFields = [
            'fplxdm', 'kplx', 'xhdwsbh', 'xhdwmc', 'xhdwdzdh', 'xhdwyhzh', 
            'ghdwmc', 'hjje', 'hjse', 'jshj'
        ];
        
        $this->validateRequiredFields($data, $requiredFields);
        
        // 验证商品明细
        if (!isset($data['fyxm']) || !is_array($data['fyxm']) || empty($data['fyxm'])) {
            throw new \Exception('商品明细不能为空');
        }
        
        foreach ($data['fyxm'] as $item) {
            $itemRequiredFields = ['fphxz', 'spmc', 'je', 'sl', 'se', 'hsbz', 'spbm'];
            $this->validateRequiredFields($item, $itemRequiredFields);
        }
        
        // 如果没有传入fpqqlsh，则自动生成
        if (!isset($data['fpqqlsh']) || empty($data['fpqqlsh'])) {
            $data['fpqqlsh'] = $this->generateInvoiceSerialNumber();
        }
        
        return $this->client->blueTicket($data);
    }

    /**
     * 创建红票
     *
     * @param array $data 发票数据
     * @return array
     * @throws \Exception
     */
    public function createRedInvoice(array $data)
    {
        // 验证必填字段
        $requiredFields = [
            'fplxdm', 'kplx', 'xhdwsbh', 'xhdwmc', 'xhdwdzdh', 'xhdwyhzh', 
            'ghdwmc', 'hjje', 'hjse', 'jshj', 'yfphm', 'hzxxbbh'
        ];
        
        $this->validateRequiredFields($data, $requiredFields);
        
        // 验证商品明细
        if (!isset($data['fyxm']) || !is_array($data['fyxm']) || empty($data['fyxm'])) {
            throw new \Exception('商品明细不能为空');
        }
        
        foreach ($data['fyxm'] as $item) {
            $itemRequiredFields = ['fphxz', 'spmc', 'je', 'sl', 'se', 'hsbz', 'spbm'];
            $this->validateRequiredFields($item, $itemRequiredFields);
        }
        
        // 如果没有传入fpqqlsh，则自动生成
        if (!isset($data['fpqqlsh']) || empty($data['fpqqlsh'])) {
            $data['fpqqlsh'] = $this->generateInvoiceSerialNumber();
        }
        
        return $this->client->redTicket($data);
    }

    /**
     * 申请红字信息表
     *
     * @param array $data 申请数据
     * @return array
     * @throws \Exception
     */
    public function applyRedInfo(array $data)
    {
        // 验证必填字段
        $requiredFields = [
            'nsrsbh', 'yfphm', 'sqyy'
        ];
        
        $this->validateRequiredFields($data, $requiredFields);
        
        return $this->client->applyRedInfo($data);
    }

    /**
     * 获取发票PDF
     *
     * @param string $nsrsbh 纳税人识别号
     * @param string $fphm 发票号码
     * @param string $kprq 开票日期
     * @param string $username 用户名
     * @param bool $addSeal 是否添加签章
     * @return array
     * @throws \Exception
     */
    public function getInvoicePdf(string $nsrsbh, string $fphm, string $kprq = '', string $username = '', bool $addSeal = false)
    {
        return $this->client->getPdfOfdXml(
            $nsrsbh, 
            $fphm, 
            '1', 
            $kprq, 
            $username, 
            $addSeal ? '1' : ''
        );
    }

    /**
     * 获取发票OFD
     *
     * @param string $nsrsbh 纳税人识别号
     * @param string $fphm 发票号码
     * @param string $kprq 开票日期
     * @param string $username 用户名
     * @param bool $addSeal 是否添加签章
     * @return array
     * @throws \Exception
     */
    public function getInvoiceOfd(string $nsrsbh, string $fphm, string $kprq = '', string $username = '', bool $addSeal = false)
    {
        return $this->client->getPdfOfdXml(
            $nsrsbh, 
            $fphm, 
            '2', 
            $kprq, 
            $username, 
            $addSeal ? '1' : ''
        );
    }

    /**
     * 获取发票XML
     *
     * @param string $nsrsbh 纳税人识别号
     * @param string $fphm 发票号码
     * @param string $kprq 开票日期
     * @param string $username 用户名
     * @return array
     * @throws \Exception
     */
    public function getInvoiceXml(string $nsrsbh, string $fphm, string $kprq = '', string $username = '')
    {
        return $this->client->getPdfOfdXml(
            $nsrsbh, 
            $fphm, 
            '3', 
            $kprq, 
            $username
        );
    }

    /**
     * 获取发票下载链接
     *
     * @param string $nsrsbh 纳税人识别号
     * @param string $fphm 发票号码
     * @param string $kprq 开票日期
     * @param string $username 用户名
     * @return array
     * @throws \Exception
     */
    public function getInvoiceDownloadUrls(string $nsrsbh, string $fphm, string $kprq = '', string $username = '')
    {
        return $this->client->getPdfOfdXml(
            $nsrsbh, 
            $fphm, 
            '4', 
            $kprq, 
            $username
        );
    }

    /**
     * 验证必填字段
     *
     * @param array $data 数据
     * @param array $requiredFields 必填字段
     * @throws \Exception
     */
    protected function validateRequiredFields(array $data, array $requiredFields)
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                throw new \Exception("字段 {$field} 不能为空");
            }
        }
    }

    /**
     * 生成发票请求流水号
     *
     * @return string
     */
    protected function generateInvoiceSerialNumber(): string
    {
        $prefix = 'FP';
        $timestamp = date('YmdHis');
        $random = mt_rand(1000, 9999);
        
        return $prefix . $timestamp . $random;
    }
}