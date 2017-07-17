<?php
//新增富文本的对应的标签的id
$config->testcase->editor->edit = array('id' => 'comment,expects111,expects112,expects113,expects114,expects115,expects116,expects117,expects118,expects119,expects110', 'tools' => 'simpleTools');
$config->testcase->editor->create = array('id' => 'expects0,expects1,expects2,expects3,expects4,expects5,expects6,expects7,expects8,expects9,expects10', 'tools' => 'simpleTools');

$insert_array = array('story' =>'相关需求');
$first_array = array_splice ($config->testcase->search['fields'], 0, 8);
$config->testcase->search['fields'] = array_merge ($first_array, $insert_array, $config->testcase->search['fields']);

$config->testcase->search['params']['story']  = array('operator' => '=', 'control' => 'select',  'values' => '');