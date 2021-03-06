<?php

  session_start();

  if (is_null($_SESSION["user_id"])) {
    header('Location: /datawrite/login.php/');
    exit();
  }

  require_once 'model/Bbs.class.php';

  $post_id = (string) filter_input(INPUT_GET, 'post_id');

  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");
  $post_record = model\Bbs::getPostById($post_id, $mysqli);
  if (isset($post_record[0]['image'])) {
    header('Content-type: '. $post_record[0]['img_mime']);
    echo $post_record[0]['image'];
  }
  exit();

