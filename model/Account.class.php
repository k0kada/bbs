<?php
namespace model;

class Account {

  /**
   * idから名前を取ってくる
   * @param type $user_id
   * @param type $mysqli
   * @return type
   */
  public static function getNameById($user_id, $mysqli)
  {
    
    $sql = "SELECT * FROM account WHERE id = '" . $user_id . "'";
    $result = $mysqli->query($sql);

    //ユーザー名が一致するレコードが存在したら
    if ($result) {
      $row = $result->fetch_assoc();
    }

    // 結果セットを閉じる
    $result->close();

    return isset($row) ? $row['name'] : null;
  }

  public static function getFacebookUrl($fb)
  {
    $helper = $fb->getRedirectLoginHelper();
    //オプション
    $permissions = ['email'];
    //コールバック
    $url = 'http://ko-okada.net/callback.php';
    $loginUrl = $helper->getLoginUrl($url, $permissions);
    return $loginUrl;
  }

  public static function getTwitterUrl($tw, $tw_api_key)
  {
    //コールバックURLをここでセット
    $request_token = $tw->oauth('oauth/request_token', array('oauth_callback' => $tw_api_key['OAUTH_CALLBACK']));
    //callbackで使うのでセッションに入れる
    $_SESSION['oauth_token'] = $request_token['oauth_token'];
    $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
    //Twitter.com 上の認証画面のURLを取得( この行についてはコメント欄も参照 )
    $loginUrl = $tw->url('oauth/authenticate', array('oauth_token' => $request_token['oauth_token']));
    return $loginUrl;
  }

  public static function checkOverlapByApiKey($api_key, $mysqli)
  {
    $stmt_sel = $mysqli->prepare("SELECT * FROM account WHERE api_key = ?");
    $stmt_sel->bind_param('s', htmlspecialchars($api_key));
    $stmt_sel->execute();

    $result = $stmt_sel->get_result();
    $records = array();
    while ($row = $result->fetch_assoc()) {
      //セッションにユーザ名を保存(ログイン済みかのフラグ)
      $records[] = $row;
    }
    //結果セットを開放
    $result->close();

    if (count($records) < 1) {
      return 'ok';
    } else {
      $_SESSION["user_id"] = $records[0]['id'];
      return 'overlap';
    }
  }

  public static function checkOverlapByName($name, $mysqli)
  {
    $stmt_sel = $mysqli->prepare("SELECT * FROM account WHERE name = ?");
    $stmt_sel->bind_param('s', $name);
    $stmt_sel->execute();

    $stmt_sel->store_result();
    if ($stmt_sel->num_rows < 1) {
      return 'ok';
    } else {
      return 'overlap';
    }
  }

  public static function createAccountbyNameApiKey($name, $api_key, $mysqli)
  {
    //現時刻
    $now = date("Y-m-d H:i:s");
    //パスワードはハッシュ化する
    $hashed_pwd = password_hash(htmlspecialchars($api_key), PASSWORD_DEFAULT);

    //インサート文
    $stmt = $mysqli->prepare("INSERT INTO account (name, password, api_key, created_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $name, $hashed_pwd, htmlspecialchars($api_key), $now);

    if ($stmt->execute()) {
      $insert_id = $stmt->insert_id;
      //セッションにAccountIdをセット
      $_SESSION["user_id"] = $insert_id;
      $mysqli->close();

      header('Location: /datawrite.php');
      exit();
    } else {
      $status = "failed";
    }
    return $status;
  }

  public static function createAccountByNamePass($name, $pass, $mysqli)
  {  
    //現時刻
    $now = date("Y-m-d H:i:s");
    //パスワードはハッシュ化する
    $hashed_pwd = password_hash($pass, PASSWORD_DEFAULT);
    //インサート文
    $stmt = $mysqli->prepare("INSERT INTO account (name, password, created_at) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $name, $hashed_pwd, $now);

    if ($stmt->execute()) {
      $insert_id = $stmt->insert_id;
      //セッションにAccountIdをセット
      $_SESSION["user_id"] = $insert_id;
      $mysqli->close();
	  header('Location: ../login.php');
      exit();
    } else {
      $status = "failed";
    }
    return $status;
  }
}
