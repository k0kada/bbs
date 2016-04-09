<?php

  require_once 'model/Account.class.php';

  session_start();

  $user_id = (int) $_SESSION["user_id"];
  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");
  //ユーザーidからアカウント名を取得(DBのユーザー名前)
  $accout_name = account\Account::getNameById((int) $user_id, $mysqli);
  
  $name = (string) filter_input(INPUT_POST, 'name');  
  $msg = (string) filter_input(INPUT_POST, 'body');
  $checked_name = htmlspecialchars($name);
  $checked_msg = htmlspecialchars($msg);

  $status = insertFormValue($checked_name, $checked_msg);

  /**
   * フォームの入力をDBに保存する
   * @param type $checked_name
   * @param type $checked_msg
   * @return string
   */
  function insertFormValue($checked_name, $checked_msg)
  {
    if ($checked_name !== '' && $checked_msg !== '') {
      $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");

      $now = date("Y-m-d H:i:s");

      $stmt = $mysqli->prepare("INSERT INTO post (user_id, name, body, created_at) VALUES (?, ?, ?, ?)");
      $stmt->bind_param('isss', $_SESSION["user_id"], $checked_name,  $checked_msg, $now);

      if ($stmt->execute()) {
        return 'success';
      }
      return 'failed';
    }
    return '';
  }

  /**
   * Step1::ファイルほぞん
   */
  function saveDataFile()
  {
    $file_name = dirname(__FILE__). '/data/data.txt';
    $input_word = 'hoge';

    // ファイルの存在確認
    if (file_exists($file_name)) {
      echo 'すでにファイルが存在しているので上書きします<br>';
    } else {
      echo '新規に作成します<br>';
    }

    //ファイルポインタをファイルの先頭に置く(上書き)
    $fopen = fopen($file_name, 'w');

    //ファイルをロック
    if (flock($fopen, LOCK_EX)){
      //書き出し
      if (fwrite($fopen,  $input_word)){
        echo $input_word. 'を'. $file_name. 'に書き込みました<br>';
      } else {
        echo 'ファイル書き込みに失敗しました<br>';
      }
      flock($fopen, LOCK_UN);
    } else {
      echo '誰かが同時に書き込もうとして失敗しました<br>';
    }

    fclose($fopen);
  }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>メッセージ投稿</title>
    <script type="text/javascript" src="js/jquery-2.2.3.min.js"></script>
    <script type="text/javascript" src="js/jquery.validate.min.js"></script>

    <script type="text/javascript">
    $(function(){

      //バリデーション
      $("#msgForm").validate({
        rules : {
          name: {
            required: true
          },
          body: {
            required: true
          }
        },
        messages: {
          name: {
            required: "何か入力してください"
          },
          body: {
            required: "何か入力してください"
          }
        },
        errorClass: "msgError",
        errorElement: "div"
      });
    });
    </script>

  </head>
  <body>
    <a href="../logout.php">ログアウト</a>

    <h1>メッセージ投稿</h1>
    <form id="msgForm" method="POST" action="datawrite.php">
      名前：<input name="name" value="<?= $accout_name ?>" type="text" />
      <textarea name="body" rows="4" cols="40" placeholder="テキストを入力してください"></textarea>
      <input  type="submit" value="投稿" />
    </form>
    <?= $status === 'success' ? $checked_name. '<br>'. nl2br($checked_msg) : '' ?>
    <?= $status === 'failed' ? 'メッセージの保存が失敗しました。' : '' ?>
  </body>

</html>
