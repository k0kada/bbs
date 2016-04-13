<?php

  require_once 'model/Bbs.class.php';
  require_once 'model/Account.class.php';

  session_start();

  if (is_null($_SESSION["user_id"])) {
    header('Location: ./login.php/');
    exit();
  }
  $user_id = (int) $_SESSION["user_id"];
  $post_id = (string) filter_input(INPUT_GET, 'id');

  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");
  
  $post_record = model\Bbs::getPostById($post_id, $mysqli);
  $replay_records = model\Bbs::getRepliesByPostId($post_id, $mysqli);
  
  //ワンタイムチケットを生成する。
  $ticket = md5(uniqid(rand(), true));
  $_SESSION['ticket'] = $ticket;

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    <link href="/css/non-responsive.css" rel="stylesheet">

    <title>コメント一覧</title>

    <script type="text/javascript" src="js/jquery-2.2.3.min.js"></script>
    <script type="text/javascript" src="js/jquery.validate.min.js"></script>
    <script type="text/javascript">
    $(function(){

      //バリデーション
      $("#replyForm").validate({
        rules : {
          body: {
            required: true,
            uniformity: true,
            length: [300, 600]
          }
        },
        messages: {
          body: {
            required: "何か入力してください",
            uniformity: "半角または全角に統一してください",
            length: "全角300文字以内または半角600文字以内にしてください"
          }
        },
        errorClass: "msgError",
        errorElement: "div"
      });

      //全角半角が統一されているかチェック
      jQuery.validator.addMethod("uniformity", function(value, element) {
        //最初の文字が全角か半角かチェック
        if (escape(value.charAt(0)).length >= 4) {
          for (var i = 0; i < value.length; i++) {
              //1文字ずつ文字コードをエスケープし、その長さが4文字以上なら全角
              var len = escape(value.charAt(i)).length;
              //半角ならfalse(改行は除外)
              if (len < 4 && (escape(value.charAt(i)) !== '%0A' && escape(value.charAt(i)) !== '%0D')) {
                return this.optional(element) || false;
              }
          }
          return this.optional(element) || true;
        } else {
          for (var i = 0; i < value.length; i++) {
              var len = escape(value.charAt(i)).length;
              //全角ならfalse(改行は除外)
              if (len >= 4 && (escape(value.charAt(i)) !== '%0D%0A')) {
                return this.optional(element) || false;
              }
          }
          return this.optional(element) || true;
        }
      });

      //文字数チェック
      jQuery.validator.addMethod("length", function(value, element, param) {
        //最初の文字が全角か半角かチェック
        if (escape(value.charAt(1)).length >= 4) {
          return this.optional(element) || value.length <= param[0];
        } else {
          return this.optional(element) || value.length <= param[1];
        }
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
            <li><a href="/datawrite.php" >戻る</a></li>
            <li><a href="/search.php" >検索</a></li>
          </ul>
       </div>
      </div>
    </nav>

    <div class="container">
      <h1>返信一覧ページ</h1>
      <div class="page-header">
        <h2>コメント投稿</h2>
      </div>

      <form id="replyForm" method="POST" action="replyRegister.php">
        <textarea name="body" rows="4" cols="40" placeholder="テキストを入力してください"></textarea>
        <input type="hidden" name="post_id" value="<?=$post_id?>">
        <input type="hidden" name="ticket" value="<?=$ticket?>"><br>
        <button class="btn btn-success" type="submit">コメント</button>
      </form>
    </div>

    <div class="table-size">
      <div class="page-header">
        <h2>スレッド</h2>
      </div>
      <div class="table-responsive">
        <table class="table table-striped">
          <tr><th>投稿スレッドid</th><th>ハンドルネーム</th><th >テキスト</th><th>作成日時</th><th>画像</th><th>削除</th></tr>
          <? foreach ($post_record as $post) { ?>
            <tr>
              <td><?= $post['id'] ?></td><td><?= $post['name'] ?></td>
              <td class="col-md-1"><?= nl2br($post['body']) ?></td><td><?= $post['created_at'] ?></td>
              <td>
                <? if (isset($post['image']) && $post['image'] !== '') {?>
                  <img  width="50" height="50" src="/drawImage.php?post_id=<?= $post['id'] ?>">
                <? } ?>
              </td>
              <td>
                <? if ($user_id == $post['user_id']) { ?>
                  <a class="btn btn-danger" href="postDelete.php?id=<?= $post['id'] ?>">削除</a>
                <? } ?>
              </td>
            </tr>
          <? } ?>
        </table>
      </div>
    
      <div class="page-header">
        <h2>コメント</h2>
      </div>
      <div class="table-responsive">

        <table class="table table-striped">
      　　 <tr><th>返信id</th><th>投稿スレッドid</th><th>名前</th><th>テキスト</th><th>作成日時</th></tr>
      　　　<? foreach ($replay_records as $relpy) { ?>
      　　　  <? $accout_name = model\Account::getNameById((int) $relpy['user_id'], $mysqli); ?>
      　　　  <tr>
      　　　      <td><?= $relpy['id'] ?></td><td><?= $relpy['post_id'] ?></td><td><?= $accout_name ?></td><td><?= nl2br($relpy['body']) ?></td><td><?= $relpy['created_at'] ?></td>
      　　　  </tr>
      　　　<? } ?>
        </table>
      </div>
    </div>
  </body>
</html>
