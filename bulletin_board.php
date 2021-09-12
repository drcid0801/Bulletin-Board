<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>bullet board</title>
</head>
<body>
<?PHP 
    // DB接続設定
    $dsn = 'mysql:dbname=データベース名;host=localhost';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

    //テーブルの作成
    $sql = "CREATE TABLE IF NOT EXISTS post"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name char(32),"
    . "comment TEXT,"
    . "time DATETIME,"
    . "password char(32)"
    .");";
    $stmt = $pdo->query($sql);

    //エラーフラグ
    $name_empty = false;
    $str_empty = false;
    $del_empty = false;
    $edit_empty = false;
    $pass_empty = false;
    $invalid_pass = false;
    $invalid_del_num = false;
    $invalid_edit_num = false;

    //フォーム未入力の判定
    if(isset($_POST["name"])){
        if(empty($_POST["name"])){
            $name_empty = true;
        }elseif(empty($_POST["str"])){
            $str_empty = true;
        }elseif(empty($_POST["pass"])){
            $pass_empty = true;
        }
    }elseif(isset($_POST["del"])){
        if(empty($_POST["del"])){
            $del_empty = true;
        }elseif(empty($_POST["pass"])){
            $pass_empty = true;
        }
    }elseif(isset($_POST["edit"])){
        if(empty($_POST["edit"])){
            $edit_empty = true;
        }elseif(empty($_POST["pass"])){
            $pass_empty = true;
        }
    }
    //処理パート
    if(!empty($_POST["name"]) && !empty($_POST["str"]) && !empty($_POST["pass"])){
        //投稿パート

        if(empty($_POST["edit_num"])){
            //編集対象の目印を受け取っていないとき

            //データベースに投稿を追加
            $sql = $pdo -> prepare("INSERT INTO post (name, comment, time, password) VALUES (:name, :comment, :time, :pass)");
            $sql -> bindParam(':name', $name, PDO::PARAM_STR);
            $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
            $sql -> bindParam(':time', $time, PDO::PARAM_STR);
            $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
            $name = $_POST["name"];
            $comment = $_POST["str"]; 
            $time = date("Y/m/d H:i:s");
            $pass = $_POST["pass"];
            $sql -> execute();

        }else{
            //編集対象の目印を受け取ったとき

            //データベースの対象レコードを編集
            $id = $_POST["edit_num"]; 
            $name = $_POST["name"];
            $comment = $_POST["str"]; 
            $pass = $_POST["pass"];
            $sql = 'UPDATE post SET name=:name,comment=:comment,password=:pass WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
            $stmt->execute();

        }

    }elseif(!empty($_POST["del"]) && !empty($_POST["pass"])){
        //削除パート

        //受信した削除対象番号とパスワードを新たな変数に格納
        $pass = $_POST["pass"];
        $id = $_POST["del"]; 

        //削除対象番号のレコードを抽出
        $sql = 'SELECT * FROM post WHERE id=:id ';
        $stmt = $pdo->prepare($sql);  
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
        $stmt->execute();             
        $results = $stmt->fetchAll(); 
        $count=$stmt->rowCount();

        if($count > 0){
            //削除対象番号のレコードがある時
            
            if($results[0]['password'] == $pass){
                //passが一致した時
                $sql = 'delete from post where id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

            }else{
                //passが一致しない時

                //パスワードが一致しないフラグを立てる
                $invalid_pass = true;
            }

        }else{
            //削除対象番号のレコードがない時

            //削除対象番号がないフラグを立てる
            $invalid_del_num = true;
        }

    }elseif(!empty($_POST["edit"]) && !empty($_POST["pass"])){
        //編集パート

        //受信した編集対象番号とパスワードを新たな変数に格納
        $pass = $_POST["pass"];
        $id = $_POST["edit"]; 

        //編集対象番号のレコードを抽出
        $sql = 'SELECT * FROM post WHERE id=:id ';
        $stmt = $pdo->prepare($sql);  
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
        $stmt->execute();             
        $results = $stmt->fetchAll(); 
        $count=$stmt->rowCount();

        if($count > 0){
            //編集対象番号のレコードがある時
            if($results[0]['password'] == $pass){
                //passが一致した時
                
                $edit_name = $results[0]['name'];
                $edit_str = $results[0]['comment'];
                $edit_pass = $results[0]['password'];
                    
                $edit_num = $_POST["edit"];

            }else{
                //passが一致しない時

                //パスワードが一致しないフラグを立てる
                $invalid_pass = true;
            }

        }else{
            //編集対象番号のレコードがない時

            //編集対象番号がないフラグを立てる
            $invalid_edit_num = true;
        }
    }
?>

<!--題名-->
<h1>好きなくだものを投稿してね！</h1>

<!--投稿フォーム-->
【投稿フォーム】
<form action="" method="post">
    お名前：　　　
    <input type="text" name="name" value="<?PHP
        if(isset($edit_name)){
            echo $edit_name;
        }
    ?>" placeholder="お名前"><br>
    コメント：　　
    <input type="text" name="str" value="<?PHP
        if(isset($edit_str)){
            echo $edit_str;
        }
    ?>" placeholder="コメント">
    <!--編集判定-->
    <input type="hidden" name="edit_num" value="<?PHP
        if(isset($edit_num)){
            echo $edit_num;
        }
    ?>" ><br>
    パスワード：　
    <input type="password" name="pass" value="<?PHP
        if(isset($edit_pass)){
            echo $edit_pass;
        }
    ?>" placeholder="パスワード"><br>
    <input type="submit" name="submit"><br><br>
</form>

<!--削除フォーム-->
【削除フォーム】
<form action="" method="post">
    削除対象番号：
    <input type="number" name="del" value="" placeholder="削除対象番号"><br>
    パスワード：　
    <input type="password" name="pass" value="" placeholder="パスワード"><br>
    <input type="submit" name="submit" value="削除"><br><br>
</form>

<!--編集フォーム-->
【編集フォーム】
<form action="" method="post">
    編集対象番号：
    <input type="number" name="edit" value="" placeholder="編集対象番号"><br>
    パスワード：　
    <input type="password" name="pass" value="" placeholder="パスワード"><br>
    <input type="submit" name="submit" value="編集"><br><br>
</form>

<?PHP
    
    //エラーメッセージの表示
    if($name_empty){
        echo '<font color="red">名前を入力して下さい</font><br><br>';
    }elseif($str_empty){
        echo '<font color="red">コメントを入力して下さい</font><br><br>';
    }elseif($del_empty){
        echo '<font color="red">削除対象番号を入力して下さい</font><br><br>';
    }elseif($edit_empty){
        echo '<font color="red">編集対象番号を入力して下さい</font><br><br>';
    }elseif($pass_empty){
        echo '<font color="red">パスワードを入力して下さい</font><br><br>';
    }elseif($invalid_pass){
        echo '<font color="red">パスワードが正しくありません</font><br><br>';
    }elseif($invalid_del_num){
        echo '<font color="red">指定した番号の投稿がありません</font><br><br>';
    }elseif($invalid_edit_num){
        echo '<font color="red">指定した番号の投稿がありません</font><br><br>';
    }

    //投稿の表示
    echo "【投稿一覧】<hr>";
    
    $sql = 'SELECT * FROM post';
    $stmt = $pdo->prepare($sql);  // ←差し替えるパラメータを含めて記述したSQLを準備し、
    $stmt->execute();           // ←SQLを実行する。
    $results = $stmt->fetchAll();

    foreach ($results as $row){
        //$rowの中にはテーブルのカラム名が入る
            echo "投稿番号：".$row['id'].'<br>';
            echo "名前：　　".$row['name'].'<br>';
            echo "コメント：".$row['comment'].'<br>';
            echo "投稿日時：".$row['time'].'<br><hr>';
        
    } 
    
?>

</body>
<html>

