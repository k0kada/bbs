<?php

  require_once 'model/Api.class.php';
  require_once 'vendor/abraham/twitteroauth/autoload.php';
  require_once 'vendor/autoload.php';
  use Abraham\TwitterOAuth\TwitterOAuth;

    //セッション開始
  session_start();

$fb_key = model\Api::getFacebookKey();
$fb = new Facebook\Facebook($fb_key);

$helper = $fb->getRedirectLoginHelper();
$permissions = ['email']; // optional
$url = 'http://ko-okada.net/callback.php';
$loginUrl = $helper->getLoginUrl($url, $permissions);

echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';

  
  
  $tw_api_key = model\Api::getTwitterKey();

  //TwitterOAuth をインスタンス化
  $connection = new TwitterOAuth($tw_api_key['CONSUMER_KEY'], $tw_api_key['CONSUMER_SECRET']);

  //コールバックURLをここでセット
  $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $tw_api_key['OAUTH_CALLBACK']));

  //callback.phpで使うのでセッションに入れる
  $_SESSION['oauth_token'] = $request_token['oauth_token'];
  $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

  //Twitter.com 上の認証画面のURLを取得( この行についてはコメント欄も参照 )
  $tw_url = $connection->url('oauth/authenticate', array('oauth_token' => $request_token['oauth_token']));


  //DBのオブジェクト作成
  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");

  //ログイン状態
  $status = '';

  $username = (string) filter_input(INPUT_POST, 'username');
  $password = (string) filter_input(INPUT_POST, 'password');

  //セッションにセットされていたらログイン済み
  if (isset($_SESSION["user_id"])) {
    $status = "logged_in";
  } elseif ($username !== '' && $password !== '') {
    $sql = "SELECT * FROM account WHERE name = '" . $username . "'";
    $result = $mysqli->query($sql);

    $status = 'failed';

    //ユーザー名が一致するレコードが存在したら
    if ($result) {
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
  }

  //ログインが成功していたらリダイレクト
  if ($status === 'logged_in' || $status === 'success') {
	header('Location: ../datawrite.php');
    exit();
  }

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>ログイン</title>
  </head>
  <body>
    <h1>ログイン</h1>
    <?= $status === 'failed' ? 'ログインできません' : '' ?>
    <form method="POST" action="login.php">
      ユーザ名：<input type="text" name="username" />
      パスワード：<input type="password" name="password" />
      <input type="submit" value="ログイン" />
    </form>
    <a href="<?= $tw_url ?>">twitterでログイン</a><br>
    <a href="../newAccount.php">新規登録</a>

  </body>
</html>