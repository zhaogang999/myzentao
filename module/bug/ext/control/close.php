<?php
include '../../control.php';
class myBug extends bug
{
    /**
     * Close a bug.
     *
     * @param  int $bugID
     * @access public
     * @return void
     */
    public function close($bugID)
    {
        if (!empty($_POST)) {
            $this->bug->close($bugID);
            if (dao::isError()) die(js::error(dao::getError()));
            $actionID = $this->action->create('bug', $bugID, 'Closed', $this->post->comment);
            $this->bug->sendmail($bugID, $actionID);
            if (isonlybody()) die(js::closeModal('parent.parent'));
            die(js::locate($this->createLink('bug', 'view', "bugID=$bugID"), 'parent'));
        }

        $bug = $this->bug->getById($bugID);
        $productID = $bug->product;
        $this->bug->setMenu($this->products, $productID, $bug->branch);

        $this->view->title = $this->products[$productID] . $this->lang->colon . $this->lang->bug->close;
        $this->view->position[] = html::a($this->createLink('bug', 'browse', "productID=$productID"), $this->products[$productID]);
        $this->view->position[] = $this->lang->bug->close;

        $this->view->bug = $bug;
        $this->view->users = $this->user->getPairs('noletter');
        $this->view->actions = $this->action->getList('bug', $bugID);
        //xinzeng
        $this->view->telComent  = $this->lang->bug->tplVerificationResults . $this->lang->bug->tplVerificationVersion . $this->lang->bug->tplVerificationContent;
        $this->display();
    }
}