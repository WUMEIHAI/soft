<?php
// 连接到 SQLite 数据库
$db = new SQLite3('soft.sqlite');
date_default_timezone_set('Asia/Shanghai');
// 处理按钮点击事件
if ($_GET['action']== "click" && isset($_GET['soft_name'])) {
    $softName = $_GET['soft_name'];

    // 获取当前点击次数
    $query = 'SELECT click_count FROM download_stats WHERE soft_name = :soft_name';
    $statement = $db->prepare($query);
    $statement->bindValue(':soft_name', $softName);
    $result = $statement->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result) {
        // 增加点击次数
        $newCount = $result['click_count'] + 1;

        // 更新数据库中的记录
        $query = 'UPDATE download_stats SET click_count = :click_count WHERE soft_name = :soft_name';
        $statement = $db->prepare($query);
        $statement->bindValue(':click_count', $newCount);
        $statement->bindValue(':soft_name', $softName);
        $statement->execute();
    } else {
        // 如果按钮记录不存在，则创建新记录并设置点击次数为 1
        $query = 'INSERT INTO download_stats (soft_name, click_count) VALUES (:soft_name, 1)';
        $statement = $db->prepare($query);
        $statement->bindValue(':soft_name', $softName);
        $statement->execute();
    }
    //记录点击ip
    // 获取用户IP地址
    $user_ip = getRealIpAddr();
    $stmt = $db->prepare('INSERT INTO download_clicks (soft_name, user_ip, click_time) VALUES (:soft_name, :user_ip, datetime("now", "localtime"))');
    $stmt->bindValue(':soft_name', $softName, SQLITE3_TEXT);
    $stmt->bindValue(':user_ip', $user_ip, SQLITE3_TEXT);
    $stmt->execute();
}
//获取所有数据
if ($_GET['action']== "getdata") {


    $data = array();
    $data["countDatas"] = getCountDatas($db);
    $data["fileSizes"] = getFileSizes("../app");

    // 输出 JSON 格式数据
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}

// 关闭数据库连接
$db->close();


function getCountDatas($db){
    // 获取当前点击次数
    $query = 'SELECT soft_name,click_count FROM download_stats';
    $results = $db->query($query);
    // 将查询结果转换为关联数组
    $countData = array();
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $countData[$row['soft_name']] = $row['click_count'];
    }
    return $countData;
}

function getFileSizes($directory){
// 获取目录中的文件列表  
    $files = scandir($directory);  
    // 排除当前目录（.）和上级目录（..）  
    $files = array_diff($files, array('.', '..'));  
    // 初始化空数组用于存储文件大小信息  
    $fileSizes = array();  
    // 遍历文件列表并获取文件大小信息  
    foreach ($files as $file) {  
        $filePath = $directory . '/' . $file;  
        $fileSize =  round(stat($filePath)['size'] / 1024 / 1024) . "M"; // 单位转换为M  
        $fileSizes[$file] = $fileSize;  
    }  
    return $fileSizes;
}

// 获取用户真实IP地址
function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        // 检查IP地址是否来自共享互联网
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // 检查IP地址是否来自代理
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
?>