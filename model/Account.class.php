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
}
