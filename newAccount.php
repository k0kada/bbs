<?php

  //DBのオブジェクト作成
  $mysqli = new mysqli("localhost", "okada", "kokada", "datawrite");

  $status = '';

  $username = (string) filter_input(INPUT_POST, 'username');
  $password = (string) filter_input(INPUT_POST, 'password');

  if ($username !== '' && $password !== '') {
    $status = checkOverlap($username, $mysqli);

    if ($status === 'ok') {
    
      //現時刻
      $now =date("Y-m-d H:i:s");
      //パスワードはハッシュ化する
      $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);
      //インサート文
      $stmt_ins = $mysqli->prepare("INSERT INTO account (name, password, created_at) VALUES (?, ?, ?)");
      $stmt_ins->bind_param('sss', $username, $hashed_pwd, $now);
    
      if ($stmt_ins->execute()) {
	    header('Location: ./login.php');
        exit();
      } else {
        $status = "failed";
      }
    }
  }

  /**
   * すでに同じユーザー名が登録されているか確認
   * @param type $username
   * @param type $mysqli
   * @return string
   */
  function checkOverlap($username, $mysqli)
  {
    $stmt_sel = $mysqli->prepare("SELECT * FROM account WHERE name = ?");
    $stmt_sel->bind_param('s', $username);
    $stmt_sel->execute();

    $stmt_sel->store_result();
    
    
    if ($stmt_sel->num_rows < 1) {
      return 'ok';
    } else {
      return 'overlap';
    }
  }

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>新規登録</title>
  </head>
  <body>
    <h1>新規登録</h1>
    <?= $status === 'overlap' ? 'ユーザー名が重複しています<br>' : '' ?>
    <?= $status === 'failed' ? '登録に失敗しました<br>' : '' ?>
    <form method="POST" action="newAccount.php">
      ユーザ名：<input type="text" name="username" />
      パスワード：<input type="password" name="password" />
      <input type="submit" value="作成" />
    </form>

    <a href="../login.php">ログイン</a>

  </body>
</html>
