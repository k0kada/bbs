<?php

  session_start();

  require_once 'model/Api.class.php';
  require_once 'vendor/abraham/twitteroauth/autoload.php';
  require_once 'vendor/autoload.php';

  use Abraham\TwitterOAuth\TwitterOAuth;

  $get_oauth_token = (string) filter_input(INPUT_GET, 'oauth_token');
  $get_oauth_verifier = (string) filter_input(INPUT_GET, 'oauth_verifier');
  //ここからtwitterコールバック
  if ($get_oauth_token !== '' && $get_oauth_verifier !== '') {

    //login.phpでセットしたセッション
    $request_token = [];
    $request_token['oauth_token'] = $_SESSION['oauth_token'];
    $request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

    //Twitterから返されたOAuthトークンと、あらかじめlogin.phpで入れておいたセッション上のものと一致するかをチェック
    if ($get_oauth_token !== '' && $request_token['oauth_token'] !== $get_oauth_token) {
       header('location: /datawrite/login.php');
       exit();
    }
    try {
      $tw_api_key = model\Api::getTwitterKey();
      //OAuth トークンも用いて TwitterOAuth をインスタンス化
      $connection = new TwitterOAuth($tw_api_key['CONSUMER_KEY'], $tw_api_key['CONSUMER_SECRET'], $request_token['oauth_token'], $request_token['oauth_token_secret']);
      //セッションに保存
      $_SESSION['access_token'] = $connection->oauth("oauth/access_token", array("oauth_verifier" => $get_oauth_verifier));
      $user = $connection->get("account/verify_credentials");
      //アカウント登録ページへ
      header('location: /datawrite/newAccount.php');
      exit();
    } catch (Exception $exc) {
      //ログインページへリダイレクト
      header('location: /datawrite/login.php');
      exit();
    }
  }

  $code = (string) filter_input(INPUT_GET, 'code');
  if ($code !== '') {
    //ここからFacebookのコールバック
    try {
      $fb_key = model\Api::getFacebookKey();
      $fb = new Facebook\Facebook($fb_key);
      $helper = $fb->getRedirectLoginHelper();
      $accessToken = $helper->getAccessToken();
      //セッションに保存
      $_SESSION['facebook_access_token'] = (string) $accessToken;
      //アカウント登録ページへ
      header('location: /datawrite/newAccount.php');
      exit();
    } catch(Exception $exc) {
      //エラーを返した場合
      header('location: /datawrite/login.php');
      exit();
    }
  }

  $_SESSION = array();
  session_destroy();

  header('location: /datawrite/login.php');
  exit();
