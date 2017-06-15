<?php
include '../../control.php';
class myProject extends project
{
    /**
     * Browse stories of a project.
     *
     * @param  int    $projectID
     * @param  string $orderBy
     * @access public
     * @return void
     */
    public function story($projectID = 0, $orderBy = '', $type = 'byModule', $param = 0, $recTotal = 0, $recPerPage = 50, $pageID = 1)
    {
        /* Load these models. */
        $this->loadModel('story');
        $this->loadModel('user');
        $this->app->loadLang('testcase');

        /* Save session. */
        $this->app->session->set('storyList', $this->app->getURI(true));

        /* Process the order by field. */
        if(!$orderBy) $orderBy = $this->cookie->projectStoryOrder ? $this->cookie->projectStoryOrder : 'pri';
        setcookie('projectStoryOrder', $orderBy, $this->config->cookieLife, $this->config->webRoot);

        /* Append id for secend sort. */
        $sort = $this->loadModel('common')->appendOrder($orderBy);

        $queryID = ($type == 'bySearch') ? (int)$param : 0;
        $project = $this->commonAction($projectID);
        $projectID = $project->id;

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $stories = $this->story->getProjectStories($projectID, $sort, $type, $param, $pager);
        $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'story', false);
        $users      = $this->user->getPairs('noletter');

        /* Get project's product. */
        $productID = 0;
        $productPairs = $this->loadModel('product')->getProductsByProject($projectID);
        if($productPairs) $productID = key($productPairs);

        /* Build the search form. */
        $modules  = array();
        $projectModules = $this->loadModel('tree')->getTaskTreeModules($projectID, true);
        $products = $this->project->getProducts($projectID);
        foreach($products as $product)
        {
            $productModules = $this->tree->getOptionMenu($product->id);
            foreach($productModules as $moduleID => $moduleName)
            {
                if($moduleID and !isset($projectModules[$moduleID])) continue;
                $modules[$moduleID] = ((count($products) >= 2 and $moduleID) ? $product->name : '') . $moduleName;
            }
        }
        $actionURL    = $this->createLink('project', 'story', "projectID=$projectID&orderBy=$orderBy&type=bySearch&queryID=myQueryID");
        $branchGroups = $this->loadModel('branch')->getByProducts(array_keys($products), 'noempty');
        $this->project->buildStorySearchForm($products, $branchGroups, $modules, $queryID, $actionURL, 'projectStory');

        /* Header and position. */
        $title      = $project->name . $this->lang->colon . $this->lang->project->story;
        $position[] = html::a($this->createLink('project', 'browse', "projectID=$projectID"), $project->name);
        $position[] = $this->lang->project->story;

        /* Count T B C */
        $storyIdList = array_keys($stories);;
        $storyTasks = $this->loadModel('task')->getStoryTaskCounts($storyIdList,$projectID);
        $storyBugs  = $this->loadModel('bug')->getStoryBugCounts($storyIdList,$projectID);
        $storyCases = $this->loadModel('testcase')->getStoryCaseCounts($storyIdList);

        //同产品的项目
        $proID = $this->dao->select('product')
            ->from(TABLE_PROJECTPRODUCT)
            ->where('project')
            ->eq($projectID)
            ->fetch();
        $projects = $this->dao->select('t1.project, t2.name')
            ->from(TABLE_PROJECTPRODUCT)->alias('t1')
            ->leftJoin(TABLE_PROJECT)->alias('t2')
            ->on('t1.project = t2.id')
            ->where('t2.deleted')->eq('0')
            ->andWhere('t1.product')->eq($proID->product)
            ->andWhere('t1.project')->ne($projectID)
            ->fetchAll();
        
        $this->view->projects      = $projects;
        $this->view->oldProjectID      = $projectID;

        /* Assign. */
        $this->view->title        = $title;
        $this->view->position     = $position;
        $this->view->productID    = $productID;
        $this->view->stories      = $stories;
        $this->view->summary      = $this->product->summary($stories);
        $this->view->orderBy      = $orderBy;
        $this->view->type         = $type;
        $this->view->param        = $param;
        $this->view->moduleTree   = $this->loadModel('tree')->getProjectStoryTreeMenu($projectID, $startModuleID = 0, array('treeModel', 'createProjectStoryLink'));
        $this->view->tabID        = 'story';
        $this->view->storyTasks   = $storyTasks;
        $this->view->storyBugs    = $storyBugs;
        $this->view->storyCases   = $storyCases;
        $this->view->users        = $users;
        $this->view->pager        = $pager;
        $this->view->branchGroups = $branchGroups;

        $this->display();
    }
}