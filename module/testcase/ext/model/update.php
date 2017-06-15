<?php
/**
 * Update a case.
 *
 * @param  int    $caseID
 * @access public
 * @return void
 */
public function update($caseID)
{
    $oldCase     = $this->getById($caseID);
    if(isset($_POST['lastEditedDate']) and $oldCase->lastEditedDate != $this->post->lastEditedDate)
    {
        dao::$errors[] = $this->lang->error->editedByOther;
        return false;
    }

    $now         = helper::now();
    $stepChanged = false;
    $steps       = array();

    //---------------- Judge steps changed or not.-------------------- */

    /* Remove the empty setps in post. */
    foreach($this->post->steps as $key => $desc)
    {
        $desc = trim($desc);
        if(!empty($desc)) $steps[] = array('desc' => $desc, 'expect' => trim($this->post->expects[$key]));
    }

    /* If step count changed, case changed. */
    if(count($oldCase->steps) != count($steps))
    {
        $stepChanged = true;
    }
    else
    {
        /* Compare every step. */
        foreach($oldCase->steps as $key => $oldStep)
        {
            if(trim($oldStep->desc) != trim($steps[$key]['desc']) or trim($oldStep->expect) != $steps[$key]['expect'])
            {
                $stepChanged = true;
                break;
            }
        }
    }
    $version = $stepChanged ? $oldCase->version + 1 : $oldCase->version;

    $case = fixer::input('post')
        ->add('lastEditedBy', $this->app->user->account)
        ->add('lastEditedDate', $now)
        ->add('version', $version)
        ->setIF($this->post->story != false and $this->post->story != $oldCase->story, 'storyVersion', $this->loadModel('story')->getVersion($this->post->story))
        ->setDefault('story,branch', 0)
        ->join('stage', ',')
        ->remove('comment,steps,expects,files,labels,stepType')
        ->get();
    if($this->forceReview() and $stepChanged) $case->status = 'wait';
    $this->dao->update(TABLE_CASE)->data($case)->autoCheck()->batchCheck($this->config->testcase->edit->requiredFields, 'notempty')->where('id')->eq((int)$caseID)->exec();
    if(!$this->dao->isError())
    {
        if($stepChanged)
        {
            $parentStepID = 0;
            foreach($this->post->steps as $stepID => $stepDesc)
            {
                if(empty($stepDesc)) continue;
                $stepType = $this->post->stepType;
                $step = new stdclass();
                $step->type    = ($stepType[$stepID] == 'item' and $parentStepID == 0) ? 'step' : $stepType[$stepID];
                $step->parent  = ($step->type == 'item') ? $parentStepID : 0;
                $step->case    = $caseID;
                $step->version = $version;
                $step->desc    = htmlspecialchars($stepDesc);
                //不转义特殊字符
                //$step->expect  = htmlspecialchars($this->post->expects[$stepID]);
                $step->expect  = $this->post->expects[$stepID];

                $this->dao->insert(TABLE_CASESTEP)->data($step)->autoCheck()->exec();
                if($step->type == 'group') $parentStepID = $this->dao->lastInsertID();
                if($step->type == 'step')  $parentStepID = 0;
            }
        }

        /* Join the steps to diff. */
        if($stepChanged)
        {
            $oldCase->steps = $this->joinStep($oldCase->steps);
            $case->steps    = $this->joinStep($this->getById($caseID, $version)->steps);
        }
        else
        {
            unset($oldCase->steps);
        }
        return common::createChanges($oldCase, $case);
    }
}
