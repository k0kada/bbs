<?php

  session_start();
  require_once 'model/Bbs.class.php';

  if (is_null($_SESSION["user_id"])) {
    header('Location: /login.php/');
    exit();
  }
  $post_id = (string) filter_input(INPUT_GET, 'id');

  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");
  $status = model\Bbs::deletePost($post_id, $mysqli);

  header('Location: /datawrite.php/');
  exit();