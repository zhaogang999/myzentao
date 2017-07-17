<?php
/**
 * Batch update task.
 *
 * @access public
 * @return void
 */
public function batchUpdate()
{
    $tasks      = array();
    $allChanges = array();
    $now        = helper::now();
    $today      = date(DT_DATE1);
    $data       = fixer::input('post')->get();
    $taskIDList = $this->post->taskIDList;

    /* Process data if the value is 'ditto'. */
    foreach($taskIDList as $taskID)
    {
        if(isset($data->modules[$taskID]) and ($data->modules[$taskID] == 'ditto')) $data->modules[$taskID] = isset($prev['module']) ? $prev['module'] : 0;
        if($data->types[$taskID]       == 'ditto') $data->types[$taskID]       = isset($prev['type'])       ? $prev['type']       : '';
        if($data->statuses[$taskID]    == 'ditto') $data->statuses[$taskID]    = isset($prev['status'])     ? $prev['status']     : '';
        if($data->assignedTos[$taskID] == 'ditto') $data->assignedTos[$taskID] = isset($prev['assignedTo']) ? $prev['assignedTo'] : '';
        if($data->pris[$taskID]        == 'ditto') $data->pris[$taskID]        = isset($prev['pri'])        ? $prev['pri']        : 0;
        if($data->finishedBys[$taskID] == 'ditto') $data->finishedBys[$taskID] = isset($prev['finishedBy']) ? $prev['finishedBy'] : '';
        if($data->canceledBys[$taskID] == 'ditto') $data->canceledBys[$taskID] = isset($prev['canceledBy']) ? $prev['canceledBy'] : '';
        if($data->closedBys[$taskID]   == 'ditto') $data->closedBys[$taskID]   = isset($prev['closedBy'])   ? $prev['closedBy']   : '';

        $prev['module']     = $data->modules[$taskID];
        $prev['type']       = $data->types[$taskID];
        $prev['status']     = $data->statuses[$taskID];
        $prev['assignedTo'] = $data->assignedTos[$taskID];
        $prev['pri']        = $data->pris[$taskID];
        $prev['finishedBy'] = $data->finishedBys[$taskID];
        $prev['canceledBy'] = $data->canceledBys[$taskID];
        $prev['closedBy']   = $data->closedBys[$taskID];
    }

    /* Initialize tasks from the post data.*/
    foreach($taskIDList as $taskID)
    {
        $oldTask = $this->getById($taskID);

        $task = new stdclass();
        $task->color          = $data->colors[$taskID];
        $task->name           = $data->names[$taskID];
        $task->module         = isset($data->modules[$taskID]) ? $data->modules[$taskID] : 0;
        $task->type           = $data->types[$taskID];
        $task->status         = $data->statuses[$taskID];
        $task->assignedTo     = $task->status == 'closed' ? 'closed' : $data->assignedTos[$taskID];
        $task->pri            = $data->pris[$taskID];
        $task->estimate       = $data->estimates[$taskID];
        $task->left           = $data->lefts[$taskID];
        $task->estStarted     = $data->estStarteds[$taskID];
        $task->deadline       = $data->deadlines[$taskID];
        $task->finishedBy     = $data->finishedBys[$taskID];
        $task->canceledBy     = $data->canceledBys[$taskID];
        $task->closedBy       = $data->closedBys[$taskID];
        $task->closedReason   = $data->closedReasons[$taskID];
        $task->assignedDate   = $oldTask->assignedTo ==$task->assignedTo  ? $oldTask->assignedDate : $now;
        $task->finishedDate   = $oldTask->finishedBy == $task->finishedBy ? $oldTask->finishedDate : $now;
        $task->canceledDate   = $oldTask->canceledBy == $task->canceledBy ? $oldTask->canceledDate : $now;
        $task->closedDate     = $oldTask->closedBy == $task->closedBy ? $oldTask->closedDate : $now;
        $task->lastEditedBy   = $this->app->user->account;
        $task->lastEditedDate = $now;
        $task->consumed       = $oldTask->consumed;
        //禅道任务增加关键字字段；需求：858 批量添加任务，批量编辑任务增加关键字字段;行：67-68
        $task->keywords       = $data->keywords[$taskID];
        //需求1340 任务点击完成时，开启时间和完成时间改为必填项。
        $task->realStarted    = $data->realStarted[$taskID];

        if($data->consumeds[$taskID])
        {
            if($data->consumeds[$taskID] < 0)
            {
                echo js::alert(sprintf($this->lang->task->error->consumed, $taskID));
            }
            else
            {
                $record = new stdclass();
                $record->account  = $this->app->user->account;
                $record->task     = $taskID;
                $record->date     = $today;
                $record->left     = $task->left;
                $record->consumed = $data->consumeds[$taskID];
                $this->addTaskEstimate($record);

                $task->consumed = $oldTask->consumed + $record->consumed;
            }
        }

        switch($task->status)
        {
            case 'done':
            {
                //需求1340 任务点击完成时，开启时间和完成时间改为必填项。
                if ($task->type == 'review') die(js::error($this->lang->task->error->reviewError));
                if ($task->realStarted =='0000-00-00') die(js::error($this->lang->task->error->doneError));
                
                $task->left = 0;
                if(!$task->finishedBy)   $task->finishedBy = $this->app->user->account;
                if($task->closedReason)  $task->closedDate = $now;
                $task->finishedDate = $oldTask->status == 'done' ?  $oldTask->finishedDate : $now;
            }
                break;
            case 'cancel':
            {
                $task->assignedTo   = $oldTask->openedBy;
                $task->assignedDate = $now;

                if(!$task->canceledBy)   $task->canceledBy   = $this->app->user->account;
                if(!$task->canceledDate) $task->canceledDate = $now;
            }
                break;
            case 'closed':
            {
                if(!$task->closedBy)   $task->closedBy   = $this->app->user->account;
                if(!$task->closedDate) $task->closedDate = $now;
            }
                break;
            case 'wait':
            {
                if($task->consumed > 0 and $task->left > 0) $task->status = 'doing';
                if($task->left == $oldTask->left and $task->consumed == 0) $task->left = $task->estimate;
            }
            default:break;
        }
        if($task->assignedTo) $task->assignedDate = $now;

        $this->dao->update(TABLE_TASK)->data($task)
            ->autoCheck()
            ->batchCheckIF($task->status != 'cancel', $this->config->task->edit->requiredFields, 'notempty')

            ->checkIF($task->estimate != false, 'estimate', 'float')
            ->checkIF($task->consumed != false, 'consumed', 'float')
            ->checkIF($task->left     != false, 'left',     'float')
            ->checkIF($task->left == 0 and $task->status != 'cancel' and $task->status != 'closed' and $task->consumed != 0, 'status', 'equal', 'done')

            ->batchCheckIF($task->status == 'wait' or $task->status == 'doing', 'finishedBy, finishedDate,canceledBy, canceledDate, closedBy, closedDate, closedReason', 'empty')

            ->checkIF($task->status == 'done', 'consumed', 'notempty')
            ->checkIF($task->status == 'done' and $task->closedReason, 'closedReason', 'equal', 'done')
            ->batchCheckIF($task->status == 'done', 'canceledBy, canceledDate', 'empty')

            ->checkIF($task->status == 'closed', 'closedReason', 'notempty')
            ->batchCheckIF($task->closedReason == 'cancel', 'finishedBy, finishedDate', 'empty')
            ->where('id')->eq((int)$taskID)
            ->exec();

        if($task->status == 'done' and $task->closedReason) $this->dao->update(TABLE_TASK)->set('status')->eq('closed')->where('id')->eq($taskID)->exec();

        if($oldTask->story != false) $this->loadModel('story')->setStage($oldTask->story);
        if(!dao::isError())
        {
            $allChanges[$taskID] = common::createChanges($oldTask, $task);
        }
        else
        {
            die(js::error('task#' . $taskID . dao::getError(true)));
        }
    }

    return $allChanges;
}
