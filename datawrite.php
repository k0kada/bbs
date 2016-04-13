<?php

  require_once 'model/Account.class.php';
  require_once 'model/Bbs.class.php';

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

  //表示するレコードを取得
  $records = model\Bbs::getPage((int) $page, $order, $mysqli);
  //最大ページ数計算
  $max_page = model\Bbs::getMaxPage($mysqli);
  //フォームでpostされた情報
  $form_output_array = model\Bbs::getFormOutput();

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
            uniformity: true,
            length: [15, 30]
          },
          body: {
            required: true,
            uniformity: true,
            length: [300, 600]
          }
        },
        messages: {
          name: {
            required: "何か入力してください",
            uniformity: "半角または全角に統一してください",
            length: "全角15文字以内または半角30文字以内にしてください"
          },
          body: {
            required: "何か入力してください",
            uniformity: "半角または全角に統一してください",
            length: "全角300文字以内または半角600文字以内にしてください"
          }
        },
        errorClass: "msgError",
        errorElement: "div"
      });

      ////バイト数判定
      //jQuery.validator.addMethod("maxByte", function(value, element, param) {
      //  var txt_byte = encodeURIComponent(value).replace(/%../g,"x").length;
      //  return txt_byte <= param;
      //});

      //全角半角が統一されているかチェック
      jQuery.validator.addMethod("uniformity", function(value, element) {
        console.log(escape(value));
        //最初の文字が全角か半角かチェック
        if (escape(value.charAt(1)).length >= 4) {
          for (var i = 0; i < value.length; i++) {
            //改行は除外
            if (escape(value.charAt(1)) !== '%0A' || escape(value.charAt(1)) !== '%0D' || escape(value.charAt(1)) !== '%0D%0A') {
              //1文字ずつ文字コードをエスケープし、その長さが4文字以上なら全角
              var len = escape(value.charAt(i)).length;
              if (len < 4) {
                return false;
              }
            }
          }
          return true;
        } else {
          for (var i = 0; i < value.length; i++) {
            if (escape(value.charAt(1)) !== '%0A' || escape(value.charAt(1)) !== '%0D' || escape(value.charAt(1)) !== '%0D%0A') {            //1文字ずつ文字コードをエスケープし、その長さが4文字以上なら全角
              var len = escape(value.charAt(i)).length;
              if (len >= 4) {
                return false;
              }
            }
          }
          return true;
        }
      });

      //文字数チェック
      jQuery.validator.addMethod("length", function(value, element, param) {
        //最初の文字が全角か半角かチェック
        if (escape(value.charAt(1)).length >= 4) {
          return value.length <= param[0];
        } else {
          return value.length <= param[1];
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
        <?= $form_output_array['status'] === 'success' ? '<h3>--form出力--<br>'. $form_output_array['name']. '<br>'. nl2br($form_output_array['msg']). '</h3>' : '' ?>
        <?= $form_output_array['status'] === 'failed' ? 'メッセージの保存が失敗しました。<br>' : '' ?>
        <?= $form_output_array['status'] === 'duplicate' ? '<h2 style="color:red">2重投稿です</h2>' : '' ?>
      </div>

    </div>


    <div class="table-size">
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
      <div class="table-responsive">

        <table class="table table-striped">
          <tr><th>投稿id</th><th>ハンドルネーム</th><th >テキスト</th><th>作成日時</th><th>返信を見る</th><th>画像</th><th>削除</th></tr>
          <? foreach ($records as $post) { ?>
            <tr>
                <td><?= $post['id'] ?></td><td><?= $post['name'] ?></td>
                <td class="col-md-1"><?= nl2br($post['body']) ?></td><td><?= $post['created_at'] ?></td>
                <td><button><a href="reply.php?id=<?= $post['id'] ?>">コメント</button></td>
                <td>
                    <? if (isset($post['image']) && $post['image'] !== '') {?>
                      <img  width="50" height="50" src="/drawImage.php?post_id=<?= $post['id'] ?>">
                    <? } ?>
                </td>
                <td><button><a href="postDelete.php?id=<?= $post['id'] ?>">削除</button></td>
            </tr>
          <? } ?>
        </table>
      </div>

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
