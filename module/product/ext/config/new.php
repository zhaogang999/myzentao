<?php
//1084 产品需求下增加溯源号及搜索;需求搜索新增追溯号字段
$config->product->search['fields']['sourcenote']  = '溯源号';
$config->product->search['params']['sourcenote']  = array('operator' => 'include', 'control' => 'input',  'values' => '');