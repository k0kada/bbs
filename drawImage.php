<?php
  $post_id = (string) filter_input(INPUT_GET, 'post_id');

  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");
  $post_array = getPost($post_id, $mysqli);
  if (isset($post_array[0]['image'])) {
    header('Content-type: '. $post_array[0]['img_mime']);
    echo $post_array[0]['image'];
  }
  exit();

  function getPost($post_id, $mysqli)
  {
    $sql = "SELECT * FROM post WHERE id = ". $post_id;
    $result = $mysqli->query($sql);
    $array = array();
    while ($row = $result->fetch_assoc()) {
      //セッションにユーザ名を保存(ログイン済みかのフラグ)
      $array[] = $row;
    }
    $result->close();
    return $array;
  }

