<?php
//禅道任务增加关键字字段；需求：858 在任务搜索条件中增加该字段
$config->project->search['fields']['keywords']        = $lang->task->keywords;
$config->project->search['params']['keywords']          = array('operator' => 'include', 'control' => 'input',  'values' => '');