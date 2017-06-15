<?php
include '../../control.php';
class myStory extends story
{
    /**
     * View a story.
     *
     * @param  int    $storyID
     * @param  int    $version
     * @access public
     * @return void
     */
    public function view($storyID, $version = 0, $from = 'product', $param = 0)
    {
        $storyID = (int)$storyID;
        $story   = $this->story->getById($storyID, $version, true);
        //细分需求相互关联 
        $parentStory = $this->dao->select('id,title')->from(TABLE_STORY)->where('childStories')->like("%$storyID%")->fetch();
        //新增
        unset($story->tasks);
        $story->tasks  = $this->dao->select('id, name, assignedTo, project, type, status, consumed, `left`')->from(TABLE_TASK)->where('story')->eq($storyID)->andWhere('deleted')->eq(0)->orderBy('id DESC')->fetchGroup('project');

        if(!$story) die(js::error($this->lang->notFound) . js::locate('back'));

        $story->files = $this->loadModel('file')->getByObject('story', $storyID);
        $product      = $this->dao->findById($story->product)->from(TABLE_PRODUCT)->fields('name, id, type')->fetch();
        $plan         = $this->dao->findById($story->plan)->from(TABLE_PRODUCTPLAN)->fetch('title');
        $bugs         = $this->dao->select('id,title')->from(TABLE_BUG)->where('story')->eq($storyID)->andWhere('deleted')->eq(0)->fetchAll();
        $fromBug      = $this->dao->select('id,title')->from(TABLE_BUG)->where('toStory')->eq($storyID)->fetch();
        $cases        = $this->dao->select('id,title')->from(TABLE_CASE)->where('story')->eq($storyID)->andWhere('deleted')->eq(0)->fetchAll();
        $modulePath   = $this->tree->getParents($story->module);
        $users        = $this->user->getPairs('noletter');

        /* Set the menu. */
        $this->product->setMenu($this->product->getPairs(), $product->id, $story->branch);

        if($from == 'project')
        {
            $project = $this->loadModel('project')->getById($param);
            if($project->status == 'done') $from = '';
        }

        $title      = "STORY #$story->id $story->title - $product->name";
        $position[] = html::a($this->createLink('product', 'browse', "product=$product->id&branch=$story->branch"), $product->name);
        $position[] = $this->lang->story->common;
        $position[] = $this->lang->story->view;
        //细分需求相互关联
        if ($parentStory)
        {
            $this->view->parentStory = $parentStory;
        }

        $this->view->title      = $title;
        $this->view->position   = $position;
        $this->view->product    = $product;
        $this->view->branches   = $product->type == 'normal' ? array() : $this->loadModel('branch')->getPairs($product->id);
        $this->view->plan       = $plan;
        $this->view->bugs       = $bugs;
        $this->view->fromBug    = $fromBug;
        $this->view->cases      = $cases;
        $this->view->story      = $story;
        $this->view->users      = $users;
        $this->view->projects   = $this->loadModel('project')->getPairs('nocode');
        $this->view->actions    = $this->action->getList('story', $storyID);
        $this->view->modulePath = $modulePath;
        $this->view->version    = $version == 0 ? $story->version : $version;
        $this->view->preAndNext = $this->loadModel('common')->getPreAndNextObject('story', $storyID);
        $this->view->from       = $from;
        $this->view->param      = $param;
        $this->display();
    }
}