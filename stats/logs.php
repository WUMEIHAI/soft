<?php  
// 数据库文件路径  
$dbPath = 'soft.sqlite';  
  date_default_timezone_set('Asia/Shanghai');
// 初始化SQLite3连接  
try {  
    $db = new SQLite3($dbPath);  
} catch (Exception $e) {  
    die("连接数据库失败: " . $e->getMessage());  
}  
  
// 初始化分页参数  
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;  
$perPage = isset($_GET['perPage']) ? intval($_GET['perPage']) : 50;  
$offset = ($page - 1) * $perPage;  
  
// 初始化搜索参数  
$softName = isset($_GET['soft_name']) ? $_GET['soft_name'] : '';  
$userIp = isset($_GET['user_ip']) ? $_GET['user_ip'] : '';  
$startTime = isset($_GET['start_time']) ? $_GET['start_time'] : '';  
$endTime = isset($_GET['end_time']) ? $_GET['end_time'] : '';  
  
// 构建查询语句  
$query = "SELECT soft_name, user_ip, click_time FROM download_clicks";  
$where = array();  
$params = array();  
  
if (!empty($softName)) {  
    $where[] = "soft_name = :softName";  
    $params['softName'] = $softName;  
}  
  
if (!empty($userIp)) {  
    $where[] = "user_ip =:user_ip";  
    $params['user_ip'] = $userIp;  
}  
  
if (!empty($startTime)) {  
    $where[] = "click_time >= :startTime";  
    $params['startTime'] = $startTime;  
}  
if (!empty($endTime)) {  
    $where[] = "click_time <= :endTime";  
    $params['endTime'] = $endTime;  
}  
  
// 添加WHERE子句  
if (!empty($where)) {  
    $query .= " WHERE " . implode(" AND ", $where) ;  
}  
$query .= " order by click_time desc";  
  
// 添加分页限制  
$query .= " LIMIT :perPage OFFSET :offset";  
$params['perPage'] = $perPage;  
$params['offset'] = $offset;  
  
// 准备并执行查询  
$stmt = $db->prepare($query);  
foreach ($params as $key => $value){
    $stmt->bindValue(':'.$key, $value);
}

$rows = [];
$result = $stmt->execute();
 while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
     $rows[] = $row;
 }
// 计算总记录数  
$countQuery = "SELECT COUNT(*) FROM download_clicks";  
if (!empty($where)) {  
    $countQuery .= " WHERE " . implode(" AND ", $where);  
}  
$countStmt = $db->prepare($countQuery); 
foreach ($params as $key => $value){
    $countStmt->bindValue(':'.$key, $value);
}
$countResult = $countStmt->execute();  
$total = $countResult->fetchArray(SQLITE3_NUM)[0];  
  
// 计算总页数  
$totalPages = ceil($total / $perPage);  
 
 // 关闭数据库连接
$db->close();

// 输出HTML  
?>  
<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">  
    <title>Download Clicks</title>  
</head>  
<body>  
    <h1>Download Clicks</h1>  
  
    <form method="GET" action="">  
        <label for="soft_name">软件名称:</label>  
        <input type="text" name="soft_name" value="<?php echo htmlspecialchars($softName); ?>">  
  
        <label for="user_ip">用户IP:</label>  
        <input type="text" name="user_ip" value="<?php echo htmlspecialchars($userIp); ?>">  
  
        <label for="start_time">开始时间:</label>  
        <input type="text" name="start_time" value="<?php echo htmlspecialchars($startTime); ?>">  
  
        <label for="end_time">结束时间:</label>  
        <input type="text" name="end_time" value="<?php echo htmlspecialchars($endTime); ?>">  
  
        <input type="submit" value="搜索">  
    </form>  
  
    <table border="1">  
        <thead>  
            <tr>  
                <th>软件名称</th>  
                <th>用户IP</th>  
                <th>点击时间</th>  
            </tr>  
        </thead>  
        <tbody>  
            <?php foreach ($rows as $row): ?>  
                <tr>  
                    <td><?php echo htmlspecialchars($row['soft_name']); ?></td>  
                    <td><?php echo htmlspecialchars($row['user_ip']); ?></td>  
                    <td><?php echo $row['click_time']; ?></td>  
            </tr> 
            <?php endforeach; ?>
               </tbody>  
    </table>      
                    
     <nav>  
        <ul class="pagination">  
        <li><span>总数量：</span><span><?php echo $total; ?></span><span>,当前页：</span><span><?php echo $page; ?></span></li>  
            <!-- 分页链接，这些通常是由PHP生成的 -->  
            <li><a href="?page=1">首页</a></li>  
            <li><a href="?page=<?php echo $page-1; ?>">上一页</a></li>  
            <li><a href="?page=<?php echo $page+1; ?>">下一页</a></li>  
            <li><a href="?page=<?php echo $totalPages; ?>">尾页</a></li>  
        </ul>  
    </nav>  
 </body>  
</html>
