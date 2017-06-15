<?php
class excelTask extends taskModel
{
    public function setListValue($projectID)
    {
        $project   = $this->loadModel('project')->getByID($projectID);
        $modules   = $this->loadModel('tree')->getTaskOptionMenu($projectID);
        $stories   = $this->loadModel('story')->getProjectStories($projectID);
        $priList   = $this->lang->task->priList;
        $typeList  = $this->lang->task->typeList;

        unset($typeList['']);

        foreach($modules  as $id => $module) $modules[$id] .= "(#$id)";
        foreach($stories  as $id => $story)  $stories[$id]  = "$story->title(#$story->id)";

        $this->post->set('moduleList',   array_values($modules));
        $this->post->set('storyList',    array_values($stories));
        $this->post->set('priList',      join(',', $priList));
        $this->post->set('typeList',     join(',', $typeList));
        $this->post->set('listStyle',  $this->config->task->export->listFields);
        $this->post->set('extraNum',   0);
        $this->post->set('project',    $project->name);
    }

    public function createFromImport($projectID)
    {
        $this->loadModel('action');
        $this->loadModel('file');
        $this->loadModel('story');
        $now  = helper::now();
        $data = fixer::input('post')->get();

        if(!empty($_POST['id'])) $oldTasks = $this->dao->select('*')->from(TABLE_TASK)->where('id')->in(($_POST['id']))->andWhere('project')->eq($projectID)->fetchAll('id');

        foreach($data->project as $key => $project)
        {
            $taskData = new stdclass();

            $taskData->project      = $project;
            $taskData->module       = (int)$data->module[$key];
            $taskData->name         = $data->name[$key];
            $taskData->desc         = nl2br($data->desc[$key]);
            $taskData->story        = (int)$data->story[$key];
            $taskData->pri          = (int)$data->pri[$key];
            $taskData->type         = $data->type[$key];
            $taskData->estimate     = (float)$data->estimate[$key];
            $taskData->estStarted   = empty($data->estStarted[$key]) ? '0000-00-00' : $data->estStarted[$key];
            $taskData->deadline     = empty($data->deadline[$key]) ? '0000-00-00' : $data->deadline[$key];

            //完善任务导入功能，增加指派给
            $assingedToAccount = $this->dao->select('account')->from(TABLE_USER)->where('realname')->eq(trim($data->assignedTo[$key]))->fetch();
            $taskData->assignedTo  =  empty($assingedToAccount)?'':$assingedToAccount->account;
            $taskData->assignedDate  = $now;

            if(isset($this->config->task->create->requiredFields))
            {
                $requiredFields = explode(',', $this->config->task->create->requiredFields);
                $invalid = false;
                foreach($requiredFields as $requiredField)
                {
                    $requiredField = trim($requiredField);
                    if(empty($taskData->$requiredField)) $invalid = true;
                }
                if($invalid) continue;
            }

            $taskID = 0;
            if(!empty($_POST['id'][$key]) and empty($_POST['insert']))
            {
                $taskID = $data->id[$key];
                if(!isset($oldTasks[$taskID])) $taskID = 0;
            }

            if($taskID)
            {
                if($taskData->story != $oldTasks[$taskID]->story) $taskData->storyVersion = $this->story->getVersion($taskData->story);
                $taskData->desc         = str_replace('src="' . common::getSysURL() . '/', 'src="', $taskData->desc);
                $taskData->status       = $oldTasks[$taskID]->status;

                $oldTask = (array)$oldTasks[$taskID];
                $newTask = (array)$taskData;
                $oldTask['desc'] = trim($this->file->excludeHtml($oldTask['desc'], 'noImg'));
                $newTask['desc'] = trim($this->file->excludeHtml($newTask['desc'], 'noImg'));
                $changes = common::createChanges((object)$oldTask, (object)$newTask);
                if(empty($changes)) continue;

                if($oldTask['estimate'] == 0 and $oldTask['left'] == 0) $taskData->left = $taskData->estimate;

                $taskData->lastEditedBy   = $this->app->user->account;
                $taskData->lastEditedDate = $now;

                $this->dao->update(TABLE_TASK)->data($taskData)
                    ->autoCheck()
                    ->where('id')->eq((int)$taskID)->exec();

                $actionID = $this->action->create('task', $taskID, 'Edited', '');
                $this->action->logHistory($actionID, $changes);
                $tasksID[$key] = $taskID;
            }
            else
            {
                if($taskData->story != false) $taskData->storyVersion = $this->loadModel('story')->getVersion($taskData->story);
                $taskData->left       = $taskData->estimate;
                $taskData->status     = 'wait';
                $taskData->openedBy   = $this->app->user->account;
                $taskData->openedDate = $now;

                if($taskData->deadline != '0000-00-00' and strtotime($taskData->deadline) < strtotime($taskData->estStarted)) continue;
                $this->dao->insert(TABLE_TASK)->data($taskData)
                    ->autoCheck()
                    ->exec();

                if(!dao::isError())
                {
                    $taskID = $this->dao->lastInsertID();
                    $actionID = $this->loadModel('action')->create('task', $taskID, 'opened', '');
                    $tasksID[$key] = $taskID;
                    //发信
                    $this->sendmail($taskID, $actionID);
                }
                else
                {
                    dao::getError();
                }
            }
        }

        unlink($this->session->importFile);
        unset($_SESSION['importFile']);

        return $tasksID;
    }
}
