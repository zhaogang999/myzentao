<?php
/**
 * Set stage of a story.
 *
 * @param  int    $storyID
 * @access public
 * @return bool
 */
public function setStage($storyID)
{
    $storyID = (int)$storyID;

    /* Get projects which status is doing. */
    $this->dao->delete()->from(TABLE_STORYSTAGE)->where('story')->eq($storyID)->exec();
    $story    = $this->dao->findById($storyID)->from(TABLE_STORY)->fetch();
    $product  = $this->dao->findById($story->product)->from(TABLE_PRODUCT)->fetch();
    $projects = $this->dao->select('t1.project,t3.branch')->from(TABLE_PROJECTSTORY)->alias('t1')
        ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project = t2.id')
        ->leftJoin(TABLE_PROJECTPRODUCT)->alias('t3')->on('t1.project = t3.project')
        ->where('t1.story')->eq($storyID)
        ->andWhere('t2.deleted')->eq(0)
        ->fetchPairs('project', 'branch');

    $hasBranch = ($product->type != 'normal' and empty($story->branch));
    $stages    = array();
    if($hasBranch and $story->plan)
    {
        $plans = $this->dao->select('*')->from(TABLE_PRODUCTPLAN)->where('id')->in($story->plan)->fetchPairs('branch', 'branch');
        foreach($plans as $branch) $stages[$branch] = 'planned';
    }

    /* If no projects, in plan, stage is planned. No plan, wait. */
    if(!$projects)
    {
        $this->dao->update(TABLE_STORY)->set('stage')->eq('wait')->where('id')->eq($storyID)->andWhere('plan')->eq('')->exec();

        foreach($stages as $branch => $stage) $this->dao->insert(TABLE_STORYSTAGE)->set('story')->eq($storyID)->set('branch')->eq($branch)->set('stage')->eq($stage)->exec();
        $this->dao->update(TABLE_STORY)->set('stage')->eq('planned')->where('id')->eq($storyID)->andWhere("(plan != '' AND plan != '0')")->exec();
    }

    if($hasBranch)
    {
        foreach($projects as $projectID => $branch) $stages[$branch] = 'projected';
    }
    //获取任务类型配置信息
    $taskType = $this->config->story->develTask . ',test';

    /* Search related tasks. 取消统计status为pause的任务 */
    $tasks = $this->dao->select('type,project,status')->from(TABLE_TASK)
        ->where('project')->in(array_keys($projects))
        ->andWhere('story')->eq($storyID)
        ->andWhere('type')->in($taskType)
        ->andWhere('status')->ne('cancel')
        ->andWhere('status')->ne('pause')
        ->andWhere('closedReason')->ne('cancel')
        ->andWhere('deleted')->eq(0)
        ->fetchGroup('type');

    /* No tasks, then the stage is projected. */
    if(!$tasks and $projects)
    {
        foreach($stages as $branch => $stage) $this->dao->insert(TABLE_STORYSTAGE)->set('story')->eq($storyID)->set('branch')->eq($branch)->set('stage')->eq('projected')->exec();
        $this->dao->update(TABLE_STORY)->set('stage')->eq('projected')->where('id')->eq($storyID)->exec();
    }

    /* Get current stage and set as default value. */
    $currentStage = $story->stage;
    $stage = $currentStage;

    /* Cycle all tasks, get counts of every type and every status. */
    $branchStatusList = array();
    $branchDevelTasks = array();
    $branchTestTasks  = array();
    $statusList['devel'] = array('wait' => 0, 'doing' => 0, 'done'=> 0);
    $statusList['test']  = array('wait' => 0, 'doing' => 0, 'done'=> 0);

    foreach($tasks as $type => $typeTasks)
    {
        foreach($typeTasks as $task)
        {
            $status = $task->status ? $task->status : 'wait';
            $status = $status == 'closed' ? 'done' : $status;

            $branch = $projects[$task->project];

            if(!isset($branchStatusList[$branch])) $branchStatusList[$branch] = $statusList;
            //计算开发和测试各状态的任务数量
            if ($task->type != 'test')
            {
                $branchStatusList[$branch]['devel'][$status] ++;
            }
            else
            {
                $branchStatusList[$branch]['test'][$status] ++;
            }

            $develTask = explode(',', $this->config->story->develTask);

            if(in_array($type, $develTask))
            {
                if(!isset($branchDevelTasks[$branch])) $branchDevelTasks[$branch] = 0;
                $branchDevelTasks[$branch] ++;
            }
            elseif($type == 'test')
            {
                if(!isset($branchTestTasks[$branch])) $branchTestTasks[$branch] = 0;
                $branchTestTasks[$branch] ++;
            }
        }
    }

    /**
     * Judge stage according to the devel and test tasks' status.
     *
     * 1. one doing devel task, all test tasks waiting, set stage as developing.
     * 2. all devel tasks done, all test tasks waiting, set stage as developed.
     * 3. one test task doing, set stage as testing.
     * 4. all test tasks done, still some devel tasks not done(wait, doing), set stage as testing.
     * 5. all test tasks done, all devel tasks done, set stage as tested.
     */
    foreach($branchStatusList as $branch => $statusList)
    {
        $testTasks  = isset($branchTestTasks[$branch]) ? $branchTestTasks[$branch] : 0;
        $develTasks = isset($branchDevelTasks[$branch]) ? $branchDevelTasks[$branch] : 0;
        if($statusList['devel']['doing'] > 0 and $statusList['test']['wait'] == $testTasks) $stage = 'developing';
        if($statusList['devel']['done'] == $develTasks and $develTasks > 0 and $statusList['test']['wait'] == $testTasks) $stage = 'developed';
        if($statusList['test']['doing'] > 0) $stage = 'testing';
        if(($statusList['devel']['wait'] > 0 or $statusList['devel']['doing'] > 0) and $statusList['test']['done'] == $testTasks and $testTasks > 0) $stage = 'testing';
        if($statusList['devel']['done'] == $develTasks and $develTasks > 0 and $statusList['test']['done'] == $testTasks and $testTasks > 0) $stage = 'tested';

        $stages[$branch] = $stage;
    }

    $releases = $this->dao->select('*')->from(TABLE_RELEASE)->where("CONCAT(',', stories, ',')")->like("%,$storyID,%")->andWhere('deleted')->eq(0)->fetchPairs('branch', 'branch');
    foreach($releases as $branch) $stages[$branch] = 'released';

    if(empty($stages)) return;
    if($hasBranch)
    {
        $stageList   = join(',', array_keys($this->lang->story->stageList));
        $minStagePos = strlen($stageList);
        $minStage    = '';
        foreach($stages as $branch => $stage)
        {
            $this->dao->insert(TABLE_STORYSTAGE)->set('story')->eq($storyID)->set('branch')->eq($branch)->set('stage')->eq($stage)->exec();
            if(strpos($stageList, $stage) !== false and strpos($stageList, $stage) < $minStagePos)
            {
                $minStage    = $stage;
                $minStagePos = strpos($stageList, $stage);
            }
        }
        $this->dao->update(TABLE_STORY)->set('stage')->eq($minStage)->where('id')->eq($storyID)->exec();
    }
    else
    {
        $this->dao->update(TABLE_STORY)->set('stage')->eq(current($stages))->where('id')->eq($storyID)->exec();
    }

    return;
}
