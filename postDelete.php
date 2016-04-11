<?php

  session_start();

  if (is_null($_SESSION["user_id"])) {
    header('Location: ./login.php/');
    exit();
  }
  $post_id = (string) filter_input(INPUT_GET, 'id');

  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");
  updateDeletePost($post_id, $mysqli);

  function updateDeletePost($post_id, $mysqli)
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