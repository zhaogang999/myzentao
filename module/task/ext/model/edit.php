<?php
/**
 * Update a task.
 *
 * @param  int    $taskID
 * @access public
 * @return void
 */
public function update($taskID)
{
    //新增
    $reviewDetail = array();
    $auditDetail = array();
    $taskDetail = new stdClass();
    $emptyReviewDetail = new stdclass();
    $reviewDetailChanges = array();
    $changes = array();

    $emptyReviewDetail->reviewID = '';
    $emptyReviewDetail->number = '';
    $emptyReviewDetail->reviewer = '';
    $emptyReviewDetail->item = '';
    $emptyReviewDetail->line = '';
    $emptyReviewDetail->severity = '';
    $emptyReviewDetail->description = '';
    $emptyReviewDetail->proposal = '';
    $emptyReviewDetail->changed = '';
    $emptyReviewDetail->action = '';
    $emptyReviewDetail->chkd = '';

    $oldTask = $this->getById($taskID);
    if(isset($_POST['lastEditedDate']) and $oldTask->lastEditedDate != $this->post->lastEditedDate)
    {
        dao::$errors[] = $this->lang->error->editedByOther;
        return false;
    }

    $now  = helper::now();
    $task = fixer::input('post')
        ->setDefault('story, estimate, left, consumed', 0)
        ->setDefault('deadline', '0000-00-00')
        ->setIF($this->post->story != false and $this->post->story != $oldTask->story, 'storyVersion', $this->loadModel('story')->getVersion($this->post->story))

        ->setIF($this->post->status == 'done', 'left', 0)
        ->setIF($this->post->status == 'done'   and !$this->post->finishedBy,   'finishedBy',   $this->app->user->account)
        ->setIF($this->post->status == 'done'   and !$this->post->finishedDate, 'finishedDate', $now)

        ->setIF($this->post->status == 'cancel' and !$this->post->canceledBy,   'canceledBy',   $this->app->user->account)
        ->setIF($this->post->status == 'cancel' and !$this->post->canceledDate, 'canceledDate', $now)
        ->setIF($this->post->status == 'cancel', 'assignedTo',   $oldTask->openedBy)
        ->setIF($this->post->status == 'cancel', 'assignedDate', $now)

        ->setIF($this->post->status == 'closed' and !$this->post->closedBy,     'closedBy',     $this->app->user->account)
        ->setIF($this->post->status == 'closed' and !$this->post->closedDate,   'closedDate',   $now)
        ->setIF($this->post->consumed > 0 and $this->post->left > 0 and $this->post->status == 'wait', 'status', 'doing')

        ->setIF($this->post->assignedTo != $oldTask->assignedTo, 'assignedDate', $now)

        ->setIF($this->post->status == 'wait' and $this->post->left == $oldTask->left and $this->post->consumed == 0, 'left', $this->post->estimate)

        ->add('lastEditedBy',   $this->app->user->account)
        ->add('lastEditedDate', $now)
        ->stripTags($this->config->task->editor->edit['id'], $this->config->allowedTags)
        ->join('mailto', ',')
        ->remove('comment,files,labels,uid')
        ->get();

    if($task->consumed < $oldTask->consumed)
    {
        die(js::error($this->lang->task->error->consumedSmall));
    }
    elseif($task->consumed != $oldTask->consumed or $task->left != $oldTask->left)
    {
        $estimate = new stdClass();
        $estimate->consumed = $task->consumed - $oldTask->consumed;
        $estimate->left     = $task->left;
        $estimate->task     = $taskID;
        $estimate->account  = $this->app->user->account;
        $estimate->date     = helper::now();
        $this->addTaskEstimate($estimate);
    }

    $task = $this->loadModel('file')->processEditor($task, $this->config->task->editor->edit['id'], $this->post->uid);

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
    //禅道任务增加关键字字段；需求：858；行号：100-101
    $taskDetail->keywords    = $task->keywords;

    if ($this->post->story != false and $this->post->story != $oldTask->story)
    {
        $taskDetail->storyVersion = $task->storyVersion;
    }
    $taskDetail->assignedTo = $task->assignedTo;
    if (!empty($task->assignedDate))
    {
        $taskDetail->assignedDate = $task->assignedDate;
    }
    $taskDetail->consumed = $task->consumed;
    $taskDetail->realStarted = $task->realStarted;
    $taskDetail->finishedBy = $task->finishedBy;
    $taskDetail->finishedDate = $task->finishedDate;
    $taskDetail->canceledBy = $task->canceledBy;
    $taskDetail->canceledDate = $task->canceledDate;
    $taskDetail->closedBy = $task->closedBy;
    $taskDetail->closedReason = $task->closedReason;
    $taskDetail->closedDate = $task->closedDate;
    $taskDetail->lastEditedBy = $task->lastEditedBy;
    $taskDetail->lastEditedDate = $task->lastEditedDate;
    //1 task
    $this->dao->begin();
    $this->dao->update(TABLE_TASK)->data($taskDetail)
        ->autoCheck()
        ->batchCheckIF($taskDetail->status != 'cancel', $this->config->task->edit->requiredFields, 'notempty')
        ->checkIF($taskDetail->deadline != '0000-00-00', 'deadline', 'ge', $task->estStarted)

        ->checkIF($taskDetail->estimate != false, 'estimate', 'float')
        ->checkIF($taskDetail->left     != false, 'left',     'float')
        ->checkIF($taskDetail->consumed != false, 'consumed', 'float')
        ->checkIF($taskDetail->status != 'wait' and $taskDetail->left == 0 and $taskDetail->status != 'cancel' and $taskDetail->status != 'closed', 'status', 'equal', 'done')

        ->batchCheckIF($taskDetail->status == 'wait' or $taskDetail->status == 'doing', 'finishedBy, finishedDate,canceledBy, canceledDate, closedBy, closedDate, closedReason', 'empty')

        ->checkIF($taskDetail->status == 'done', 'consumed', 'notempty')
        ->checkIF($taskDetail->status == 'done' and $taskDetail->closedReason, 'closedReason', 'equal', 'done')
        ->batchCheckIF($taskDetail->status == 'done', 'canceledBy, canceledDate', 'empty')

        ->checkIF($taskDetail->status == 'closed', 'closedReason', 'notempty')
        ->batchCheckIF($taskDetail->closedReason == 'cancel', 'finishedBy, finishedDate', 'empty')
        ->where('id')->eq((int)$taskID)->exec();

    if(dao::isError())
    {
        $this->dao->rollback();
        return false;
    }else
    {
        $changes = common::createChanges($oldTask, $taskDetail);
    }
    //QA审计;
    if ($oldTask->source == 'QA')
    {
        $emptyAuditDetail = new stdclass();
        $emptyAuditDetail->auditID = '';
        $emptyAuditDetail->noDec = '';
        $emptyAuditDetail->noType = '';
        $emptyAuditDetail->serious = '';
        $emptyAuditDetail->cause = '';
        $emptyAuditDetail->measures = '';
        //不符合项
        $num = count($task->auditID);
        for ($i = 0; $i < $num; $i++)
        {
            $auditDetail["$i"] = new stdClass();
            //$auditDetail["$i"]->aid       = $task->aid["$i"];
            $auditDetail["$i"]->auditID  = $task->auditID["$i"];
            $auditDetail["$i"]->noDec    = $task->noDec["$i"];
            $auditDetail["$i"]->noType   = $task->noType["$i"];
            $auditDetail["$i"]->serious  = $task->serious["$i"];
            $auditDetail["$i"]->cause     = $task->cause["$i"];
            $auditDetail["$i"]->measures   = $task->measures["$i"];
            //过滤空记录
            if ($auditDetail["$i"]->noDec == '')
            {
                continue;
            }
            $oldAuditDetail = $this->dao->select('*')
                ->from(TABLE_QAAUDIT)
                ->where('id')->eq($task->aid["$i"])
                ->andWhere('deleted')->eq('0')
                ->fetch();
            //新增不合格项详情
            if ($task->aid["$i"] == '')
            {
                if(empty($auditDetail["$i"]->noType)) die(js::error($this->lang->task->error->emptyNoType));
                if(empty($auditDetail["$i"]->serious)) die(js::error($this->lang->task->error->emptySerious));
                //unset($auditDetail["$i"]->id);
                $auditDetail["$i"]->task   = $taskID;
                $this->dao->insert(TABLE_QAAUDIT)->data($auditDetail["$i"])
                    ->autoCheck()
                    ->batchCheck($this->config->task->edit->requiredFields, 'notempty')
                    ->exec();
                if (!dao::isError()) {
                    unset($auditDetail["$i"]->task);
                    $auditDetailChanges["$i"] = common::createChanges($emptyAuditDetail, $auditDetail["$i"]);
                } else {
                    $this->dao->rollback();
                    return false;
                }
            }
            //编辑不合格项详情
            else
            {
                $this->dao->update(TABLE_QAAUDIT)->data($auditDetail["$i"])
                    ->autoCheck()
                    ->batchCheck($this->config->task->edit->requiredFields, 'notempty')
                    ->where('id')->eq($task->aid["$i"])->exec();
                if (!dao::isError())
                {
                    $auditDetailChanges["$i"] = common::createChanges($oldAuditDetail, $auditDetail["$i"]);
                }
                else
                {
                    $this->dao->rollback();
                    return false;
                }
            }
        }
        //成功操作
        foreach($auditDetailChanges as $auditDetailChange)
        {
            $changes = array_merge($changes,$auditDetailChange);
        }
    }

    if ($task->type == 'review' && $task->status == 'done')
    {
        $review = new stdClass();
        $review->fileNO = $task->fileNO;
        $review->recorder = $task->recorder;
        $review->reviewName = $task->reviewName;
        $review->task = $taskID;
        $review->doc = $task->doc;
        $review->referenceDoc = $task->referenceDoc;
        $review->reference = $task->reference;
        $review->pages = $task->pages;
        $review->reviewers = $task->reviewers;
        $review->reviewDate = $task->reviewDate;
        $review->reviewScope = $task->reviewScope;
        $review->reviewPlace = $task->reviewPlace;
        $review->effort = $task->effort;
        $review->conclusion = $task->conclusion;

        //编辑评审
        $oldReview = $this->dao->select('*')
            ->from(TABLE_REVIEW)
            ->where('id')
            ->eq("$task->reviewID")
            ->fetch();
        //review
        if (!$oldReview)
        {
            //附件为空跳出
            if($_FILES['files']['size']['0'] == '0') die(js::error($this->lang->task->error->fileNotEmpty));

            $this->dao->insert(TABLE_REVIEW)->data($review)
                ->autoCheck()
                ->batchCheck($this->config->task->finish->requiredFields, 'notempty')
                ->exec();
            if (!dao::isError()) {
                $task->reviewID = $this->dao->lastInsertID();
            } else {
                $this->dao->rollback();
                return false;
            }
        }
        else
        {
            $review->id = $task->reviewID;
            $this->dao->update(TABLE_REVIEW)->data($review)
                ->autoCheck()
                ->batchCheckIF($task->status != 'cancel', $this->config->task->edit->requiredFields, 'notempty')
                ->where('id')->eq($review->id)->limit(1)->exec();
            if(dao::isError())
            {
                $this->dao->rollback();
                return false;
            }
        }

        //reviewDetail
        $num = count($task->number);
        for ($i = 0; $i < $num; $i++)
        {
            $reviewDetail["$i"] = new stdClass();
            $reviewDetail["$i"]->id = $task->id["$i"];
            $reviewDetail["$i"]->reviewID = $task->reviewID;
            $reviewDetail["$i"]->number = $task->number["$i"];
            $reviewDetail["$i"]->reviewer = $task->reviewer["$i"];
            $reviewDetail["$i"]->item = $task->item["$i"];
            $reviewDetail["$i"]->line = $task->line["$i"];
            $reviewDetail["$i"]->severity = $task->severity["$i"];
            $reviewDetail["$i"]->description = $task->description["$i"];
            $reviewDetail["$i"]->proposal = $task->proposal["$i"];
            $reviewDetail["$i"]->changed = $task->changed["$i"];
            $reviewDetail["$i"]->action = $task->action["$i"];
            $reviewDetail["$i"]->chkd = $task->chkd["$i"];
            //过滤空记录
            if ($reviewDetail["$i"]->description == '')
            {
                continue;
            }
            $oldreViewDetail = $this->dao->select('*')
                ->from(TABLE_REVIEWDETAIL)
                ->where('id')->eq($reviewDetail["$i"]->id)
                ->andWhere('deleted')->eq('0')
                ->fetch();
            //新增评审详情
            if (!$oldreViewDetail)
            {
                unset($reviewDetail["$i"]->id);

                $this->dao->insert(TABLE_REVIEWDETAIL)->data($reviewDetail["$i"])
                    ->autoCheck()
                    ->batchCheck($this->config->task->create->requiredFields, 'notempty')
                    ->exec();
                if (!dao::isError()) {
                    $emptyReviewDetail->reviewID = $task->reviewID;
                    $reviewDetailChanges["$i"] = common::createChanges($emptyReviewDetail, $reviewDetail["$i"]);
                } else {
                    $this->dao->rollback();
                    return false;
                }
            }
            //编辑任务详情
            else
            {
                $this->dao->update(TABLE_REVIEWDETAIL)->data($reviewDetail["$i"])
                    ->autoCheck()
                    ->batchCheckIF($task->status != 'cancel', $this->config->task->edit->requiredFields, 'notempty')
                    ->where('id')->eq($reviewDetail["$i"]->id)->exec();
                if (!dao::isError())
                {
                    $reviewDetailChanges["$i"] = common::createChanges($oldreViewDetail, $reviewDetail["$i"]);
                }
                else
                {
                    $this->dao->rollback();
                    return false;
                }
            }
        }
        //成功操作
        $reviewChange = common::createChanges($oldReview, $review);
        $changes = array_merge($changes,$reviewChange);

        foreach($reviewDetailChanges as $reviewDetailChange)
        {
            $changes = array_merge($changes,$reviewDetailChange);
        }
    }
    $this->dao->commit();
    if($this->post->story != false) $this->loadModel('story')->setStage($this->post->story);
    $this->file->updateObjectID($this->post->uid, $taskID, 'task');
    return $changes;
}
