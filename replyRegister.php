<?php

  session_start();

  $post_id = (string) filter_input(INPUT_POST, 'post_id');
  $msg = (string) filter_input(INPUT_POST, 'body');
  $checked_msg = htmlspecialchars($msg);

  //ポストされたワンタイムチケット
  $post_ticket = (string) filter_input(INPUT_POST, 'ticket');
  //セッションのワンタイムチケット
  $session_ticket = isset($_SESSION['ticket']) ? $_SESSION['ticket'] : '';
  //ブラウザバック対策
  unset($_SESSION['ticket']);

  $status = insertFormValue($post_id, $checked_msg, $post_ticket, $session_ticket);

  /**
   * フォームの入力をDBに保存する
   * @param type $checked_name
   * @param type $checked_msg
   * @return string
   */
  function insertFormValue($post_id, $checked_msg, $post_ticket, $session_ticket)
  {
    $ticket_flag = getTicketFlag($post_ticket, $session_ticket);

    if (!$ticket_flag) {
      return 'duplicate';
    }

    $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");

    $now = date("Y-m-d H:i:s");

    $stmt = $mysqli->prepare("INSERT INTO reply (post_id, user_id, body, created_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iiss', $post_id, $_SESSION["user_id"],  $checked_msg, $now);

    if ($stmt->execute()) {
      return 'success';
    }
    return 'failed';
  }

  function getTicketFlag($post_ticket, $session_ticket)
  {
    $flag = true;
    if ($post_ticket === '' || $post_ticket != $session_ticket) {
      $flag =  false;
    }
    return $flag;
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
    <form id="msgForm" method="POST" action="reply.php?id=<?= $post_id ?>">
      <input type="hidden"name="body" value="<?= $checked_msg ?>"/>
      <input type="hidden"name="status" value="<?= $status ?>"/>
    </form>
  </body>

</html>