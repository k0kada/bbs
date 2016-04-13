<?php

  require_once 'model/Account.class.php';

  session_start();

  if (is_null($_SESSION["user_id"])) {
    header('Location: ./login.php/');
    exit();
  }
  $user_id = (int) $_SESSION["user_id"];

  //ワンタイムチケットを生成する。
  $ticket = md5(uniqid(rand(), true));
  $_SESSION['ticket'] = $ticket;
  
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
  $status = (string) filter_input(INPUT_POST, 'status');

  $checked_name = htmlspecialchars($name);
  $checked_msg = htmlspecialchars($msg);

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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    <link href="/css/non-responsive.css" rel="stylesheet">

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
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div id="navbar">
          <ul class="nav navbar-nav">
            <li class="active"><a href="/logout.php">ログアウト</a></li>
            <li><a href="/search.php" >検索</a></li>
          </ul>
       </div>
      </div>
    </nav>

    <div class="container">

      <div class="page-header">
        <h1>メッセージ投稿</h1>
      </div>
      <form id="msgForm" method="POST" enctype="multipart/form-data" action="postRegister.php">
        ハンドルネーム：<input name="name" value="<?= $accout_name ?>" type="text" /><br>
        <textarea name="body" rows="4" cols="40" placeholder="テキストを入力してください"></textarea><br>
        <input type="hidden" name="ticket" value="<?=$ticket?>">
        <input type="file" name="image" accept="image/jpeg, image/gif, image/png">
        <button class="btn btn-success" type="submit">投稿</button>
      </form>
      <div id="error_msg">
        <?= $status === 'success' ? $checked_name. '<br>'. nl2br($checked_msg). '<br>' : '' ?>
        <?= $status === 'failed' ? 'メッセージの保存が失敗しました。<br>' : '' ?>
        <?= $status === 'duplicate' ? '<h2 style="color:red">2重投稿です</h2>' : '' ?>
      </div>

      <div class="page-header">
        <h2>投稿済み一覧</h2>
      </div>
      <ul class="list-inline">
      <? if ($order === 'ASC') { ?>
        <li>昇順</li>
        <li><a href="datawrite.php?order=DESC">降順</a></li>
      <? } ?>
      <? if ($order === 'DESC') { ?>
        <li><a href="datawrite.php?order=ASC">昇順</a></li>
        <li>降順</li>
      <? } ?>
      </ul>


        <table class="table-striped"  width="100%">
          <tr><th>投稿id</th><th>名前</th><th>テキスト</th><th>作成日時</th><th>返信を見る</th><th>画像</th><th>削除</th></tr>
        <? foreach ($pager_array as $post) { ?>
        <?// var_dump($post['image']) ?>
          <tr>
              <td><?= $post['id'] ?></td><td><?= $post['name'] ?></td><td><?= nl2br($post['body']) ?></td><td><?= $post['created_at'] ?></td>
              <td><button><a href="reply.php?id=<?= $post['id'] ?>">コメント</button></td>
              <td>
                  <? if (isset($post['image']) && $post['image'] !== '') {?>
                    <img  width="50" height="50" src="http://ko-okada.net/drawImage.php?post_id=<?= $post['id'] ?>">
                  <? } ?>
              </td>
              <td><button><a href="postDelete.php?id=<?= $post['id'] ?>">削除</button></td>
          </tr>
        <? } ?>
      </table>

      <ul class="list-inline">
      <? if ($page > 1) { ?>
        <li><a href="datawrite.php?order=<?= $order ?>&page=<?= $page - 1 ?>">前のページへ</a></li>
      <? } ?>
      <? if ($page < $max_page) { ?>
        <li><a href="datawrite.php?order=<?= $order ?>&page=<?= $page + 1 ?>">次のページへ</a></li>
      <? } ?>
      </ul>
    </div>
  </body>

</html>
