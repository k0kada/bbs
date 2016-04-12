<?php

  require_once 'model/Api.class.php';
  require_once 'model/Account.class.php';
  require_once 'vendor/autoload.php';
  use Abraham\TwitterOAuth\TwitterOAuth;

    //セッション開始
  session_start();

  //facebook
  $fb_api_key = model\Api::getFacebookKey();
  $fb = new Facebook\Facebook($fb_api_key);
  $fb_url = model\Account::getFacebookUrl($fb);

  //twitter
  $tw_api_key = model\Api::getTwitterKey();
  $tw = new TwitterOAuth($tw_api_key['CONSUMER_KEY'], $tw_api_key['CONSUMER_SECRET']);
  $tw_url = model\Account::getTwitterUrl($tw, $tw_api_key);

  //DBのオブジェクト作成
  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");
  //ログイン状態(logged_in,failed,success)
  $status = '';

  $username = (string) filter_input(INPUT_POST, 'username');
  $password = (string) filter_input(INPUT_POST, 'password');

  //セッションにセットされていたらログイン済み
  if (isset($_SESSION["user_id"])) {
    $status = "logged_in";
  } elseif ($username !== '' && $password !== '') {

    $stmt = $mysqli->prepare("SELECT * FROM account WHERE name = ?");
    $stmt->bind_param('s', htmlspecialchars($username));
    $stmt->execute();
    $result = $stmt->get_result();

    $status = 'failed';

    //ユーザー名が一致するレコードが存在したら
    if ($result->num_rows > 0) {
      // 連想配列で回す
      while ($row = $result->fetch_assoc()) {
        //DBに保存してあるハッシュ済みパスワードを取り出す
        $db_hashed_pwd = $row['password'];
        //入力されたパスと、ハッシュ済みパスが一致したら
        if (password_verify($password, $db_hashed_pwd)) {
          $status = "success";

          //セッションにユーザ名を保存(ログイン済みかのフラグ)
          $_SESSION["user_id"] = $row['id'];
          break;
        }
      }
    }

    // 結果セットを閉じる
    $result->close();
    $mysqli->close();
  }

  //ログインが成功していたらリダイレクト
  if ($status === 'logged_in' || $status === 'success') {
	header('Location: /datawrite.php');
    exit();
  }

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ログイン</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    <link href="/css/signin.css" rel="stylesheet">

  </head>
  <body>
    <div class="container">
      <form class="form-signin" method="POST" action="/login.php">
        <h2 class="form-signin-heading">ログイン</h2>
        <?= $status === 'failed' ? 'ログインできません' : '' ?>

        <input class="form-control" type="text" name="username" placeholder="ユーザー名" />
        <input class="form-control" type="password" name="password" placeholder="パスワード" />
        <button class="btn btn-lg btn-success btn-block" type="submit">ログイン</button>
      </form>

        <a href="<?= $fb_url ?>"><button class="btn btn-primary btn-block">Facebookでログイン</button></a>
        <a href="<?= $tw_url ?>"><button class="btn btn-info btn-block">twitterでログイン</button></a>
        <a href="/newAccount.php"><button class="btn btn-danger btn-block">新規登録</button></a>

    </div>
  </body>
</html>