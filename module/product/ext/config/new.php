<?php
//1084 产品需求下增加溯源号及搜索;需求搜索新增追溯号字段
//优化搜索功能；将追溯号放在搜索框的指定位置
$insert_array = array('sourcenote' =>'溯源号');
$first_array = array_splice ($config->product->search['fields'], 0, 4);
$config->product->search['fields'] = array_merge ($first_array, $insert_array, $config->product->search['fields']);

$config->product->search['params']['sourcenote']  = array('operator' => 'include', 'control' => 'input',  'values' => '');