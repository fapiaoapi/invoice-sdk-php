<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tax\Invoice\Utils;



/**
 * 含税金额计算示例
 *
 *   不含税单价 = 含税单价/(1+ 税率)  noTaxDj = dj / (1 + sl)
 *   不含税金额 = 不含税单价*数量  noTaxJe = noTaxDj * spsl
 *   含税金额 = 含税单价*数量  je = dj * spsl
 *   税额 = 税额 = 1 / (1 + 税率) * 税率 * 含税金额  se = 1  / (1 + sl) * sl * je
 *    hjse= se1 + se2 + ... + seN
 *    jshj= je1 + je2 + ... + jeN
 *   价税合计 =合计金额+合计税额 jshj = hjje + hjse
 *
 */


/**
 * 含税计算示例1  无价格  无数量
 * @link https://fa-piao.com/fapiao.html?action=data1&source=github
 *
 */

$hsbz = 1; // 含税标志，0不含税，1含税
$amount = 200;
$sl = 0.01;
$se = Utils::calculateTax($amount, $sl, (bool)$hsbz);
$data = [
    "hjje" => 0,
    "hjse" => 0,
    "jshj" => 0,
    "fyxm" => [
        [
            "fphxz" => 0,
            "hsbz" => $hsbz,
            "spmc" => "*软件维护服务*接口服务费",
            "spbm" => "3040201030000000000",
            "je" => $amount,
            "sl" => $sl,
            "se" => $se,
        ]
    ]
];

foreach ($data['fyxm'] as $item) {
    $data['jshj'] = bcadd($data['jshj'], $item['je'], 2);
    $data['hjse'] = bcadd($data['hjse'], $item['se'], 2);
}
$data['hjje'] = bcsub($data['jshj'], $data['hjse'], 2);

$data['hjje'] = floatval($data['hjje']);
$data['hjse'] = floatval($data['hjse']);
$data['jshj'] = floatval($data['jshj']);
echo '含税计算示例1  无价格  无数量: ' . PHP_EOL;
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo '---------------------------------------------' . PHP_EOL;


/**
 * 含税计算示例2  有价格 有数量
 * @link https://fa-piao.com/fapiao.html?action=data3&source=github
 *
 */

$hsbz = 1; // 含税标志，0不含税，1含税
$spsl = 1;
$dj = 2;
$sl = 0.03;

$spsl2 = 1;
$dj2 = 3;
$sl2 = 0.01;

$je = bcmul($dj, $spsl, 2);
$se = Utils::calculateTax($je, $sl, (bool)$hsbz);

$je2 = bcmul($dj2, $spsl2, 2);
$se2 = Utils::calculateTax($je2, $sl2, (bool)$hsbz);
$data = [
    "hjje" => 0,
    "hjse" => 0,
    "jshj" => 0,
    "fyxm" => [
        [
            "fphxz" => 0,
            "hsbz" => $hsbz,
            "spmc" => "*水冰雪*一阶水费",
            "spbm" => "1100301030000000000",
            "ggxh" => "",
            "dw" => "吨",
            "dj" => $dj,
            "spsl" => $spsl,
            "je" => floatval($je),
            "sl" => $sl,
            "se" => $se,
        ],
        [
            "fphxz" => 0,
            "hsbz" => $hsbz,
            "spmc" => "*水冰雪*二阶水费",
            "spbm" => "1100301030000000000",
            "ggxh" => "",
            "dw" => "吨",
            "dj" => $dj2,
            "spsl" => $spsl2,
            "je" => floatval($je2),
            "sl" => $sl2,
            "se" => $se2,
        ]
    ]
];

foreach ($data['fyxm'] as $item) {
    $data['jshj'] = bcadd($data['jshj'], $item['je'], 2);
    $data['hjse'] = bcadd($data['hjse'], $item['se'], 2);
}
$data['hjje'] = bcsub($data['jshj'], $data['hjse'], 2);

$data['hjje'] = floatval($data['hjje']);
$data['hjse'] = floatval($data['hjse']);
$data['jshj'] = floatval($data['jshj']);

echo '含税计算示例2  有价格 有数量: ' . PHP_EOL;
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo '---------------------------------------------' . PHP_EOL;



/**
 * 含税计算示例3  有价格自动算数量  购买猪肉1000元,16.8元/斤
 * @link https://fa-piao.com/fapiao.html?action=data5&source=github
 *
 */
$hsbz = 1; // 含税标志，0不含税，1含税
$amount = 1000;
$dj = 16.8;
$sl = 0.01;
$se = Utils::calculateTax($amount, $sl, (bool)$hsbz);

$data = [
    "hjje" => 0,
    "hjse" => 0,
    "jshj" => 0,
    "fyxm" => [
        [
            "fphxz" => 0,
            "hsbz" => $hsbz,
            "spmc" => "*肉类*猪肉",
            "spbm" => "1030107010100000000",
            "ggxh" => "",
            "dw" => "斤",
            "dj" => $dj,
            "spsl" => floatval(bcdiv($amount, $dj, 13)),
            "je" => $amount,
            "sl" => $sl,
            "se" => $se,
        ]
    ]
];
foreach ($data['fyxm'] as $item) {
    $data['jshj'] = bcadd($data['jshj'], $item['je'], 2);
    $data['hjse'] = bcadd($data['hjse'], $item['se'], 2);
}
$data['hjje'] = bcsub($data['jshj'], $data['hjse'], 2);

$data['hjje'] = floatval($data['hjje']);
$data['hjse'] = floatval($data['hjse']);
$data['jshj'] = floatval($data['jshj']);
echo '含税计算示例3  有价格自动算数量 购买猪肉1000元,16.8元/斤: ' . PHP_EOL;
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo '---------------------------------------------' . PHP_EOL;


/**
 * 含税计算示例4  有数量自动算价格  购买接口服务1000元7次
 * @link https://fa-piao.com/fapiao.html?action=data7&source=github
 *
 */

$hsbz = 1; // 含税标志，0不含税，1含税
$amount = 1000;
$spsl = 7;
$sl = 0.01;
$se = Utils::calculateTax($amount, $sl, (bool)$hsbz);

$data = [
    "hjje" => 0,
    "hjse" => 0,
    "jshj" => 0,
    "fyxm" => [
        [
            "fphxz" => 0,
            "hsbz" => $hsbz,
            "spmc" => "*软件维护服务*接口服务费",
            "spbm" => "3040201030000000000",
            "ggxh" => "",
            "dw" => "次",
            "dj" => floatval(bcdiv($amount, $spsl, 13)),
            "spsl" => $spsl,
            "je" => $amount,
            "sl" => $sl,
            "se" => $se,
        ]
    ]
];
foreach ($data['fyxm'] as $item) {
    $data['jshj'] = bcadd($data['jshj'], $item['je'], 2);
    $data['hjse'] = bcadd($data['hjse'], $item['se'], 2);
}
$data['hjje'] = bcsub($data['jshj'], $data['hjse'], 2);

$data['hjje'] = floatval($data['hjje']);
$data['hjse'] = floatval($data['hjse']);
$data['jshj'] = floatval($data['jshj']);
echo '含税计算示例4  有数量自动算价格 购买接口服务1000元7次: ' . PHP_EOL;
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo '---------------------------------------------' . PHP_EOL;



/**
 * 不含税计算示例
 *  金额 = 单价 * 数量  je = dj * spsl
 *  税额 = 金额 * 税率  se = je * sl
 *   hjse= se1 + se2 + ... + seN
 *   hjje= je1 + je2 + ... + jeN
 *  价税合计 =合计金额+合计税额 jshj = hjje + hjse
 *
 */

/**
 *
 * 不含税计算示例1 无价格 无数量
 * @link https://fa-piao.com/fapiao.html?action=data2&source=github
 */

$hsbz = 0; // 含税标志，0不含税，1含税
$amount = 200;
$sl = 0.01;
$se = Utils::calculateTax($amount, $sl, (bool)$hsbz);
$data = [
    "hjje" => 0,
    "hjse" => 0,
    "jshj" => 0,
    "fyxm" => [
        [
            "fphxz" => 0,
            "hsbz" => $hsbz,
            "spmc" => "*软件维护服务*接口服务费",
            "spbm" => "3040201030000000000",
            "je" => $amount,
            "sl" => $sl,
            "se" => $se,
        ]
    ]
];


foreach ($data['fyxm'] as $item) {
    $data['hjje'] = bcadd($data['hjje'], $item['je'], 2);
    $data['hjse'] = bcadd($data['hjse'], $item['se'], 2);
}
$data['jshj'] = bcadd($data['hjje'], $data['hjse'], 2);

$data['hjje'] = floatval($data['hjje']);
$data['hjse'] = floatval($data['hjse']);
$data['jshj'] = floatval($data['jshj']);

echo '不含税计算示例1 无价格 无数量: '.PHP_EOL;
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo '---------------------------------------------' . PHP_EOL;


/**
 *
 * 不含税计算示例2  有价格 有数量
 * @link https://fa-piao.com/fapiao.html?action=data4&source=github
 */
// 一阶水费 1吨，单价2元/吨，税率0.03
// 二阶水费 1吨，单价3元/吨，税率0.01
$hsbz = 0; // 含税标志，0不含税，1含税
$spsl = 1;
$dj = 2;
$sl = 0.03;

$spsl2 = 1;
$dj2 = 3;
$sl2 = 0.01;

$je = bcmul($dj, $spsl, 2);
$se = Utils::calculateTax($je, $sl, (bool)$hsbz);

$je2 = bcmul($dj2, $spsl2, 2);
$se2 = Utils::calculateTax($je2, $sl2, (bool)$hsbz);
$data = [
    "hjje" => 0,
    "hjse" => 0,
    "jshj" => 0,
    "fyxm" => [
        [
            "fphxz" => 0,
            "hsbz" => $hsbz,
            "spmc" => "*水冰雪*一阶水费",
            "spbm" => "1100301030000000000",
            "ggxh" => "",
            "dw" => "吨",
            "dj" => $dj,
            "spsl" => $spsl,
            "je" => floatval($je),
            "sl" => $sl,
            "se" => floatval($se),
        ],
        [
            "fphxz" => 0,
            "hsbz" => $hsbz,
            "spmc" => "*水冰雪*而阶水费",
            "spbm" => "1100301030000000000",
            "ggxh" => "",
            "dw" => "吨",
            "dj" => $dj2,
            "spsl" => $spsl2,
            "je" => floatval($je2),
            "sl" => $sl2,
            "se" => floatval($se2),
        ]
    ]
];

foreach ($data['fyxm'] as $item) {
    $data['hjje'] = bcadd($data['hjje'], $item['je'], 2);
    $data['hjse'] = bcadd($data['hjse'], $item['se'], 2);
}
$data['jshj'] = bcadd($data['hjje'], $data['hjse'], 2);

$data['hjje'] = floatval($data['hjje']);
$data['hjse'] = floatval($data['hjse']);
$data['jshj'] = floatval($data['jshj']);

echo '不含税计算示例2  有价格 有数量: ' . PHP_EOL;
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo '---------------------------------------------' . PHP_EOL;


/**
 * 不含税计算示例3  有价格自动算数量  购买猪肉1000元,16.8元/斤
 * @link https://fa-piao.com/fapiao.html?action=data6&source=github
 *
 */
$hsbz = 0; // 含税标志，0不含税，1含税
$amount = 1000;
$dj = 16.8;
$sl = 0.01;
$se = Utils::calculateTax($amount, $sl, (bool)$hsbz);

$data = [
    "hjje" => 0,
    "hjse" => 0,
    "jshj" => 0,
    "fyxm" => [
        [
            "fphxz" => 0,
            "hsbz" => $hsbz,
            "spmc" => "*肉类*猪肉",
            "spbm" => "1030107010100000000",
            "ggxh" => "",
            "dw" => "斤",
            "dj" => $dj,
            "spsl" => floatval(bcdiv($amount, $dj, 13)),
            "je" => $amount,
            "sl" => $sl,
            "se" => $se,
        ]
    ]
];
foreach ($data['fyxm'] as $item) {
    $data['hjje'] = bcadd($data['hjje'], $item['je'], 2);
    $data['hjse'] = bcadd($data['hjse'], $item['se'], 2);
}
$data['jshj'] = bcadd($data['hjje'], $data['hjse'], 2);
echo '不含税计算示例3  有价格自动算数量 购买猪肉1000元,16.8元/斤: ' . PHP_EOL;
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo '---------------------------------------------' . PHP_EOL;


/**
 * 不含税计算示例4  有数量自动算价格  购买接口服务1000元7次
 *
 * @link https://fa-piao.com/fapiao.html?action=data8&source=github
 *
 */


$hsbz = 0; // 含税标志，0不含税，1含税
$amount = 1000;
$spsl = 7;
$sl = 0.01;
$se = Utils::calculateTax($amount, $sl, (bool)$hsbz);

$data = [
    "hjje" => 0,
    "hjse" => 0,
    "jshj" => 0,
    "fyxm" => [
        [
            "fphxz" => 0,
            "hsbz" => $hsbz,
            "spmc" => "*软件维护服务*接口服务费",
            "spbm" => "1030107010100000000",
            "ggxh" => "",
            "dw" => "次",
            "dj" => floatval(bcdiv($amount, $spsl, 13)),
            "spsl" =>$spsl,
            "je" => $amount,
            "sl" => $sl,
            "se" => $se,
        ]
    ]
];
foreach ($data['fyxm'] as $item) {
    $data['hjje'] = bcadd($data['hjje'], $item['je'], 2);
    $data['hjse'] = bcadd($data['hjse'], $item['se'], 2);
}
$data['jshj'] = bcadd($data['hjje'], $data['hjse'], 2);
echo '不含税计算示例4  有数量自动算价格 购买接口服务1000元7次: ' . PHP_EOL;
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo '---------------------------------------------' . PHP_EOL;

/**
 * 免税计算示例
 *  金额 = 单价 * 数量  je = dj * spsl
 *  税额 = 0
 *  hjse = se1 + se2 + ... + seN
 *  jshj = je1 + je2 + ... + jeN
 *  价税合计 =合计金额+合计税额 jshj = hjje + hjse
 * @link https://fa-piao.com/fapiao.html?action=data9&source=github
 */

$hsbz = 0; // 含税标志，0不含税，1含税
$dj = 32263.98;
$sl = 0;
$se = 0;
$data = [
    "hjje" => 0,
    "hjse" => 0,
    "jshj" => 0,
    "fyxm" => [
        [
            "fphxz" => 0,
            "hsbz" => $hsbz,
            "spmc" => "*经纪代理服务*国际货物运输代理服务",
            "spbm" => "3040802010200000000",
            "ggxh" => "",
            "dw" => "次",
            "spsl" => 1,
            "dj" => $dj,
            "je" => $dj,
            "sl" => $sl,
            "se" => $se,
            "yhzcbs" => 1,
            "lslbs" => 1,
            "zzstsgl" => "免税"
        ]
    ]
];
foreach ($data['fyxm'] as $item) {
    $data['hjje'] = bcadd($data['hjje'], $item['je'], 2);
    $data['hjse'] = bcadd($data['hjse'], $item['se'], 2);
}

$data['jshj'] = bcadd($data['hjje'], $data['hjse'], 2);

echo '免税计算示例: '.json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

?>

