<?php
public function start($taskID)
{
    return $this->loadExtension('gantt')->start($taskID);
}

public function finish($taskID)
{
    $taskInfo = $this->dao->select('*')->from(TABLE_TASK)->where('id')->eq("$taskID")->fetch();

    if ($taskInfo->type == 'review')
    {
        $emptyReviewDetail  = '';
        $reviewDetail       = array();
        $emptyReview        = '';
        $review             = '';
        $taskDetail         = '';
        $estimate           = '';
    
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
        $now  = helper::now();
        $task = fixer::input('post')
            ->setDefault('left', 0)
            ->setDefault('assignedTo',   $oldTask->openedBy)
            ->setDefault('assignedDate', $now)
            ->setDefault('status', 'done')
            ->setDefault('finishedBy, lastEditedBy', $this->app->user->account)
            ->setDefault('finishedDate, lastEditedDate', $now)
            ->remove('comment,files,labels')
            ->get();
    
        if($task->finishedDate == substr($now, 0, 10)) $task->finishedDate = $now;
        if(!is_numeric($task->consumed)) die(js::error($this->lang->task->error->consumedNumber));
    
        /* Record consumed and left. */
        $consumed = $task->consumed - $oldTask->consumed;
        if($consumed < 0) die(js::error($this->lang->task->error->consumedSmall));
        /*$estimate = fixer::input('post')
            ->setDefault('account', $this->app->user->account)
            ->setDefault('task', $taskID)
            ->setDefault('date', date(DT_DATE1))
            ->setDefault('left', 0)
            ->remove('finishedDate,comment,assignedTo,files,labels,consumed')
            ->get();*/
        $estimate->uid = $task->uid;
        $estimate->account = $this->app->user->account;
        $estimate->task = $taskID;
        $estimate->date = date(DT_DATE1);
        $estimate->left = 0;
        $estimate->consumed = $consumed;
        if($estimate->consumed) $this->addTaskEstimate($estimate);
    

        
        $taskDetail->consumed = $task->consumed;
        $taskDetail->assignedTo = $task->assignedTo;
        $taskDetail->finishedDate = $task->finishedDate;
        $taskDetail->left = $task->left;
        $taskDetail->assignedDate = $task->assignedDate;
        $taskDetail->status = $task->status;
        $taskDetail->finishedBy = $task->finishedBy;
        $taskDetail->lastEditedBy = $task->lastEditedBy;
        $taskDetail->lastEditedDate = $task->lastEditedDate;

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

        //附件为空跳出
        if($_FILES['files']['size']['0'] == '0') die(js::error($this->lang->task->error->fileNotEmpty));

        if($task->fileNO == '') die(js::error($this->lang->task->error->fileNOEmpty));
        if($task->recorder == '') die(js::error($this->lang->task->error->recorderEmpty));
        if($task->reviewName == '') die(js::error($this->lang->task->error->reviewNameEmpty));
        if($task->reviewDate == '') die(js::error($this->lang->task->error->reviewDateEmpty));
        if($task->doc == '') die(js::error($this->lang->task->error->docEmpty));
        if($task->reviewScope == '') die(js::error($this->lang->task->error->reviewScopeEmpty));
        if($task->referenceDoc == '') die(js::error($this->lang->task->error->referenceDocEmpty));
        if($task->reviewPlace == '') die(js::error($this->lang->task->error->reviewPlaceEmpty));
        if($task->reference == '') die(js::error($this->lang->task->error->referenceEmpty));
        if($task->effort == '') die(js::error($this->lang->task->error->effortEmpty));
        if($task->pages == '') die(js::error($this->lang->task->error->pagesEmpty));
        if($task->conclusion == '') die(js::error($this->lang->task->error->conclusionEmpty));
        if($task->reviewers == '') die(js::error($this->lang->task->error->reviewersEmpty));

        if(!is_numeric($task->effort)) die(js::error($this->lang->task->error->effortNumber));
        if(!is_numeric($task->pages)) die(js::error($this->lang->task->error->pagesNumber));

        //添加评审
        //1.task
        $this->dao->begin();
        $this->dao->update(TABLE_TASK)->data($taskDetail)
            ->autoCheck()
            ->check('consumed', 'notempty')
            ->where('id')->eq((int)$taskID)->exec();
        if(dao::isError())
        {
            $this->dao->rollback;
            return false;
        }

        //2.insert review
        $this->dao->insert(TABLE_REVIEW)->data($review)
            ->autoCheck()
            ->batchCheck($this->config->task->finish->requiredFields, 'notempty')
            ->exec();
        if (!dao::isError()) {
            $reviewID = $this->dao->lastInsertID();
        } else {
            $this->dao->rollback();
            return false;
        }

        $emptyReviewDetail->reviewID = $reviewID;

        $num = count($task->number);
        for ($i = 0; $i < $num; $i++) {
            $reviewDetail["$i"]->reviewID = $reviewID;
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
            $this->dao->insert(TABLE_REVIEWDETAIL)->data($reviewDetail["$i"])
                ->autoCheck()
                ->batchCheck($this->config->task->create->requiredFields, 'notempty')
                ->exec();
            if (!dao::isError()) {
                $reviewDetailChanges["$i"] = common::createChanges($emptyReviewDetail, $reviewDetail["$i"]);
            } else {
                $this->dao->rollback();
                return false;
            }
        }
        //成功操作
        $this->dao->commit();
        //设置需求状态
        if($this->post->story != false) $this->loadModel('story')->setStage($this->post->story);
        //获得改变值
        $taskChange = common::createChanges($oldTask, $taskDetail);
        //建一个空对象
        $emptyReview->fileNO = '';
        $emptyReview->recorder = '';
        $emptyReview->reviewName = '';
        $emptyReview->task = $taskID;
        $emptyReview->doc = '';
        $emptyReview->referenceDoc = '';
        $emptyReview->reference = '';
        $emptyReview->pages = '';
        $emptyReview->reviewers = '';
        $emptyReview->reviewDate = '';
        $emptyReview->reviewScope = '';
        $emptyReview->reviewPlace = '';
        $emptyReview->effort = '';
        $emptyReview->conclusion = '';
        $reviewChange = common::createChanges($emptyReview, $review);
        $changes = array_merge($taskChange,$reviewChange);

        foreach($reviewDetailChanges as $reviewDetailChange)
        {
            $changes = array_merge($changes,$reviewDetailChange);
        }
        return $changes;
    }
    else
    {
        return $this->loadExtension('gantt')->finish($taskID);
    }
}
