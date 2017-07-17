<?php
/* 模板。*/
$lang->bug->tplReason   = "<p>[问题原因]</p>\n";
$lang->bug->tplProject = "<p>[解决方案]</p>\n";
$lang->bug->tplInfluence = "<p>[影响范围]</p>\n";
$lang->bug->tplExpectedSolutionVersion = "<p>[预计解决版本]</p>";

$lang->bug->tplVerificationResults = "<p>[验证结果]</p>";
$lang->bug->tplVerificationVersion = "<p>[验证版本]</p>";
$lang->bug->tplVerificationContent = "<p>[验证内容]</p>";

//优化搜索功能搜索条件增加空选项
$lang->bug->confirmedList[''] = '';
$lang->bug->severityList[''] = '';

//新增bug截止日期统计
$lang->bug->report->charts['bugsDeadline']              = 'Bug截止日期统计';
$lang->bug->report->bugsDeadline                        = new stdclass();
$lang->bug->report->bugsDeadline->graph                 = new stdclass();
$lang->bug->report->bugsDeadline->type                  = 'bar';
$lang->bug->report->bugsDeadline->graph->xAxisName     = '日期';
