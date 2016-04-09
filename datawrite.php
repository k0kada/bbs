<?php
  session_start();

  $msg = (string) filter_input(INPUT_POST, 'body');
  $checked_msg = htmlspecialchars($msg);

  $status = insertMsg($checked_msg);

  function insertMsg($checked_msg)
  {
    if ($checked_msg !== '') {
      $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");

      $now = date("Y-m-d H:i:s");

      $stmt = $mysqli->prepare("INSERT INTO post (user_id, body, created_at) VALUES (?, ?, ?)");
      $stmt->bind_param('iss', $_SESSION["user_id"], $checked_msg, $now);

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
      $("#msgForm").validate({
        rules : {
          body: {
            required: true
          }
        },
        messages: {
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
      メッセージ：<input name="body" type="text" />
      <input  type="submit" value="投稿" />
    </form>
    <?= $status === 'success' ? $checked_msg : '' ?>
    <?= $status === 'failed' ? 'メッセージの保存が失敗しました。' : '' ?>
  </body>

</html>
