<?php
  require_once 'model/Account.class.php';

  session_start();

  if (is_null($_SESSION["user_id"])) {
    header('Location: /login.php/');
    exit();
  }
  $user_id = (int) $_SESSION["user_id"];

  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");
  $search_word = (string) filter_input(INPUT_POST, 'search');
  $post_array = getPostsByBody($search_word, $mysqli);
  $reply_array = getRepliesByBody($search_word, $mysqli);

  $count_hit_search = countHitSeach($post_array, $reply_array);

  function getPostsByBody($search_word, $mysqli)
  {
    $records = array();

    if ($search_word !== '') {
      $sql = "SELECT * FROM post WHERE body LIKE ". "'%". htmlspecialchars($search_word). "%'";
      $result = $mysqli->query($sql);
  
      while ($row = $result->fetch_assoc()) {
        $records[] = $row;
      }
      $result->close();
    }

    return $records;
  }

  function getRepliesByBody($search_word, $mysqli)
  {
    $records = array();

    if ($search_word !== '') {
      $sql = "SELECT * FROM reply WHERE body LIKE ". "'%". htmlspecialchars($search_word). "%'";
      $result = $mysqli->query($sql);
  
      while ($row = $result->fetch_assoc()) {
        $records[] = $row;
      }
      $result->close();
    }

    return $records;
  }

  function countHitSeach($post_array, $reply_array)
  {
    return count($post_array) + count($reply_array);
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

    <title>検索</title>
  </head>
  <body>
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div id="navbar">
          <ul class="nav navbar-nav">
            <li class="active"><a href="/logout.php">ログアウト</a></li>
            <li><a href="/datawrite.php" >戻る</a></li>
          </ul>
       </div>
      </div>
    </nav>

    <div class="container">
      <div class="page-header">
        <h1>検索</h1>
      </div>

      <form id="searchForm" method="POST" action="search.php">
        <input type="text" name="search">
        <input  type="submit" value="検索" />
      </form>

    <?= $search_word !== '' ? '検索ワード='. htmlspecialchars($search_word) : '' ?><br>
    <?= $search_word !== '' ? '検索結果＝'. $count_hit_search. '件ヒットしました' : '' ?>
    </div>

    <? if ($count_hit_search > 0) { ?>
    <div class="table-size">
      <div class="page-header">
        <h3>投稿検索結果</h3>
      </div>

      <div class="table-responsive">
        <table class="table table-striped">
          <tr><th>投稿スレッドid</th><th>ハンドルネーム</th><th>テキスト</th><th>作成日時</th><th>返信を見る</th><th>画像</th><th>削除</th></tr>
          <? foreach ($post_array as $post) { ?>
            <tr>
                <td><?= $post['id'] ?></td><td><?= $post['name'] ?></td>
                <td class="col-md-1"><?= nl2br($post['body']) ?></td><td><?= $post['created_at'] ?></td>
                <td><a class="btn btn-primary" href="reply.php?id=<?= $post['id'] ?>">コメント</a></td>
                <td>
                  <? if (isset($post['image']) && $post['image'] !== '') {?>
                    <img  width="50" height="50" src="/drawImage.php?post_id=<?= $post['id'] ?>">
                  <? } ?>
                </td>
                <td>
                  <? if ($user_id == $post['user_id']) { ?>
                    <a class="btn btn-danger" href="/postDelete.php?id=<?= $post['id'] ?>">削除</a>
                  <? } ?>
                </td>
            </tr>
          <? } ?>
        </table>
      </div>

      <div class="page-header">
        <h3>返信検索結果</h3>
      </div>

      <div class="table-responsive">
        <table class="table table-striped">
          <tr><th>返信id</th><th>投稿スレッドid</th><th>名前</th><th>テキスト</th><th>作成日時</th></tr>
          <? foreach ($reply_array as $reply) { ?>
            <? $accout_name = model\Account::getNameById((int) $reply['user_id'], $mysqli); ?>
  
            <tr>
                <td><?= $reply['id'] ?></td><td><?= $reply['post_id'] ?></td><td><?= $accout_name ?></td><td><?= nl2br($reply['body']) ?></td><td><?= $reply['created_at'] ?></td>
            </tr>
          <? } ?>
        </table>
      </div>
    </div>
    <? } ?>
  </body>
</html>
