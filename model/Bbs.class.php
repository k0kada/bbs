<?php

namespace model;

class Bbs {

  public static function getPage($page, $order, $mysqli)
  {
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM post WHERE delete_flag = 0 ORDER BY id ". $order. " LIMIT " . $offset. ", ". $limit;
    $result = $mysqli->query($sql);

    $limited_array = array();
    while ($row = $result->fetch_assoc()) {
      //セッションにユーザ名を保存(ログイン済みかのフラグ)
      $limited_array[] = $row;
    }
    $result->close();

    return $limited_array;
  }

  public static function getMaxPage($mysqli)
  {
    $limit = 10;

    $sql = "SELECT id FROM post WHERE delete_flag = 0";
    $result = $mysqli->query($sql);
    $num_rows = $result->num_rows;
    //小数点切り上げ
    $max_page = ceil($num_rows / $limit);
    $result->close();

    return (int) $max_page;
  }

  public static function getFormOutput()
  {
    $result = [];
    $name = (string) filter_input(INPUT_POST, 'name');  
    $msg = (string) filter_input(INPUT_POST, 'body');
    $status = (string) filter_input(INPUT_POST, 'status');
  
    $checked_name = htmlspecialchars($name);
    $checked_msg = htmlspecialchars($msg);
    $result['name'] = $checked_name;
    $result['msg'] = $checked_msg;
    $result['status'] = $status;
    
    return $result;
  }
}
