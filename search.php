<?php
  require_once 'model/Account.class.php';

  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");
  $search_word = (string) filter_input(INPUT_POST, 'search');
  $post_array = getPostsByBody($search_word, $mysqli);
  $reply_array = getRepliesByBody($search_word, $mysqli);

  $count_hit_search = countHitSeach($post_array, $reply_array);

  function getPostsByBody($search_word, $mysqli)
  {
    $array = array();

    if ($search_word !== '') {
      $sql = "SELECT * FROM post WHERE body LIKE ". "'%". htmlspecialchars($search_word). "%'";
      $result = $mysqli->query($sql);
  
      while ($row = $result->fetch_assoc()) {
        //セッションにユーザ名を保存(ログイン済みかのフラグ)
        $array[] = $row;
      }
      $result->close();
    }

    return $array;
  }

  function getRepliesByBody($search_word, $mysqli)
  {
    $array = array();

    if ($search_word !== '') {
      $sql = "SELECT * FROM reply WHERE body LIKE ". "'%". htmlspecialchars($search_word). "%'";
      $result = $mysqli->query($sql);
  
      while ($row = $result->fetch_assoc()) {
        //セッションにユーザ名を保存(ログイン済みかのフラグ)
        $array[] = $row;
      }
      $result->close();
    }

    return $array;
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
    <title>検索</title>
  </head>
  <body>
      <a href="../datawrite.php">戻る</a>
    <h1>検索</h1>

    <form id="searchForm" method="POST" action="search.php">
      <input type="text" name="search">
      <input  type="submit" value="検索" />
    </form>

    <?= '検索ワード='. htmlspecialchars($search_word) ?><br>

    <? if ($count_hit_search > 0) { ?>
      <?= '検索結果＝'. $count_hit_search. '件ヒットしました' ?>

      <h3>投稿検索結果</h3>
      <table border=1>
        <tr><th>投稿id</th><th>名前</th><th>テキスト</th><th>作成日時</th><th>返信を見る</th><th>画像</th></tr>
        <? foreach ($post_array as $post) { ?>
          <tr>
            <td><?= $post['id'] ?></td><td><?= $post['name'] ?></td><td><?= nl2br($post['body']) ?></td><td><?= $post['created_at'] ?></td>
            <td><button><a href="reply.php?id=<?= $post['id'] ?>">コメント</button></td>
            <td style="background-image:url('drawImage.php?post_id=<?= $post['id'] ?>'); background-size:cover;"></td>          </tr>
        <? } ?>
      </table>

      <h3>返信検索結果</h3>
      <table border=1>
          <tr><th>返信id</th><th>名前</th><th>テキスト</th><th>作成日時</th></tr>
        <? foreach ($reply_array as $reply) { ?>
          <? $accout_name = model\Account::getNameById((int) $reply['user_id'], $mysqli); ?>

          <tr>
              <td><?= $reply['id'] ?></td><td><?= $accout_name ?></td><td><?= nl2br($reply['body']) ?></td><td><?= $reply['created_at'] ?></td>
          </tr>
        <? } ?>
      </table>
    <? } ?>
  </body>
</html>
