<?php
function solve($a, $b, $x) {
  $total = (int)$a + (int) $b;
  $over = $total % $x;
  $max_y = $total - $x;

if ($over != 0) {
  $result_number = [];
  for($x_multiple = 1; $x_multiple * $x <= $total; $x_multiple++) {
    $x = $x_multiple * $x;
//    echo $x;
    $max_y = $total - $x;
    for ($i = 1; $i <= $max_y; $i++) {
      if ($max_y % $i === 0) {
          $result_number[] = $i;
      }
  }
}

var_dump(array_unique($result_number));
// echo count(array_unique($result_number));
} else {
  echo -1;
}
echo '<br>';

}
solve(23,42,19);
