<?php
include '../../control.php';
class myBug extends bug
{
    /**
     * Resolve a bug.
     *
     * @param  int    $bugID
     * @access public
     * @return void
     */
    public function resolve($bugID)
    {
        if(!empty($_POST))
        {
            $this->bug->resolve($bugID);
            if(dao::isError()) die(js::error(dao::getError()));
            $files = $this->loadModel('file')->saveUpload('bug', $bugID);

            $fileAction = !empty($files) ? $this->lang->addFiles . join(',', $files) . "\n" : '';
            $actionID = $this->action->create('bug', $bugID, 'Resolved', $fileAction . $this->post->comment, $this->post->resolution . ($this->post->duplicateBug ? ':' . (int)$this->post->duplicateBug : ''));
            $this->bug->sendmail($bugID, $actionID);

            $bug = $this->bug->getById($bugID);
            if($bug->toTask != 0)
            {
                /* If task is not finished, update it's status. */
                $task = $this->task->getById($bug->toTask);
                if($task->status != 'done')
                {
                    $confirmURL = $this->createLink('task', 'view', "taskID=$bug->toTask");
                    unset($_GET['onlybody']);
                    $cancelURL  = $this->createLink('bug', 'view', "bugID=$bugID");
                    die(js::confirm(sprintf($this->lang->bug->remindTask, $bug->toTask), $confirmURL, $cancelURL, 'parent', 'parent.parent'));
                }
            }
            if(isonlybody()) die(js::closeModal('parent.parent'));
            die(js::locate($this->createLink('bug', 'view', "bugID=$bugID"), 'parent'));
        }

        $bug        = $this->bug->getById($bugID);
        $productID  = $bug->product;
        $users      = $this->user->getPairs('nodeleted');
        $assignedTo = $bug->openedBy;
        if(!isset($users[$assignedTo])) $assignedTo = $this->bug->getModuleOwner($bug->module, $productID);
        unset($this->lang->bug->resolutionList['tostory']);

        $this->bug->setMenu($this->products, $productID, $bug->branch);

        $this->view->title      = $this->products[$productID] . $this->lang->colon . $this->lang->bug->resolve;
        $this->view->position[] = html::a($this->createLink('bug', 'browse', "productID=$productID"), $this->products[$productID]);
        $this->view->position[] = $this->lang->bug->resolve;

        $this->view->bug        = $bug;
        $this->view->users      = $users;
        $this->view->assignedTo = $assignedTo;
        $this->view->projects   = $this->loadModel('product')->getProjectPairs($productID, $bug->branch ? "0,{$bug->branch}" : 0, $params = 'nodeleted');
        $this->view->builds     = $this->loadModel('build')->getProductBuildPairs($productID, $branch = 0, 'all');
        $this->view->actions    = $this->action->getList('bug', $bugID);
        //xinzeng
        $this->view->telComent  = $this->lang->bug->tplReason . $this->lang->bug->tplProject . $this->lang->bug->tplInfluence . $this->lang->bug->tplExpectedSolutionVersion;
        $this->display();
    }
}
