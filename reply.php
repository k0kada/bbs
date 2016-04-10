<?php

  require_once 'model/Account.class.php';

  session_start();

  if (is_null($_SESSION["user_id"])) {
    header('Location: ./login.php/');
    exit();
  }
  $user_id = (int) $_SESSION["user_id"];
  $post_id = (string) filter_input(INPUT_GET, 'id');

  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");
  
  $post_array = getPost($post_id, $mysqli);
  $replay_array = getReplies($post_id, $mysqli);
  
  //ワンタイムチケットを生成する。
  $ticket = md5(uniqid(rand(), true));
  $_SESSION['ticket'] = $ticket;

  function getPost($post_id, $mysqli)
  {
    $sql = "SELECT * FROM post WHERE id = ". $post_id;
    $result = $mysqli->query($sql);
    $array = array();
    while ($row = $result->fetch_assoc()) {
      //セッションにユーザ名を保存(ログイン済みかのフラグ)
      $array[] = $row;
    }
    $result->close();
    return $array;
  }

  function getReplies($post_id, $mysqli)
  {
    $sql = "SELECT * FROM reply WHERE post_id = ". $post_id;
    $result = $mysqli->query($sql);
    $array = array();
    while ($row = $result->fetch_assoc()) {
      //セッションにユーザ名を保存(ログイン済みかのフラグ)
      $array[] = $row;
    }
    $result->close();
    return $array; 
  }

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>コメント一覧</title>
  </head>
  <body>
    <h1>コメント一覧</h1>
    <h2>スレッド</h2>
    <table border=1>
        <tr><th>投稿id</th><th>名前</th><th>テキスト</th><th>作成日時</th></tr>
      <? foreach ($post_array as $post) { ?>
        <tr>
            <td><?= $post['id'] ?></td><td><?= $post['name'] ?></td><td><?= $post['body'] ?></td><td><?= $post['created_at'] ?></td>
        </tr>
      <? } ?>
    </table>
    
    <h2>コメント</h2>
    <table border=1>
        <tr><th>返信id</th><th>名前</th><th>テキスト</th><th>作成日時</th></tr>
      <? foreach ($replay_array as $relpy) { ?>
        <? $accout_name = model\Account::getNameById((int) $relpy['user_id'], $mysqli); ?>
        <tr>
            <td><?= $relpy['id'] ?></td><td><?= $accout_name ?></td><td><?= nl2br($relpy['body']) ?></td><td><?= $relpy['created_at'] ?></td>
        </tr>
      <? } ?>
    </table>

    <h2>コメント投稿</h2>
    <form id="replyForm" method="POST" action="replyRegister.php">
      <textarea name="body" rows="4" cols="40" placeholder="テキストを入力してください"></textarea>
      <input type="hidden" name="post_id" value="<?=$post_id?>">
      <input type="hidden" name="ticket" value="<?=$ticket?>">
      <input  type="submit" value="投稿" />
    </form>

  </body>
</html>
