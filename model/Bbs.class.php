<?php

namespace model;

class Bbs {

  public static function getPageByLimit($page, $order, $mysqli)
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

  public static function getPostById($post_id, $mysqli)
  {
    $delete_flag = false;
    $stmt = $mysqli->prepare("SELECT * FROM post WHERE  delete_flag = ? AND id = ?");
    $stmt->bind_param('ii',  $delete_flag, $post_id);
    $records = [];

    if ($stmt->execute()) { 
      $result = $stmt->get_result();;
      while ($row = $result->fetch_assoc()) {
        $records[] = $row;
      }
      $result->close();
    }
    return $records;
  }

  public static function getRepliesByPostId($post_id, $mysqli)
  {
    $stmt = $mysqli->prepare("SELECT * FROM reply WHERE post_id = ?");
    $stmt->bind_param('i', $post_id);
    $records = [];

    if ($stmt->execute()) { 
      $result = $stmt->get_result();;
      while ($row = $result->fetch_assoc()) {
        $records[] = $row;
      }
      $result->close();
    }
    return $records;
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

  public static function insertFormValue($checked_name, $checked_msg, $image, $post_ticket, $session_ticket, $mysqli)
  {
    $ticket_flag = self::getTicketFlag($post_ticket, $session_ticket);

    if (!$ticket_flag) {
      return 'duplicate';
    }
    
    //入力チェック
    $insert_flag = self::checkInputFlag($checked_name, $checked_msg, $image);
    if (!$insert_flag) {
      return 'failed';
    } else {
      $now = date("Y-m-d H:i:s");
      $img_name = isset($image['name']) && $image['name'] !== '' ? $image['name'] : null;
      $img_type = isset($image['type']) && $image['type'] !== '' ? $image['type'] : null;
      $img_bin = isset($image['tmp_name']) && $image['tmp_name'] !== '' ? file_get_contents($image['tmp_name']) : null;


      $stmt = $mysqli->prepare("INSERT INTO post (user_id, name, body, img_name, img_mime, image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param('issssss', $_SESSION["user_id"], $checked_name, $checked_msg, $img_name, $img_type, $img_bin, $now);

      if ($stmt->execute()) {
        return 'success';
      }
      return 'failed';
    }
  }

  private static function getTicketFlag($post_ticket, $session_ticket)
  {
    $flag = true;
    if ($post_ticket === '' || $post_ticket != $session_ticket) {
      $flag =  false;
    }
    return $flag;
  }

  private static function checkInputFlag($name, $msg)
  {
    $status = 0;
    //名前チェック
    $name_width =  self::checkWordWidth($name);
    if ($name_width === 'zenkaku' && mb_strlen($name) <= 15) {
      $status += 1;
    } elseif ($name_width === 'hankaku' && mb_strlen($name) <= 30) {
      $status += 1;
    }

    //msgチェック
    $msg_width =  self::checkWordWidth($msg);
    if ($msg_width === 'zenkaku' && mb_strlen($msg) <= 300) {
      $status += 1;
    } elseif ($msg_width === 'hankaku' && mb_strlen($msg) <= 600) {
      $status += 1;
    }

    return $status == 2 ? true : false;
  }


  private static function checkWordWidth($text)
  {
    $status = 'failed';
    //全角チェック
    //改行などは消す
    $trim_text = preg_replace('/(?:\n|\r|\r\n)/', '', $text);
    $zenkaku_len = strlen($trim_text);
    //UTF-8の場合は全角を3文字カウントするので「* 3」にする
    $zenkaku = mb_strlen($trim_text, "UTF-8") * 3;

    if($zenkaku_len === $zenkaku) {
      $status = 'zenkaku';
    }

    $hankaku_len = strlen($text);
    $hankaku = mb_strlen($text, "UTF-8");

    if ($hankaku_len === $hankaku) {
      $status = 'hankaku';
    }
    return $status;
  }

  public static function deletePost($post_id, $mysqli)
  {
    $now = date("Y-m-d H:i:s");

    $delete_flag = true;
    $stmt = $mysqli->prepare("UPDATE post SET delete_flag = ?, updated_at = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param('isii', $delete_flag, $now, $post_id, $_SESSION["user_id"]);

    if ($stmt->execute()) {
      return 'success';
    }
    return 'failed';
  }
}
