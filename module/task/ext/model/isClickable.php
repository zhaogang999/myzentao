<?php
/**
 * Judge an action is clickable or not.
 *
 * @param  object    $task
 * @param  string    $action
 * @access public
 * @return bool
 */
public static function isClickable($task, $action)
{
    $action = strtolower($action);

    if($action == 'assignto') return $task->status != 'closed' and $task->status != 'cancel';
    if($action == 'start')    return $task->status == 'wait';
    if($action == 'restart')  return $task->status == 'pause';
    if($action == 'finish')   return $task->status != 'done'   and $task->status != 'closed' and $task->status != 'cancel';
    if($action == 'close')    return $task->status == 'done'   or  $task->status == 'cancel';
    if($action == 'activate') return $task->status == 'done'   or  $task->status == 'closed'  or $task->status == 'cancel' ;
    if($action == 'cancel')   return $task->status != 'done'   and $task->status != 'closed' and $task->status != 'cancel';
    //未开始任务的详情页增加暂停按钮
    if($action == 'pause')    return $task->status == 'doing' or  $task->status == 'wait';

    return true;
}