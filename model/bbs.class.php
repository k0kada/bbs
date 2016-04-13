<?php

namespace model;

class bbs {
  public $mysqli; // mysqliオブジェクト

  public static function getPage($page, $order, $mysqli)
  {
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM post ORDER BY id ". $order. " LIMIT " . $offset. ", ". $limit;
    $result = $mysqli->query($sql);

    $limited_array = array();
    while ($row = $result->fetch_assoc()) {
      //セッションにユーザ名を保存(ログイン済みかのフラグ)
      $limited_array[] = $row;
    }
    $result->close();

    return $limited_array;
  }
}
