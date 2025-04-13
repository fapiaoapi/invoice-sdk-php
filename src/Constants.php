<?php

namespace Tax\Invoice;

class Constants
{
    /**
     * 发票类型代码
     */
    const FPLXDM_NORMAL = '81'; // 电子发票(增值税普通发票)
    const FPLXDM_SPECIAL = '85'; // 电子发票(增值税专用发票)
    const FPLXDM_ELECTRONIC_NORMAL = '82'; // 电子发票(普通发票)
    const FPLXDM_ELECTRONIC_SPECIAL = '86'; // 电子发票(专用发票)
    
    /**
     * 开票类型
     */
    const KPLX_NORMAL = '0'; // 正数发票
    const KPLX_NEGATIVE = '1'; // 负数发票
    
    /**
     * 征收方式
     */
    const ZSFS_NORMAL = '0'; // 普通征税
    const ZSFS_DIFFERENCE_FULL = '2'; // 差额征税全额开具
    const ZSFS_DIFFERENCE_PART = '3'; // 差额征税差额开具
    
    /**
     * 发票行性质
     */
    const FPHXZ_NORMAL = '0'; // 正常行
    const FPHXZ_DISCOUNT = '1'; // 折扣行
    const FPHXZ_DISCOUNTED = '2'; // 被折扣行
    
    /**
     * 含税标志
     */
    const HSBZ_NO = '0'; // 不含税
    const HSBZ_YES = '1'; // 含税
    
    /**
     * 优惠政策标识
     */
    const YHZCBS_NO = '0'; // 未使用
    const YHZCBS_YES = '1'; // 使用
    
    /**
     * 零税率标识
     */
    const LSLBS_NORMAL = '0'; // 正常税率
    const LSLBS_EXPORT = '1'; // 出口免税和其他免税优惠政策（免税）
    const LSLBS_NO_TAX = '2'; // 不征增值税（不征税）
    const LSLBS_ZERO = '3'; // 普通零税率（0%）
    
    /**
     * 清单标志
     */
    const QDBZ_YES = '1'; // 是清单发票
    
    /**
     * 自然人标识
     */
    const ZRRBS_NO = 'N'; // 企业
    const ZRRBS_YES = 'Y'; // 个人
    
    /**
     * 申请类型
     */
    const SQYY_SELLER_FULL = '2'; // 销方全额红冲申请
    const SQYY_BUYER_FULL = '3'; // 购方全额红冲
    
    /**
     * 获取版式类型
     */
    const DOWNFLAG_PDF = '1'; // PDF
    const DOWNFLAG_OFD = '2'; // OFD
    const DOWNFLAG_XML = '3'; // XML
    const DOWNFLAG_URL = '4'; // 下载地址
    const DOWNFLAG_BASE64 = '5'; // base64文件
    
    /**
     * 电子税务局身份
     */
    const SF_LEGAL = '01'; // 法定代表人
    const SF_FINANCE = '02'; // 财务负责人
    const SF_TAX = '03'; // 办税员
    const SF_ADMIN = '05'; // 管理员
    const SF_SOCIAL = '08'; // 社保经办人
    const SF_INVOICE = '09'; // 开票员
    const SF_SALES = '10'; // 销售人员
    
    /**
     * 二维码类型
     */
    const EWMLX_TAX_FACE = '1'; // 税务人脸二维码登录
    const EWMLX_TAX_APP = '10'; // 税务app扫码登录
    const EWMLX_PERSONAL_FACE = '2'; // 个税人脸二维码登录
    const EWMLX_PERSONAL_APP = '3'; // 个税app扫码确认登录
}