<?php

  require_once 'model/Account.class.php';

  session_start();

  $user_id = (int) $_SESSION["user_id"];
  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");

  //ユーザーidからアカウント名を取得(DBのユーザー名前)
  $accout_name = model\Account::getNameById((int) $user_id, $mysqli);
  
  //ページャー処理
  $page = filter_input(INPUT_GET, 'page') ? (string) filter_input(INPUT_GET, 'page') : 1;

  //昇順、降順判定
  $order = in_array(filter_input(INPUT_GET, 'order'), array('ASC', 'DESC')) ? (string) filter_input(INPUT_GET, 'order') : 'DESC';

  $pager_array = getPage((int) $page, $order, $mysqli);
  $max_page = getMaxPage($mysqli);

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
    //入力チェック
    $insert_flag = checkInputFlag($checked_name, $checked_msg);
    if ($insert_flag) {
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
   * 入力チェック
   * @param type $checked_name
   * @param type $checked_msg
   * @return type
   */
  function checkInputFlag($checked_name, $checked_msg)
  {
    $status = 0;
    //名前入力チェック(判定falseなら0、trueなら+1)
    switch ($checked_name) {
      case '':
        break;
      case strlen($checked_name) > 30:
        break;
      default :
        $status += 1;
        break;
    }

    //msg入力チェック(判定falseなら0、trueなら+1)
    switch ($checked_msg) {
      case '':
          break;
      case strlen($checked_name) > 600:
          break;
      default :
        $status += 1;
        break;
    }

    return $status == 2 ? true : false;
  }

  /**
   * 該当するページの投稿を最大10個取ってくる
   * @param type $page
   * @param type $mysqli
   * @return type
   */
  function getPage($page, $order, $mysqli)
  {
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM post ORDER BY id ". $order. " LIMIT " . $offset. ", ". $limit;
    $result = $mysqli->query($sql);

    $limited_array = array();
    while ($row = $result->fetch_assoc()) {
      //セッションにユーザ名を保存(ログイン済みかのフラグ)
      $limited_array[] = $row;
    }
    $result->close();

    return $limited_array;
  }

  /**
   * 投稿されている最大ページ数を取ってくる
   * @param type $mysqli
   * @return type
   */
  function getMaxPage($mysqli)
  {
    $limit = 10;

    $sql = "SELECT id FROM post";
    $result = $mysqli->query($sql);
    $num_rows = $result->num_rows;
    //小数点切り上げ
    $max_page = ceil($num_rows / $limit);
    $result->close();

    return (int) $max_page;
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
            required: true,
            maxByte: 30
          },
          body: {
            required: true,
            maxByte: 600
          }
        },
        messages: {
          name: {
            required: "何か入力してください",
            maxByte: "30byte以下で入力してください"
          },
          body: {
            required: "何か入力してください",
            maxByte: "600byte以下で入力してください"
          }
        },
        errorClass: "msgError",
        errorElement: "div"
      });

      //バイト数判定
      jQuery.validator.addMethod("maxByte", function(value, element, param) {
        var txt_byte = encodeURIComponent(value).replace(/%../g,"x").length;
        return txt_byte <= param;
      });

    });
    </script>

  </head>
  <body>
    <a href="../logout.php">ログアウト</a>
    <h1>メッセージ投稿</h1>
    <form id="msgForm" method="POST" action="datawrite.php">
      名前：<input name="name" value="<?= $accout_name ?>" type="text" /><br>
      <textarea name="body" rows="4" cols="40" placeholder="テキストを入力してください"></textarea>
      <input  type="submit" value="投稿" />
    </form>
    <?= $status === 'success' ? $checked_name. '<br>'. nl2br($checked_msg) : '' ?>
    <?= $status === 'failed' ? 'メッセージの保存が失敗しました。' : '' ?>

    <h1>投稿済み一覧</h1>
    <ul>
    <? if ($order === 'ASC') { ?>
      <li>昇順</li>
      <li><a href="datawrite.php?order=DESC">降順</a></li>
    <? } ?>
    <? if ($order === 'DESC') { ?>
      <li><a href="datawrite.php?order=ASC">昇順</a></li>
      <li>降順</li>
    <? } ?>
    </ul>
    <table border=1>
      <tr><th>投稿id</th><th>名前</th><th>テキスト</th><th>作成日時</th></tr>
      <? foreach ($pager_array as $post) { ?>
        <tr><td><?= $post['id'] ?></td><td><?= $post['name'] ?></td><td><?= $post['body'] ?></td><td><?= $post['created_at'] ?></td></tr>
      <? } ?>
    </table>

    <ul>
    <? if ($page > 1) { ?>
      <li><a href="datawrite.php?order=<?= $order ?>&page=<?= $page - 1 ?>">前のページへ</a></li>
    <? } ?>
    <? if ($page < $max_page) { ?>
      <li><a href="datawrite.php?order=<?= $order ?>&page=<?= $page + 1 ?>">次のページへ</a></li>
    <? } ?>
    </ul>

  </body>

</html>
