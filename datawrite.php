<?php

  $file_name = dirname(__FILE__). '/data/data.txt';
  $input_word = 'hoge';

  // ファイルの存在確認
  if (file_exists($file_name)) {
    echo 'すでにファイルが存在しているので上書きします<br>';
  } else {
    echo '新規に作成します<br>';
  }

  //ファイルポインタをファイルの先頭に置く(上書き)
  $fopen = fopen($file_name, 'w');

  //ファイルをロック
  if (flock($fopen, LOCK_EX)){
    //書き出し
    if (fwrite($fopen,  $input_word)){
      echo $input_word. 'を'. $file_name. 'に書き込みました<br>';
    } else {
      echo 'ファイル書き込みに失敗しました<br>';
    }
    flock($fopen, LOCK_UN);
  } else {
    echo '誰かが同時に書き込もうとして失敗しました<br>';
  }

  fclose($fopen);
