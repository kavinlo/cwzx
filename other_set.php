<?php

include __DIR__.'/vendor/autoload.php';
use QL\QueryList;
$html = file_get_contents(__DIR__."/cw.html");
// $html = file_get_contents('http://cwzx.shdjt.com/top500.asp');
$type_1 = date("Y-m-d",strtotime("-1 day"));
$type_7 = date("Y-m-d",strtotime("-7 day"));
$type_15 = date("Y-m-d",strtotime("-15 day"));
$type_30 = date("Y-m-d",strtotime("-30 day"));

$timestamp_1 = strtotime($type_1);// 查询最近多少天有更新的数据
$timestamp_7 = strtotime($type_7);// 查询最近多少天有更新的数据
$timestamp_15 = strtotime($type_15);// 查询最近多少天有更新的数据
$timestamp_30 = strtotime($type_30);// 查询最近多少天有更新的数据
// 第一部分  ： 15天内没有更新
$rules_one = [
    'link' => ['.ak','href'],
    'title' => ['.ak','title']
];
$ql = QueryList::html($html)
    ->rules($rules_one)
    ->range("tr>.tdlefttop")
    ->query();
$data = $ql->getData();
$list = [];
foreach($data->all() as $info){
    if($info['link'] && $info['title']){
        $list[$info['title']] = $info['link'];
    }
}

// 第二部分  ： 15天有更新
$rules_two = [
    'link' => ['.akred','href'],
    'title' => ['.akred','title']
];
$ql = QueryList::html($html)
    ->rules($rules_two)
    ->range("tr>.tdlefttop")
    ->query();
$data = $ql->getData();
foreach($data->all() as $info){
    if($info['link'] && $info['title']){
        $list[$info['title']] = $info['link'];
    }
}

// 获取到需要爬取的列表 $list,开始爬取需要的数据
$rules = [
    'link' => ['.ak','href'],
    'title' => ['.ak','title']
];
$base_url = "http://cwzx.shdjt.com/";

$result_1 = []; // 最后的结果
$result_7 = []; // 最后的结果
$result_15 = []; // 最后的结果
$result_30 = []; // 最后的结果
foreach($list as $name=>$link){
    // $link = "cwcx.asp?gdmc=%D6%A4%C8%AF";
    $url = $base_url . $link;
    echo $url.PHP_EOL;die;


    // 定义采集规则
    $ql = QueryList::get($url);
    // 采集文章标题
    $table_html = $ql->find('.tb0td1:last-child')->html();
    $table_html = '<table>' . $table_html . '</table>';
    $table_html = iconv("gb2312", "utf-8//IGNORE",$table_html);
    $table  = QueryList::html($table_html)->find("table");
    // 采集表的每行内容
    $tableRows = $table->find('tr:gt(0)')->map(function($row){
	    return $row->find('td')->texts()->all();
    });

	print_r($tableRows->all());die;

//     <td width="20">序</td>
//     <td width="50">股票<br>代码</td>
//     <td width="220">股票名称</td>
//     <td width="20">原价</td>
// <td width="20">现价</td>
// <td width="50">区间<br>涨幅</td>
//     <td  width="80">股东类型</td>
//     <td width="78">更新日期</td>
//     <td>股东名称</td>
//     <td width="50">持股数(万)</td>
//     <td width="50">市值(亿)</td>
//     <td width="20">类型</td>
//     <td width="30">比例<br>(%)</td>
//     <td width="80">类型</td>
//     <td width="30">增减仓</td>
//     <td width="50">数量<br>(万股)</td></tr>
    foreach($tableRows->all() as $info){
     
        $tmp_timestamp = strtotime($info[7]);
        if($timestamp_1 <= $tmp_timestamp){
            $gp_name = explode(" ",$info['2'])[0];
            $result_1[$name][] = [
                "daima" =>$info['1'],    // 代码
                "gp_name" =>$gp_name,     // 名称
                "yj" =>$info['3'],       // 原价
                "xj" =>$info['4'],       // 现价
                "qjzf" =>$info['5'],       // 区间涨幅
                "date" =>$info['7'],       // 更新日期
                "gd_name" =>$info['8'],       // 股东名称
                "num" =>$info['9'],       // 持股数量
                "sz" =>$info['10'],       // 市值
                "bl" =>$info['12'],       // 比例
                "type" =>$info['13'],       // 股票类型： 流通A股还是限售
                "zjc" =>$info['14'],       // 增减仓
                "zj_num" =>$info['14'],       // 增减仓数量
                "link" =>"<a href='".$url."'  target='_blank'>点击跳转</a>",       //  链接
            ];      
        }

        if($timestamp_7 <= $tmp_timestamp){
            $gp_name = explode(" ",$info['2'])[0];
            $result_7[$name][] = [
                "daima" =>$info['1'],    // 代码
                "gp_name" =>$gp_name,     // 名称
                "yj" =>$info['3'],       // 原价
                "xj" =>$info['4'],       // 现价
                "qjzf" =>$info['5'],       // 区间涨幅
                "date" =>$info['7'],       // 更新日期
                "gd_name" =>$info['8'],       // 股东名称
                "num" =>$info['9'],       // 持股数量
                "sz" =>$info['10'],       // 市值
                "bl" =>$info['12'],       // 比例
                "type" =>$info['13'],       // 股票类型： 流通A股还是限售
                "zjc" =>$info['14'],       // 增减仓
                "zj_num" =>$info['14'],       // 增减仓数量
                "link" =>"<a href='".$url."'  target='_blank'>点击跳转</a>",       //  链接
            ];      
        }

        if($timestamp_15 <= $tmp_timestamp){
            $gp_name = explode(" ",$info['2'])[0];
            $result_15[$name][] = [
                "daima" =>$info['1'],    // 代码
                "gp_name" =>$gp_name,     // 名称
                "yj" =>$info['3'],       // 原价
                "xj" =>$info['4'],       // 现价
                "qjzf" =>$info['5'],       // 区间涨幅
                "date" =>$info['7'],       // 更新日期
                "gd_name" =>$info['8'],       // 股东名称
                "num" =>$info['9'],       // 持股数量
                "sz" =>$info['10'],       // 市值
                "bl" =>$info['12'],       // 比例
                "type" =>$info['13'],       // 股票类型： 流通A股还是限售
                "zjc" =>$info['14'],       // 增减仓
                "zj_num" =>$info['14'],       // 增减仓数量
                "link" =>"<a href='".$url."'  target='_blank'>点击跳转</a>",       //  链接
            ];      
        }


        if($timestamp_30 <= $tmp_timestamp){
            $gp_name = explode(" ",$info['2'])[0];
            $result_30[$name][] = [
                "daima" =>$info['1'],    // 代码
                "gp_name" =>$gp_name,     // 名称
                "yj" =>$info['3'],       // 原价
                "xj" =>$info['4'],       // 现价
                "qjzf" =>$info['5'],       // 区间涨幅
                "date" =>$info['7'],       // 更新日期
                "gd_name" =>$info['8'],       // 股东名称
                "num" =>$info['9'],       // 持股数量
                "sz" =>$info['10'],       // 市值
                "bl" =>$info['12'],       // 比例
                "type" =>$info['13'],       // 股票类型： 流通A股还是限售
                "zjc" =>$info['14'],       // 增减仓
                "zj_num" =>$info['14'],       // 增减仓数量
                "link" =>"<a href='".$url."'  target='_blank'>点击跳转</a>",       //  链接
            ];      
        }else{
            // print_r($result_1);
            // print_r($result_7);
            // die;
            break;
        }
    }

// break;
}

$result_1 = base64_encode(gzcompress(serialize($result_1)));
$result_7 = base64_encode(gzcompress(serialize($result_7)));
$result_15 = base64_encode(gzcompress(serialize($result_15)));
$result_30 = base64_encode(gzcompress(serialize($result_30)));
$date = date("Y-m-d");

// 连接数据库
$dsn = "mysql:host=localhost;dbname=cwzx";
$db = new PDO($dsn, 'root', '123');
// 近一天
$ret = $date . "--1";
$sql = "replace into other_set set name='{$ret}',json='{$result_1}'";
$count = $db->exec($sql);
// 近7天
$ret = $date . "--7";
$sql = "replace into other_set set name='{$ret}',json='{$result_7}'";
$count = $db->exec($sql);
// 近15天
$ret = $date . "--15";
$sql = "replace into other_set set name='{$ret}',json='{$result_15}'";
$count = $db->exec($sql);
// 近30天
$ret = $date . "--30";
$sql = "replace into other_set set name='{$ret}',json='{$result_30}'";
$count = $db->exec($sql);
$title = date('Y-m-d H:i:s')." ---- ";
if($count){
    echo "\r\n {$title} 缓存完成 \r\n";
}else {
    echo "\r\n {$title} 缓存失败  \r\n";
}
