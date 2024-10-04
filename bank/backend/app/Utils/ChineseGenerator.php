<?php

namespace App\Utils;

use Carbon\Carbon;

class ChineseGenerator
{

    public static function withdrawName()
    {
        $avail1 = ['王', '苏', '陈', '黄'];
        $avail2 = ['水', '进', '明', '来', '炳', '坤', '昆', '德', '江'];
        $avail3 = ['平', '南', '水', '福', '荣', '飞', '添', '灿'];

        return $avail1[rand(0, count($avail1) - 1)] . $avail2[rand(0, count($avail2) - 1)] . $avail3[rand(0, count($avail3) - 1)];
    }

    public static function userName()
    {
        $avail1 = ['阿', '大', '小'];
        $avail2 = ['水', '江', '龙', '平', '福', '荣'];

        return $avail1[rand(0, count($avail1) - 1)] . $avail2[rand(0, count($avail2) - 1)];
    }

    public static function phoneNumber()
    {
        $avail = [
            '18063785798',
            '13859838431',
            '14700008138',
            '18960329057',
            '17219781075',
            '15606943573',
            '18965122662',
            '17252992918',
            '18063785798',
            '17346307160',
            '18120906308',
            '19959989363',
            '17226078432',
            '17254937919',
            '13163859782',
            '15959713362',
            '18450169410',
            '13348291718',
            '18859868889',
            '15260635600',
            '13959516021',
            '17185804855',
            '17254204743',
            '13509374130',
            '18649846775',
            '13626932893',
            '18016531148',
            '18859072800',
            '15159095626',
            '18597726279',
            '18850273784',
            '13599846743',
            '15305084157',
            '13459581825',
            '15859005146',
            '13395028418',
            '14759717580',
            '18450247585',
            '17165808950',
            '13559225151',
            '15059228049',
            '15080432613',
            '18396049783',
            '13599399531',
            '16550973162',
            '13799231265',
            '18959947796',
            '18065958667',
        ];

        return $avail[rand(0, count($avail) - 1)];
    }

    public static function withdrawAddress()
    {
        $avail = [
            [
                'address' => '鼓楼区八一七北路268号冠亚广场',
                'country' => '中国',
                'state' => '福建省',
                'city' => '福州市',
                'postcode' => '350000',
            ],
            [
                'address' => '城厢区荔华东大道8号莆田万达广场地上一层A177~A178号铺位',
                'country' => '中国',
                'state' => '福建省',
                'city' => '莆田市',
                'postcode' => '350306',
            ],
            [
                'address' => '新罗区龙岩大道388号万宝广场F1',
                'country' => '中国',
                'state' => '福建省',
                'city' => '龙岩市',
                'postcode' => '342214',
            ],
            [
                'address' => '鲤城区新华北路与城西环路交叉口东南角开元盛世广场的地上1-2层1-001号',
                'country' => '中国',
                'state' => '福建省',
                'city' => '泉州市',
                'postcode' => '350704',
            ],
        ];

        return $avail[rand(0, count($avail) - 1)];
    }

    public static function withdrawAccount()
    {
        $avail = [
            [
                'bankAccountNumber' => '6222028012196535795',
                'address' => '福州市福清市宏路镇福隆购物中心综合楼108~111',
                'bankName' => '中国工商银行(福清宏路支行)',
            ],
            [
                'bankAccountNumber' => '6222024137405537910',
                'address' => '福建省龙岩市新罗区龙川东路18号2楼',
                'bankName' => '中国建设银行(龙岩东城支行)',
            ],
            [
                'bankAccountNumber' => '6222020793875864343',
                'address' => '泉州市安溪县S307安溪第九中学南侧约80米',
                'bankName' => '中国农业银行(安溪金谷支行)',
            ],
            [
                'bankAccountNumber' => '6222022869172942687',
                'address' => '福建省莆田市荔城区延寿中街荔能华景城e区3号楼2楼',
                'bankName' => '招商银行(莆田荔城支行)',
            ],
        ];

        return $avail[rand(0, count($avail) - 1)];
    }

    public static function email()
    {
        $avail = [
            'gmail.com',
            'qq.com',
            '163.com',
        ];
        return Carbon::now()->timestamp . '@' . $avail[rand(0, count($avail) - 1)];
    }
}
