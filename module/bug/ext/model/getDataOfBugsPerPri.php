<?php
/**
 * Get report data of bugs per pri
 *
 * @access public
 * @return array
 */
public function getDataOfBugsPerPri()
{
    $datas = $this->dao->select('pri AS name, COUNT(*) AS value')->from(TABLE_BUG)->where($this->reportCondition())->groupBy('name')->orderBy('value DESC')->fetchAll('name');
    if(!$datas) return array();
    //foreach($datas as $status => $data) $data->name = $this->lang->bug->report->bugsPerPri->graph->xAxisName . ':' . $data->name;
    //7178 搜索bug优先级时，显示错误；官方bug；待官方修复后删除
    foreach($datas as $pri => $data)  $data->name = $this->lang->bug->priList[$pri] != '' ? $this->lang->bug->priList[$pri] : $this->lang->report->undefined;
    return $datas;
}