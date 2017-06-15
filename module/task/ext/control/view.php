<?php
include '../../control.php';
class myTask extends task
{
    /**
     * View a task.
     *
     * @param  int    $taskID
     * @access public
     * @return void
     */
    public function view($taskID)
    {
        $task = $this->task->getById($taskID, true);
        //xinzeng
        if ($task->source == 'QA')
        {
            $auditDetails = $this->dao->select('*')
                ->from(TABLE_QAAUDIT)
                ->where('task')->eq($taskID)
                ->andWhere('deleted')->eq('0')
                ->fetchAll();
        }
        if ($task->type == 'review')
        {
            $review = $this->dao->select('*')
                ->from(TABLE_REVIEW)
                ->where('task')
                ->eq($taskID)
                ->fetch();
            $reviewDetails = $this->dao->select('*')
                ->from(TABLE_REVIEWDETAIL)
                ->where('reviewID')->eq($review->id)
                ->andWhere('deleted')->eq('0')
                ->fetchAll();
        }

        if(!$task) die(js::error($this->lang->notFound) . js::locate('back'));

        if($task->fromBug != 0)
        {
            $bug = $this->loadModel('bug')->getById($task->fromBug);
            $task->bugSteps = '';
            if($bug)
            {
                $task->bugSteps = $this->loadModel('file')->setImgSize($bug->steps);
                foreach($bug->files as $file) $task->files[] = $file;
            }
            $this->view->fromBug = $bug;
        }
        else
        {
            $story = $this->story->getById($task->story);
            $task->storySpec     = empty($story) ? '' : $this->loadModel('file')->setImgSize($story->spec);
            $task->storyVerify   = empty($story) ? '' : $this->loadModel('file')->setImgSize($story->verify);
            $task->storyFiles    = $this->loadModel('file')->getByObject('story', $task->story);
        }

        /* Update action. */
        if($task->assignedTo == $this->app->user->account) $this->loadModel('action')->read('task', $taskID);

        /* Set menu. */
        $project = $this->project->getById($task->project);
        $this->project->setMenu($this->project->getPairs(), $project->id);

        $title      = "TASK#$task->id $task->name / $project->name";
        $position[] = html::a($this->createLink('project', 'browse', "projectID=$task->project"), $project->name);
        $position[] = $this->lang->task->common;
        $position[] = $this->lang->task->view;

        if ($task->source == 'QA')
        {
            $this->view->auditDetails = $auditDetails;
        }
        if ($task->type == 'review')
        {
            $this->view->review      = $review;
            $this->view->reviewDetails = $reviewDetails;
        }

        $this->view->title       = $title;
        $this->view->position    = $position;
        $this->view->project     = $project;
        $this->view->task        = $task;
        $this->view->actions     = $this->loadModel('action')->getList('task', $taskID);
        $this->view->users       = $this->loadModel('user')->getPairs('noletter');
        $this->view->preAndNext  = $this->loadModel('common')->getPreAndNextObject('task', $taskID);
        $this->view->product     = $this->tree->getProduct($task->module);
        $this->view->modulePath  = $this->tree->getParents($task->module);
        $this->display();
    }
}

