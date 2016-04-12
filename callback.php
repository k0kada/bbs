<?php


session_start();

  require_once 'model/Api.class.php';
require_once 'vendor/abraham/twitteroauth/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

//login.phpでセットしたセッション
$request_token = [];  // [] は array() の短縮記法。詳しくは以下の「追々記」参照
$request_token['oauth_token'] = $_SESSION['oauth_token'];
$request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

$get_oauth_token = (string) filter_input(INPUT_GET, 'oauth_token');
$get_oauth_verifier = (string) filter_input(INPUT_GET, 'oauth_verifier');




//Twitterから返されたOAuthトークンと、あらかじめlogin.phpで入れておいたセッション上のものと一致するかをチェック
if ($get_oauth_token !== '' && $request_token['oauth_token'] !== $get_oauth_token) {
    die( 'Error!' );
}

  $tw_api_key = model\Api::getTwitterKey();


//OAuth トークンも用いて TwitterOAuth をインスタンス化
$connection = new TwitterOAuth($tw_api_key['CONSUMER_KEY'], $tw_api_key['CONSUMER_SECRET'], $request_token['oauth_token'], $request_token['oauth_token_secret']);

//アプリでは、access_token(配列になっています)をうまく使って、Twitter上のアカウントを操作していきます
$_SESSION['access_token'] = $connection->oauth("oauth/access_token", array("oauth_verifier" => $get_oauth_verifier));

$user = $connection->get("account/verify_credentials");

//セッションIDをリジェネレート
session_regenerate_id();

//マイページへリダイレクト
header('location: /newAccount.php');