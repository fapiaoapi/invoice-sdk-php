<?php

namespace Tax\Invoice;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Tax\Invoice\Exception\InvoiceException;

class Client
{
    /**
     * API 基础URL
     */
    protected $baseUrl = 'https://api.fa-piao.com';

    /**
     * AppKey
     */
    protected $appKey;

    /**
     * AppSecret
     */
    protected $appSecret;

    /**
     * 授权Token
     */
    protected $token;

    /**
     * HTTP客户端
     */
    protected $httpClient;

    /**
     * 构造函数
     *
     * @param string $appKey AppKey
     * @param string $appSecret AppSecret
     * @param string $baseUrl api网址 (可选)
     * @param string $token 授权Token (可选)
     */
    public function __construct(string $appKey, string $appSecret,string $baseUrl = "",string $token = '')
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->token = $token;
        if(!empty($baseUrl)){
            $this->baseUrl = $baseUrl;
        }
        $this->httpClient = new HttpClient([
            'base_uri' => $this->baseUrl ,
            'timeout' => 30,
            'verify' => false,
        ]);
    }

    /**
     * 设置授权Token
     *
     * @param string $token 授权Token
     * @return $this
     */
    public function setToken(string $token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * 获取授权
     *
     * @param string $nsrsbh 纳税人识别号
     * @return array
     * @throws InvoiceException
     */
    public function getAuthorization(string $nsrsbh)
    {
        $params = [
            'nsrsbh' => $nsrsbh,
        ];

        return $this->request('/v5/enterprise/authorization', $params);
    }

    /**
     * 登录数电发票平台
     *
     * @param string $nsrsbh 纳税人识别号
     * @param string $username 用户电票平台账号
     * @param string $password 用户电票平台密码
     * @param string $sms 验证码
     * @param string $sf 电子税务局身份
     * @param string $ewmlx 二维码类型
     * @param string $ewmid 二维码ID
     * @return array
     * @throws InvoiceException
     */
    public function loginDppt(string $nsrsbh, string $username, string $password, string $sms = '', string $sf = '', string $ewmlx = '', string $ewmid = '')
    {
        $params = [
            'nsrsbh' => $nsrsbh,
            'username' => $username,
            'password' => $password,
        ];

        if (!empty($sms)) {
            $params['sms'] = $sms;
        }

        if (!empty($sf)) {
            $params['sf'] = $sf;
        }

        if (!empty($ewmlx)) {
            $params['ewmlx'] = $ewmlx;
        }

        if (!empty($ewmid)) {
            $params['ewmid'] = $ewmid;
        }

        return $this->request('/v5/enterprise/loginDppt', $params);
    }

    /**
     * 获取人脸二维码
     *
     * @param string $nsrsbh 纳税人识别号
     * @param string $username 用户电票平台账号
     * @param string $type 类型
     * @return array
     * @throws InvoiceException
     */
    public function getFaceImg(string $nsrsbh, string $username = '', string $type = '')
    {
        $params = [
            'nsrsbh' => $nsrsbh,
        ];

        if (!empty($username)) {
            $params['username'] = $username;
        }

        if (!empty($type)) {
            $params['type'] = $type;
        }

        return $this->request('/v5/enterprise/getFaceImg', $params, 'GET');
    }

    /**
     * 获取人脸二维码认证状态
     *
     * @param string $nsrsbh 纳税人识别号
     * @param string $rzid 认证id
     * @param string $username 用户电票平台账号
     * @param string $type 类型
     * @return array
     * @throws InvoiceException
     */
    public function getFaceState(string $nsrsbh, string $rzid, string $username = '', string $type = '')
    {
        $params = [
            'nsrsbh' => $nsrsbh,
            'rzid' => $rzid,
        ];

        if (!empty($username)) {
            $params['username'] = $username;
        }

        if (!empty($type)) {
            $params['type'] = $type;
        }

        return $this->request('/v5/enterprise/getFaceState', $params, 'GET');
    }

    /**
     * 获取认证状态
     *
     * @param string $nsrsbh 纳税人识别号
     * @param string $username 用户电票平台账号
     * @return array
     * @throws InvoiceException
     */
    public function queryFaceAuthState(string $nsrsbh, string $username = '')
    {
        $params = [
            'nsrsbh' => $nsrsbh,
        ];

        if (!empty($username)) {
            $params['username'] = $username;
        }

        return $this->request('/v5/enterprise/queryFaceAuthState', $params);
    }

    /**
     * 数电蓝票开具接口
     *
     * @param array $params 开票参数
     * @return array
     * @throws InvoiceException
     */
    public function blueTicket(array $params)
    {
        return $this->request('/v5/enterprise/blueTicket', $params);
    }

    /**
     * 获取销项数电版式文件
     *
     * @param string $nsrsbh 纳税人识别号
     * @param string $fphm 发票号码
     * @param string $downflag 获取版式类型
     * @param string $kprq 开票日期
     * @param string $username 用户电票平台账号
     * @param string $addSeal 是否添加签章
     * @return array
     * @throws InvoiceException
     */
    public function getPdfOfdXml(string $nsrsbh, string $fphm, string $downflag, string $kprq = '', string $username = '', string $addSeal = '')
    {
        $params = [
            'nsrsbh' => $nsrsbh,
            'fphm' => $fphm,
            'downflag' => $downflag,
        ];

        if (!empty($kprq)) {
            $params['kprq'] = $kprq;
        }

        if (!empty($username)) {
            $params['username'] = $username;
        }

        if (!empty($addSeal)) {
            $params['addSeal'] = $addSeal;
        }

        return $this->request('/v5/enterprise/pdfOfdXml', $params);
    }

    /**
     * 数电申请红字前查蓝票信息接口
     *
     * @param string $nsrsbh 数电企业税号
     * @param string $fphm 发票号码
     * @param string $sqyy 申请类型
     * @param string $username 用户电票平台账号
     * @param string $xhdwsbh 销方税号
     * @return array
     * @throws InvoiceException
     */
    public function retInviceMsg(string $nsrsbh, string $fphm, string $sqyy, string $username = '', string $xhdwsbh = '')
    {
        $params = [
            'nsrsbh' => $nsrsbh,
            'fphm' => $fphm,
            'sqyy' => $sqyy,
        ];

        if (!empty($username)) {
            $params['username'] = $username;
        }

        if (!empty($xhdwsbh)) {
            $params['xhdwsbh'] = $xhdwsbh;
        }

        return $this->request('/v5/enterprise/retMsg', $params);
    }

    /**
     * 申请红字信息表
     *
     * @param array $params 申请参数
     * @return array
     * @throws InvoiceException
     */
    public function applyRedInfo(array $params)
    {
        return $this->request('/v5/enterprise/hzxxbsq', $params);
    }

    /**
     * 数电票开负数发票
     *
     * @param array $params 开票参数
     * @return array
     * @throws InvoiceException
     */
    public function redTicket(array $params)
    {
        return $this->request('/v5/enterprise/hzfpkj', $params);
    }

    /**
     * 切换电子税务局账号
     *
     * @param string $nsrsbh 纳税人识别号
     * @param string $username 用户电票平台账号
     * @param string $sf 电子税务局身份
     * @return array
     * @throws InvoiceException
     */
    public function switchAccount(string $nsrsbh, string $username = '', string $sf = '')
    {
        $params = [
            'nsrsbh' => $nsrsbh,
        ];

        if (!empty($username)) {
            $params['username'] = $username;
        }

        if (!empty($sf)) {
            $params['sf'] = $sf;
        }

        return $this->request('/v5/enterprise/switchAccount', $params);
    }

    /**
     * 授信额度查询
     *
     * @param string $nsrsbh 纳税人识别号
     * @param string $username 用户电票平台账号
     * @return array
     * @throws InvoiceException
     */
    public function queryCredit(string $nsrsbh, string $username = '')
    {
        $params = [
            'nsrsbh' => $nsrsbh,
        ];

        if (!empty($username)) {
            $params['username'] = $username;
        }

        return $this->request('/v5/enterprise/creditLine', $params);
    }

    /**
     * 发送请求
     *
     * @param string $endpoint 接口地址
     * @param array $params 请求参数
     * @param string $method 请求方法
     * @return array
     * @throws InvoiceException
     */
    protected function request(string $endpoint, array $params = [], string $method = 'POST'): array
    {
        $timestamp = time();
        $randomString = SignUtil::generateRandomString(20);
        
        $headers = [
            'AppKey' => $this->appKey,
            'TimeStamp' => (string)$timestamp,
            'RandomString' => $randomString,
            'Sign' => SignUtil::generateSign($method,$endpoint,$randomString,$timestamp,$this->appKey, $this->appSecret ),
        ];

        if (!empty($this->token)) {
            $headers['Authorization'] = $this->token;
        }

        try {
            $options = [
                'headers' => $headers,
            ];

            if ($method === 'GET') {
                $options['query'] = $params;
            } else {
                $options['form_params'] = $params;
            }

            $response = $this->httpClient->request($method, $endpoint, $options);
            $content = $response->getBody()->getContents();
            $result = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw InvoiceException::apiError(999, '响应不是有效的JSON: ' . $content);
            }

            if (!isset($result['code'])) {
                throw InvoiceException::apiError(999, '响应缺少code字段: ' . $content);
            }

            if ($result['code'] !== 200) {
                if ($result['code'] === 420) {
                    throw InvoiceException::needSms($result['msg']);
                }else if ($result['code'] === 430) {
                    throw InvoiceException::needFace($result['msg']);
                }else{
                    $message = isset($result['msg']) ? $result['msg'] : '未知错误';
                    throw InvoiceException::apiError($result['code'], $message);
                }
            }

            return $result;
        } catch (GuzzleException $e) {
            throw InvoiceException::networkError($e->getMessage());
        }
    }
}
