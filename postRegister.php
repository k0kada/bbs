<?php

  require_once 'model/Bbs.class.php';

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

  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");
  $status = model\Bbs::insertFormValue($checked_name, $checked_msg, $image, $post_ticket, $session_ticket, $mysqli);
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
    <form id="msgForm" method="POST" action="/datawrite/datawrite.php">
      <input type="hidden" name="name" value="<?= $checked_name ?>"/>
      <input type="hidden"name="body" value="<?= $checked_msg ?>"/>
      <input type="hidden"name="status" value="<?= $status ?>"/>
    </form>
  </body>

</html>