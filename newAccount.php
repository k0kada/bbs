<?php

  session_start();

  require_once 'model/Api.class.php';
  require_once 'model/Account.class.php';
  require_once 'vendor/autoload.php';

  use Abraham\TwitterOAuth\TwitterOAuth;
  //DBのオブジェクト作成
  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");

  //fbのアカウント新規登録(facebookのuserIdをapi_keyに入れる)
  //FIXME::userIdをパスワードにする以外の方法を考える
  if (isset($_SESSION['facebook_access_token'])) {
    $fb_key = model\Api::getFacebookKey();
    $fb = new Facebook\Facebook($fb_key);
    $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    //ユーザーの情報を取得
    $response = $fb->get('/me');
    $fb_user = $response->getGraphUser();
    //fbのユーザーidから重複ユーザーがないか検索
    $fb_status = model\Account::checkOverlapByApiKey($fb_user->getId(), $mysqli);
    if ($fb_status === 'ok') {
      model\Account::createAccountbyNameApiKey($fb_user->getName(), $fb_user->getId(), $mysqli);
    } elseif ($fb_status === 'overlap') {
      $mysqli->close();
      header('Location: /datawrite/datawrite.php');
      exit();
    }
  }

  //twitterのアカウント新規登録(twitterのアクセストークンを入れる)
  if (isset($_SESSION['access_token'])) {
    //セッションに入れておいたさっきの配列
    $access_token = $_SESSION['access_token'];
    $tw_api_key = model\Api::getTwitterKey(); 
    $tw = new TwitterOAuth($tw_api_key['CONSUMER_KEY'], $tw_api_key['CONSUMER_SECRET'], $access_token['oauth_token'], $access_token['oauth_token_secret']);
    //ユーザー情報をGET
    $tw_user = $tw->get("account/verify_credentials");
    $tw_status = model\Account::checkOverlapByApiKey($access_token['oauth_token'], $mysqli);
  
    if ($tw_status === 'ok') {
      model\Account::createAccountbyNameApiKey($tw_user->name, $access_token['oauth_token'], $mysqli);
    } elseif ($tw_status === 'overlap') {
      $mysqli->close();
      header('Location: /datawrite/datawrite.php');
      exit();
    }
  }

  //通常の新規登録
  $status = '';
  $username = (string) filter_input(INPUT_POST, 'username');
  $password = (string) filter_input(INPUT_POST, 'password');

  if ($username !== '' && $password !== '') {
    $status = model\Account::checkOverlapByName($username, $mysqli);
    if ($status === 'ok') {
      model\Account::createAccountByNamePass($username, $password, $mysqli);
    }
  }

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>新規登録</title>
    <link href="/datawrite/css/bootstrap.min.css" rel="stylesheet">
    <link href="/datawrite/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    <link href="/datawrite/css/signin.css" rel="stylesheet">
  </head>
  <body>
     <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div id="navbar">
          <ul class="nav navbar-nav">
            <li><a href="/datawrite/login.php">ログイン</a></li>
          </ul>
       </div>
      </div>
    </nav>
 
    <div class="container">

      <form class="form-signin" method="POST" action="/datawrite/newAccount.php">
        <h2 class="form-signin-heading">新規登録</h2>
        <?= $status === 'overlap' ? 'ユーザー名が重複しています<br>' : '' ?>
        <?= $status === 'failed' ? '登録に失敗しました<br>' : '' ?>

        <input class="form-control" type="text" name="username" placeholder="ユーザー名" />
        <input class="form-control" type="password" name="password" placeholder="パスワード" />
        <button class="btn btn-lg btn-success btn-block" type="submit">新規登録</button>
      </form>  
    </div>
  </body>
</html>
