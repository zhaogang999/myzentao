<?php
/**
 * Create a task.
 *
 * @param  int    $projectID
 * @access public
 * @return void
 */
public function create($projectID)
{
    $tasksID  = array();
    $taskFiles = array();
    $taskDetail = new stdClass();

    $this->loadModel('file');
    $task = fixer::input('post')
        ->add('project', (int)$projectID)
        ->setDefault('estimate, left, story', 0)
        ->setDefault('estStarted', '0000-00-00')
        ->setDefault('deadline', '0000-00-00')
        ->setDefault('status', 'wait')
        ->setIF($this->post->estimate != false, 'left', $this->post->estimate)
        ->setIF($this->post->story != false, 'storyVersion', $this->loadModel('story')->getVersion($this->post->story))
        ->setDefault('openedBy',   $this->app->user->account)
        ->setDefault('openedDate', helper::now())
        ->stripTags($this->config->task->editor->create['id'], $this->config->allowedTags)
        ->join('mailto', ',')
        ->remove('after,files,labels,assignedTo,uid')
        ->get();

    foreach($this->post->assignedTo as $assignedTo)
    {
        $task->assignedTo = $assignedTo;
        if($assignedTo) $task->assignedDate = helper::now();

        /* Check duplicate task. */
        if($task->type != 'affair')
        {
            $result = $this->loadModel('common')->removeDuplicate('task', $task, "project=$projectID");
            if($result['stop'])
            {
                $tasksID[$assignedTo] = array('status' => 'exists', 'id' => $result['duplicate']);
                continue;
            }
        }

        $task = $this->file->processEditor($task, $this->config->task->editor->create['id'], $this->post->uid);

        //任务数据新增
        $taskDetail->module = $task->module;
        $taskDetail->type = $task->type;
        $taskDetail->story = $task->story;
        $taskDetail->color = $task->color;
        $taskDetail->name = $task->name;
        $taskDetail->pri = $task->pri;
        $taskDetail->estimate = $task->estimate;
        $taskDetail->desc = $task->desc;
        $taskDetail->estStarted = $task->estStarted;
        $taskDetail->deadline = $task->deadline;
        $taskDetail->mailto = $task->mailto;
        $taskDetail->project = $task->project;
        $taskDetail->left = $task->left;
        $taskDetail->status = $task->status;
        if ($this->post->story != false)
        {
            $taskDetail->storyVersion = $task->storyVersion;
        }
        $taskDetail->openedBy = $task->openedBy;
        $taskDetail->openedDate = $task->openedDate;
        $taskDetail->assignedTo = $task->assignedTo;
        $taskDetail->assignedDate = $task->assignedDate;
        //禅道任务增加关键字字段；需求：858；行号：72-73
        $taskDetail->keywords    = $task->keywords;

        if ($task->source == 'QA')
        {
            $taskDetail->source = $task->source;
            $this->dao->begin();
            $this->dao->insert(TABLE_TASK)->data($taskDetail)
                ->autoCheck()
                ->batchCheck($this->config->task->create->newRequiredFields, 'notempty')
                ->checkIF($taskDetail->estimate != '', 'estimate', 'float')
                ->checkIF($taskDetail->deadline != '0000-00-00', 'deadline', 'ge', $taskDetail->estStarted)
                ->exec();
            if (!dao::isError()) {
                //taskId
                $taskID = $this->dao->lastInsertID();
            } else {
                $this->dao->rollback();
                return false;
            }
            $num = count($task->auditID);
           
            for ($i = 0; $i < $num; $i++) {
                $auditDetail["$i"] = new stdClass();
                $auditDetail["$i"]->task       = $taskID;
                $auditDetail["$i"]->auditID   = $task->auditID["$i"];
                $auditDetail["$i"]->noDec     = $task->noDec["$i"];
                $auditDetail["$i"]->noType    = $task->noType["$i"];
                $auditDetail["$i"]->serious   = $task->serious["$i"];
                $auditDetail["$i"]->cause     = $task->cause["$i"];
                $auditDetail["$i"]->measures   = $task->measures["$i"];

                $this->dao->insert(TABLE_QAAUDIT)->data($auditDetail["$i"])
                    ->autoCheck()
                    ->batchCheck($this->config->task->create->newRequiredFields, 'notempty')
                    ->exec();
                if (!dao::isError()) {
                    $auditDetail["$i"]->id = $this->dao->lastInsertID();
                } else {
                    $this->dao->rollback();
                    return false;
                }
            }

            //成功操作
            $this->dao->commit();
            //设置需求状态
            if ($this->post->story) $this->loadModel('story')->setStage($this->post->story);
            $this->file->updateObjectID($this->post->uid, $taskID, 'task');
            if (!empty($taskFile)) {
                $taskFile->objectID = $taskID;
                $this->dao->insert(TABLE_FILE)->data($taskFile)->exec();
            } else {
                $taskFileTitle = $this->file->saveUpload('task', $taskID);
                $taskFile = $this->dao->select('*')->from(TABLE_FILE)->where('id')->eq(key($taskFileTitle))->fetch();
                unset($taskFile->id);
            }
            $tasksID[$assignedTo] = array('status' => 'created', 'id' => $taskID);
        }
        else
        {
            //源代码
            $this->dao->insert(TABLE_TASK)->data($taskDetail)
                ->autoCheck()
                ->batchCheck($this->config->task->create->newRequiredFields, 'notempty')
                ->checkIF($taskDetail->estimate != '', 'estimate', 'float')
                ->checkIF($taskDetail->deadline != '0000-00-00', 'deadline', 'ge', $task->estStarted)
                ->exec();

            if(!dao::isError())
            {
                $taskID = $this->dao->lastInsertID();
                if($this->post->story) $this->loadModel('story')->setStage($this->post->story);
                $this->file->updateObjectID($this->post->uid, $taskID, 'task');
                if(!empty($taskFiles))
                {
                    foreach($taskFiles as $taskFile)
                    {
                        $taskFile->objectID = $taskID;
                        $this->dao->insert(TABLE_FILE)->data($taskFile)->exec();
                    }
                }
                else
                {
                    $taskFileTitle = $this->file->saveUpload('task', $taskID);
                    $taskFiles = $this->dao->select('*')->from(TABLE_FILE)->where('id')->in(array_keys($taskFileTitle))->fetchAll('id');
                    foreach($taskFiles as $fileID => $taskFile) unset($taskFiles[$fileID]->id);
                }
                $tasksID[$assignedTo] = array('status' => 'created', 'id' => $taskID);
            }
            else
            {
                return false;
            }
        }
    }
    return $tasksID;
}