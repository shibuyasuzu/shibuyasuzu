<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit;
}

$dsn = 'mysql:;host=localhost';
$user = 'shibuyasuzu';
$password = 'asAp1329';

try {
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

// スケジュール管理用のテーブル作成
$sql = "CREATE TABLE IF NOT EXISTS schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date CHAR(32),
    comment TEXT,
    password VARCHAR(255),
    created_at DATETIME,
    user_id INT
)";
$pdo->query($sql);

// 投稿処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $date = $_POST['date'];
    $comment = $_POST['str'];
    $password = $_POST['post_password'];
    $created_at = date("Y-m-d H:i:s");
    $user_id = $_SESSION['user_id'];

    if (!empty($_POST['edit_id'])) {
        // 編集処理
        $id = $_POST['edit_id'];
        $sql = 'UPDATE schedule SET date=:date, comment=:comment, password=:password, created_at=:created_at WHERE id=:id AND user_id=:user_id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':created_at', $created_at, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // 新規投稿
        $sql = "INSERT INTO schedule (date, comment, password, created_at, user_id) VALUES (:date, :comment, :password, :created_at, :user_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':created_at', $created_at, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }
}

// 投稿一覧表示
$sql = 'SELECT * FROM schedule WHERE user_id = :user_id ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>スケジュール管理</title>
</head>
<body>
    <h1>スケジュール管理</h1>

    <!-- 投稿フォーム -->
    <form action="" method="post">
        <input type="hidden" name="edit_id" value="<?php if (isset($edit_id)) echo $edit_id; ?>">
        <input type="text" name="date" placeholder="日時" required value="<?php if (isset($edit_date)) echo $edit_date; ?>"><br>
        <input type="text" name="str" placeholder="内容" required value="<?php if (isset($edit_comment)) echo $edit_comment; ?>"><br>
        <input type="text" name="post_password" placeholder="パスワード" required><br>
        <button type="submit" name="submit">投稿</button>
    </form>

    <hr>

    <h2>投稿一覧</h2>
    <?php
    foreach ($results as $row) {
        echo '<div>';
        echo '日時: ' . htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') . '<br>';
        echo '内容: ' . nl2br(htmlspecialchars($row['comment'], ENT_QUOTES, 'UTF-8')) . '<br>';
        echo '投稿日時: ' . $row['created_at'] . '<br>';
        echo '<hr>';
        echo '</div>';
    }
    ?>
</body>
</html>
