<?php

function set_content_range_header($item_type, $items_count, $range=[]) {
  if ($range) {
    $range_start = $range[0];
    $range_end = $range[1];
  } else {
    $range_start = 0;
    $range_end = $items_count < 10 ? $items_count : 10;
  }
  
  header("Content-range: $item_type {$range_start}-{$range_end}/{$items_count}");
}

function parse_params($params_str) {
  $get_params = [];
  mb_parse_str($params_str, $params);
  foreach($params as $key => $param_arr) {
    $data = json_decode($param_arr, true);

    if ($key === "filter") {
      $get_params[$key] = $data;
      continue;
    }

    $get_params[$key] = is_array($data) ? [...$data] : $data;
  }
  return $get_params;
}

function dump($data) {
  echo '<pre>';
  print_r($data);
  echo '</pre>';
}

function dump_all_of_arr($data) {
  foreach($data as $item) {
    dump_of_arr($item);
  }
}

function dump_of_arr($data) {
  $data = $data->export();
  echo '<pre>';
  print_r($data);
  echo '</pre>';
}

function simple_echo($data, $field) {
  echo '======= ' . $field . '========' . '<br>' ;
  foreach($data as $item) {
    echo $item->$field . '<br>'; 
  }
  echo '=====================' . '<br>' ;
}

function bean_to_arr($bean) {
  return $bean-> export();
}

function arr_bean_to_arr($arr_bean) {
  $arr = [];
  foreach($arr_bean as $bean) {
    $arr[] = $bean->export();
  }
  return $arr;
}

function get_put_delete_payload() {
  $raw_data = file_get_contents('php://input');
  $payload = '';
  if ($raw_data) {
    $payload = json_decode($raw_data);
  }
  return $payload;
}

function str_to_cap($s){
  $str = strtolower($s);
  $cap = true;
  
  for($x = 0; $x < strlen($str); $x++){
      $letter = substr($str, $x, 1);
      if($letter == "." || $letter == "!" || $letter == "?"){
          $cap = true;
      }elseif($letter != " " && $cap == true){
          $letter = strtoupper($letter);
          $cap = false;
      }
      
      $ret .= $letter;
  }
  
  return $ret;
}
?>