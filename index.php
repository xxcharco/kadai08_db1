<?php
date_default_timezone_set("Asia/Tokyo");

$comment_array = array();
$pdo = null;
$error_messages = array();

// データベース接続情報
$db_server = 'mysql3101.db.sakura.ne.jp';  // さくらのサーバー名
$db_name = 'tawagram_bbs';
$db_user = 'tawagram_bbs';
$db_password = 'fdms1995';

// DB接続
try {
    $dsn = "mysql:host={$db_server};dbname={$db_name};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('データベース接続エラー: ' . $e->getMessage());
}


// フォームを打ち込んだ時
if (!empty($_POST["submitButton"])) {
    // 名前のチェック
    if (empty($_POST["username"])) {
        $error_messages["username"] = "名前を入力してください";
    }

    if (empty($_POST["comment"])) {
        $error_messages["comment"] = "内容を入力してください";
    }

    if (empty($error_messages)) {
        $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
        $comment = htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8');
        $postDate = date("Y-m-d H:i:s");

        try {
            $stmt = $pdo->prepare("INSERT INTO `bbs-table` (`username`, `comment`, `postDate`) VALUES (:username, :comment, :postDate)");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR); 
            $stmt->bindParam(':postDate', $postDate, PDO::PARAM_STR); 

            $stmt->execute();
        } catch(PDOException $e) {
            die('データベース書き込みエラー: ' . $e->getMessage());
        }
        // フォームの再送信を防ぐためにリダイレクト
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
        } catch(PDOException $e) {
            die('データベース書き込みエラー: ' . $e->getMessage());
    }
}

// DBからコメントデータを取得する
try {
    $sql = "SELECT `id`, `username`, `comment`, `postDate` FROM `bbs-table` ORDER BY `postDate` DESC";
    $comment_array = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die('データベース読み込みエラー: ' . $e->getMessage());
}

// DBの接続を閉じる
$pdo = null;
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>さぶちゃんねる</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1 class="title">さぶちゃんねる</h1>
    <hr>
    <div class="boardWrapper">
        <?php if (!empty($error_messages)): ?>
            <ul class="error-messages">
                <?php foreach ($error_messages as $message): ?>
                    <li><?php echo $message; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <section>
            <?php foreach ($comment_array as $comment): ?>
            <article>
                <div class="wrapper">
                    <div class="nameArea">
                        <span>名前：</span>
                        <p class="username"><?php echo $comment["username"]; ?></p>
                        <time>：<?php echo $comment["postDate"]; ?></time>
                    </div>
                    <p class="comment"><?php echo nl2br($comment["comment"]); ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </section>
        <form class="formwrapper" method="POST">
            <div>
                <input type="submit" value="書き込む" name="submitButton">
                <label for="username">名前：</label>
                <input type="text" id="username" name="username">
            </div>
            <div>
                <textarea class="commentTextArea" name="comment"></textarea>
            </div>
        </form>
    </div>
</body>
</html>