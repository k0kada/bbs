<?php

  session_start();

  require_once 'model/Api.class.php';
  require_once 'vendor/abraham/twitteroauth/autoload.php';
  use Abraham\TwitterOAuth\TwitterOAuth;

  //DBのオブジェクト作成
  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");

  //セッションに入れておいたさっきの配列
  $access_token = $_SESSION['access_token'];
  $tw_api_key = model\Api::getTwitterKey();

  //OAuthトークンとシークレットも使って TwitterOAuth をインスタンス化
  $connection = new TwitterOAuth($tw_api_key['CONSUMER_KEY'], $tw_api_key['CONSUMER_SECRET'], $access_token['oauth_token'], $access_token['oauth_token_secret']);

  //ユーザー情報をGET
  $user = $connection->get("account/verify_credentials");

  $tw_status = checkApiOverlap(htmlspecialchars($access_token['oauth_token']), $mysqli);

  if ($tw_status === 'ok') {

    //現時刻
    $now = date("Y-m-d H:i:s");
    //パスワードはハッシュ化する
    $hashed_pwd = password_hash(htmlspecialchars($access_token['oauth_token']), PASSWORD_DEFAULT);
    //インサート文
    $stmt_ins = $mysqli->prepare("INSERT INTO account (name, password, api_token, created_at) VALUES (?, ?, ?, ?)");
    $stmt_ins->bind_param('ssss', $user->name, $hashed_pwd, htmlspecialchars($access_token['oauth_token']), $now);

    if ($stmt_ins->execute()) {
      $insert_id = $stmt_ins->insert_id;

      $_SESSION["user_id"] = $insert_id;
  	$mysqli->close();

   header('Location: /datawrite.php');
      exit();
    } else {
      $status = "failed";
    }
  }


  $status = '';

  $username = (string) filter_input(INPUT_POST, 'username');
  $password = (string) filter_input(INPUT_POST, 'password');

  if ($username !== '' && $password !== '') {
    $status = checkOverlap($username, $mysqli);

    if ($status === 'ok') {
    
      //現時刻
      $now = date("Y-m-d H:i:s");
      //パスワードはハッシュ化する
      $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);
      //インサート文
      $stmt_ins = $mysqli->prepare("INSERT INTO account (name, password, created_at) VALUES (?, ?, ?)");
      $stmt_ins->bind_param('sss', $username, $hashed_pwd, $now);

      if ($stmt_ins->execute()) {
	    header('Location: ../login.php');
        exit();
      } else {
        $status = "failed";
      }
    }
  }

  /**
   * すでに同じユーザー名が登録されているか確認
   * @param type $username
   * @param type $mysqli
   * @return string
   */
  function checkOverlap($username, $mysqli)
  {
    $stmt_sel = $mysqli->prepare("SELECT * FROM account WHERE name = ?");
    $stmt_sel->bind_param('s', $username);
    $stmt_sel->execute();

    $stmt_sel->store_result();
    
    
    if ($stmt_sel->num_rows < 1) {
      return 'ok';
    } else {
      return 'overlap';
    }
  }

  function checkApiOverlap($token, $mysqli)
  {

    $stmt_sel = $mysqli->prepare("SELECT * FROM account WHERE api_token = ?");
    $stmt_sel->bind_param('s', $token);
    $stmt_sel->execute();

    $result = $stmt_sel->get_result();
    $array = array();
    while ($row = $result->fetch_assoc()) {
      //セッションにユーザ名を保存(ログイン済みかのフラグ)
      $array[] = $row;
    }

    $result->close(); // 結果セットを開放
	$mysqli->close();

    if (count($array) < 1) {
      return 'ok';
    } else {
      $_SESSION["user_id"] = $array[0]['id'];
      return 'overlap';
    }
  }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>新規登録</title>
  </head>
  <body>
    <h1>新規登録</h1>
    <?= $status === 'overlap' ? 'ユーザー名が重複しています<br>' : '' ?>
    <?= $status === 'failed' ? '登録に失敗しました<br>' : '' ?>
    <form method="POST" action="newAccount.php">
      ユーザ名：<input type="text" name="username" />
      パスワード：<input type="password" name="password" />
      <input type="submit" value="作成" />
    </form>

    <a href="../login.php">ログイン</a>

  </body>
</html>
