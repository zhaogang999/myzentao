<?php
/**
 * Create a case.
 *
 * @param int $bugID
 * @access public
 * @return void
 */
function create($bugID)
{
    $now  = helper::now();
    $case = fixer::input('post')
        ->add('openedBy', $this->app->user->account)
        ->add('openedDate', $now)
        ->add('status', $this->forceReview() ? 'wait' : 'normal')
        ->add('version', 1)
        ->add('fromBug', $bugID)
        ->setIF($this->post->story != false, 'storyVersion', $this->loadModel('story')->getVersion((int)$this->post->story))
        ->remove('steps,expects,files,labels,stepType')
        ->setDefault('story', 0)
        ->join('stage', ',')
        ->get();

    $param = '';
    if(!empty($case->lib))$param = "lib={$case->lib}";
    if(!empty($case->product))$param = "product={$case->product}";
    $result = $this->loadModel('common')->removeDuplicate('case', $case, $param);
    if($result['stop']) return array('status' => 'exists', 'id' => $result['duplicate']);

    /* value of story may be showmore. */
    $case->story = (int)$case->story;
    $this->dao->insert(TABLE_CASE)->data($case)->autoCheck()->batchCheck($this->config->testcase->create->requiredFields, 'notempty')->exec();
    if(!$this->dao->isError())
    {
        $caseID = $this->dao->lastInsertID();
        $this->loadModel('file')->saveUpload('testcase', $caseID);
        $parentStepID = 0;
        foreach($this->post->steps as $stepID => $stepDesc)
        {
            if(empty($stepDesc)) continue;
            $stepType      = $this->post->stepType;
            $step          = new stdClass();
            $step->type    = ($stepType[$stepID] == 'item' and $parentStepID == 0) ? 'step' : $stepType[$stepID];
            $step->parent  = ($step->type == 'item') ? $parentStepID : 0;
            $step->case    = $caseID;
            $step->version = 1;
            $step->desc    = htmlspecialchars($stepDesc);
            //不转义特殊字符
            //$step->expect  = htmlspecialchars($this->post->expects[$stepID]);
            $step->expect  = $this->post->expects[$stepID];

            $this->dao->insert(TABLE_CASESTEP)->data($step)->autoCheck()->exec();
            if($step->type == 'group') $parentStepID = $this->dao->lastInsertID();
            if($step->type == 'step')  $parentStepID = 0;
        }
        return array('status' => 'created', 'id' => $caseID);
    }
}
    