<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>みょんのサイト</title>
</head>

<?php
//1.DB設定（DB名、ユーザー名、パスワード）
$dsn = 'DB名';
$user = 'ユーザー名';
$password = 'パスワード';
//2.MySQLのDBに接続
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

//2.DB内にテーブル作成する（CREATE TABLE）：1度作成したらまた書く必要ない
$sql = "CREATE TABLE IF NOT EXISTS myon"
  . " ("
  . "id INT AUTO_INCREMENT PRIMARY KEY,"
  . "name char(32),"
  . "comment TEXT,"
  . "date DATETIME,"
  . "password char(32)"
  . ");";
  $stmt = $pdo->query($sql);
?>

    <header>
        <div class="info">
          <h1 align="center">みんな、お疲れさま！✨</h1>
        </div>
    </header>
    
    <style>
    hr {
	    border: none;
	    border-top: solid 1px #ffc2ce;
    }
    </style>

<body>

   <section class="myon">
      <h>動作確認のついでに、よかったらみんなにメッセージ書いみて☺</h>
    </section>

<footer>
          
<?php
    ///3.ifで新規投稿、削除を分岐
    if(!empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["pass"]) && !empty($_POST["button1"])){
        /*変数へ*/
        $name = $_POST["name"];
        $comment = $_POST["comment"];
        $formPassword = $_POST["pass"];
        //日付は入力フォームがないから“＄”を使って変換しない
        $date = date("Y/m/d H:i:s");

        //編集用フォームに文字が入力されていないとき
        if(empty($_POST["hiddenEdit"])){
            //3-1.【新規投稿】データを入力（INSERT）：入力されたものをDBにそのまま書き込み
            $sql = $pdo -> prepare("INSERT INTO myon (name, comment ,date ,password) VALUES (:name, :comment, :date, :password)");
            $sql -> bindParam(':name', $name, PDO::PARAM_STR);
            $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
            $sql -> bindParam(':date', $date, PDO::PARAM_STR);
            $sql -> bindParam(':password', $formPassword, PDO::PARAM_STR);
            $sql -> execute();

        }else{
            //3-1.【編集（上書き）】
            $id = $_POST["hiddenEdit"]; //変更する投稿番号
            $sql = "UPDATE myon SET name=:name,comment=:comment,password=:password WHERE id=:id";
            $stmt = $pdo->prepare($sql);
            $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
            $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt -> bindParam(':password', $formPassword, PDO::PARAM_STR);
            $stmt -> bindParam(':id', $id, PDO::PARAM_INT);
            $stmt -> execute();
        }
    }

    //4.【削除】 入力したレコードを削除する（DELETE）
    if(!empty($_POST["delnum"]) && !empty($_POST["delpass"]) && !empty($_POST["button2"])){
        /*削除対象番号とパスワードを変数へ*/
        $delnum = $_POST["delnum"];
        $password = $_POST["delpass"];

        /*削除対象番号、パスワードを取り出してフォームから入力されたものと比べる*/
        /*データベースから取り出す*/
        $sql = "SELECT * FROM myon";
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            /*削除対象番号とパスワードが一致した場合削除*/
            $deleteID = $row['id'];
            $delpass = $row['password'];
            if($deleteID == $delnum && $delpass == $password){
                $id = $deleteID;
                $sql = "delete from myon where id=:id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                break;
            }
        }    
    }

    //5.【編集】入力されているレコードの内容を編集する（UPDATE）
    if(!empty($_POST["ednum"]) && !empty($_POST["edpass"]) && !empty($_POST["button3"])){
        /*編集対象番号とパスワードを変数へ*/
        $ednum=$_POST["ednum"];
        $password=$_POST["edpass"];

        /*編集対象番号、パスワードを取り出してフォームから入力されたものと比べる*/
        /*データベースから取り出す*/
        $sql = "SELECT * FROM myon";
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            /*編集対象番号、パスワードが一致した場合、名前、コメント、隠し編集番号を定義してフォームに表示された状態にする*/
            $editID = $row['id'];
            $edpass = $row['password'];
            if($editID == $ednum && $edpass == $password){
                $editNum = $row['id'];
                $editName = $row['name'];
                $editComment =$row['comment'];
                break;
            }
        }
    }    

    //6.【表示】（SELECT）
    $sql = "SELECT * FROM myon";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
?>  

<?php
    foreach ($results as $row){ /*htmlentitiesはXSS対策、&nbsp;は空白入れるため*/
        echo htmlentities($row['id'], ENT_QUOTES, 'UTF-8').'&nbsp;';
        echo ("[");
        echo htmlentities($row['name'], ENT_QUOTES, 'UTF-8').'&nbsp;';
        echo ("]");
        echo htmlentities($row['comment'], ENT_QUOTES, 'UTF-8').'&nbsp;';
        echo htmlentities($row['date'], ENT_QUOTES, 'UTF-8').'<br>';
        echo "<hr>";
    }
?>

<!--投稿フォーム-->
    <form action="" method="post">
        <!--名前フォーム-->
        <input type="text" 
            value="<?php
            if(!empty($_POST["ednum"]) && !empty($_POST["edpass"]) && !empty($_POST["button3"])){
                if($editID == $ednum && $edpass == $password){
                    echo $editName;
                }
            }?>" 
        placeholder="名前！" name="name"><br>

        <!--コメントフォーム-->
        <input type="textarea" 
            value="<?php
            if(!empty($_POST["ednum"]) && !empty($_POST["edpass"]) && !empty($_POST["button3"])){
                if($editID == $ednum && $edpass == $password){
                    echo $editComment;
                }
            }?>"  
        placeholder="メッセージ" name="comment"><br>

        <!--パスワード-->
        <input type="text" placeholder="パスワード" name="pass"><br>

        <!--隠し編集番号-->
        <input type="hidden" 
            value="<?php
            if(!empty($_POST["ednum"]) && !empty($_POST["edpass"]) && !empty($_POST["button3"])){
                if($editID == $ednum && $edpass == $password){
                    echo $editNum;
                }
            }?>"  
        name="hiddenEdit">

        <!--送信ボタン-->
        <input type="submit" value="送信" name="button1">
    </form> 

<!--削除フォーム-->
<form action="" method="post">
    <input type="number" placeholder="削除対象番号" name="delnum"><br>
    <!--パスワード-->
    <input type="text" placeholder="パスワードを入力してね" name="delpass"><br>
    <!--送信ボタン-->
    <input type="submit" value="削除" name="button2">
</form>

<!--編集フォーム-->
<form action="" method="post">
    <input type="number" placeholder="編集対象番号" name="ednum"><br>
    <!--パスワード-->
    <input type="text" placeholder="パスワードを入力してね" name="edpass"><br>
    <!--送信ボタン-->
    <input type="submit" value="編集" name="button3">
</form>
</footer>
</body>
</html>
</html>

</body>
</html>