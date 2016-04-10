<?php

  session_start();

  $name = (string) filter_input(INPUT_POST, 'name');
  $msg = (string) filter_input(INPUT_POST, 'body');
  $checked_name = htmlspecialchars($name);
  $checked_msg = htmlspecialchars($msg);

  $image = isset($_FILES["image"]) ? $_FILES["image"] : null;

  //ポストされたワンタイムチケット
  $post_ticket = (string) filter_input(INPUT_POST, 'ticket');
  //セッションのワンタイムチケット
  $session_ticket = isset($_SESSION['ticket']) ? $_SESSION['ticket'] : '';
  //ブラウザバック対策
  unset($_SESSION['ticket']);

  $status = insertFormValue($checked_name, $checked_msg, $image, $post_ticket, $session_ticket);

  /**
   * フォームの入力をDBに保存する
   * @param type $checked_name
   * @param type $checked_msg
   * @return string
   */
  function insertFormValue($checked_name, $checked_msg, $image, $post_ticket, $session_ticket)
  {
    $ticket_flag = getTicketFlag($post_ticket, $session_ticket);

    if (!$ticket_flag) {
      return 'duplicate';
    }
    
    //入力チェック
    $insert_flag = checkInputFlag($checked_name, $checked_msg, $image);
    if (!$insert_flag) {
      return '';
    } else {
      $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");

      $now = date("Y-m-d H:i:s");
      $img_bin = file_get_contents($image['tmp_name']);

      $stmt = $mysqli->prepare("INSERT INTO post (user_id, name, body, img_name, img_mime, image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param('issssss', $_SESSION["user_id"], $checked_name, $checked_msg, $image['name'], $image['type'], $img_bin, $now);

      if ($stmt->execute()) {
        return 'success';
      }
      return 'failed';
    }
  }

  function getTicketFlag($post_ticket, $session_ticket)
  {
    $flag = true;
    if ($post_ticket === '' || $post_ticket != $session_ticket) {
      $flag =  false;
    }
    return $flag;
  }

  /**
   * 入力チェック
   * @param type $checked_name
   * @param type $checked_msg
   * @return type
   */
  function checkInputFlag($checked_name, $checked_msg)
  {
    $status = 0;
    //名前入力チェック(判定falseなら0、trueなら+1)
    switch ($checked_name) {
      case '':
        break;
      case strlen($checked_name) > 30:
        break;
      default :
        $status += 1;
        break;
    }

    //msg入力チェック(判定falseなら0、trueなら+1)
    switch ($checked_msg) {
      case '':
          break;
      case strlen($checked_name) > 600:
          break;
      default :
        $status += 1;
        break;
    }

    return $status == 2 ? true : false;
  }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <script type="text/javascript" src="js/jquery-2.2.3.min.js"></script>
    <script>
      $(function () {
        $('#msgForm').submit();
      });
    </script>
  </head>
  <body>
    <form id="msgForm" method="POST" action="datawrite.php">
      <input type="hidden" name="name" value="<?= $checked_name ?>"/>
      <input type="hidden"name="body" value="<?= $checked_msg ?>"/>
      <input type="hidden"name="status" value="<?= $status ?>"/>
    </form>
  </body>

</html>