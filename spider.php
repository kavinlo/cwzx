<?php
include './vendor/autoload.php';
use QL\QueryList;

$html = file_get_contents("./cw.html");

$type = $_GET['type'] ?: '7' ;// 
// 有限读取缓存
$dsn = "mysql:host=localhost;dbname=cwzx";
$db = new PDO($dsn, 'root', '123');
$today = date("Y-m-d")."--".$type;
$sql = "select * from other_set where name='{$today}' limit 1";
$rs =$db->query($sql);
$cache =  $rs->fetchAll();
$json = unserialize(gzuncompress(base64_decode($cache[0]['json'])));
if(count($json)){   // 读取缓存
    $number = count($json);
    echo json_encode(["code"=>1,"msg"=>"一共获取到啦-{$number}-数据","data"=>$json]);
    die;
}

// 只读缓存
die;









$timestamp = strtotime($last_day);// 查询最近多少天有更新的数据

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

$result = []; // 最后的结果
foreach($list as $name=>$link){
    $link = "cwcx.asp?gdmc=%D6%A4%C8%AF";
    $url = $base_url . $link;
    $html = file_get_contents($url);
    // 手动获取页面
    $table  = QueryList::html($html)->find(".tb0td1:eq(1)");
    // 采集表的每行内容
    $tableRows = $table->find('tr:gt(0)')->map(function($row){
        return $row->find('td')->texts()->all();
    });

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
        if($timestamp <= $tmp_timestamp){
            $gp_name = explode(" ",$info['2'])[0];
            $result[$name][] = [
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
            break;
        }
    }
break;
}

$number = count($result);
echo json_encode(["code"=>1,"msg"=>"一共获取到啦-{$number}-数据","data"=>$result]);