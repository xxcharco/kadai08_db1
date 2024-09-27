<?php

date_default_timezone_set("Asia/Tokyo");

$comment_array = array();
$pdo = null;
$stmt = null;
$error_messages = array();

//DB接続用の関数
function db_conn()
{
    try {
    　　 // db_name, db_host, db_id, db_pwをご自身のものに書き換えて使用して下さい
        $db_name =  'tawagram_bbs';            //データベース名
        $db_host =  'mysql3101.db.sakura.ne.jp';  //DBホスト
        $db_id =    'fwy80902';                //さくらサーバで登録しているアカウント名
        $db_pw =    'fdms1995';           //さくらサーバのデータベースにアクセスするためのパスワード
        
        $server_info ='mysql:dbname='.$db_name.';charset=utf8;host='.$db_host;
        $pdo = new PDO($server_info, $db_id, $db_pw);
        
        return $pdo;

    } catch (PDOException $e) {
        exit('DB Connection Error:' . $e->getMessage());
    }
}

//フォームを打ち込んだ時
if (!empty($_POST["submitButton"])) {

    //名前のチェック
    if(($_POST["username"])){
        echo "名前を入力してください";
        $error_messages["username"] = "名前を入力してください";
    }

    if(empty($_POST["comment"])){
        echo "内容を入力してください";
        $error_messages["comment"] = "内容を入力してください";
    }

    if (empty($error_messages)){

        $postDate = date("Y-m-d H:i:s");

        try {
            $stmt = $pdo->prepare("INSERT INTO `bbs-table` (`username`, `comment`, `postDate`) VALUES (:username, :comment, :postDate);");
            $stmt->bindParam(':username', $_POST['username'], PDO::PARAM_STR);
            $stmt->bindParam(':comment', $_POST['comment'], PDO::PARAM_STR); 
            $stmt->bindParam(':postDate', $postDate, PDO::PARAM_STR); 

            $stmt->execute();
        } catch(PDOException $e){
        echo $e->getMessage();
        }

    }

}

//DBからコメントデータを取得する
$sql = "SELECT `id`,`username`,`comment`,`postDate` FROM `bbs-table`";
$comment_array = $pdo->query($sql);

//DBの接続を閉じる
$pdo = null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP掲示板</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1 class="title">さんちゃんねる</h1>
    <hr>
    <div class="boardWrapper">
        <section>
            <?php foreach ($comment_array as $comment): ?>
            <article>
                <div class="wrapper">
                    <div class="nameArea">
                        <span>名前：</span>
                        <p class="username"><?php echo $comment["username"]; ?></p>
                        <time>：<?php echo $comment["postDate"]; ?></time>
                    </div>
                    <p class="comment"><?php echo $comment["comment"]; ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </section>
        <form class="formwrapper" method="POST">
            <div>
                <input type="submit" value="書き込む" name="submitButton">
                <label for="">名前：</label>
                <input type="text" name="username">
            </div>
            <div>
                <textarea class="commentTextArea" name="comment"></textarea>
            </div>
        </form>
    </div>
</body>
</html>
