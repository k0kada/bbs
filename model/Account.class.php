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
}
