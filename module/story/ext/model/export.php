<?php
helper::import(dirname(dirname(dirname(__FILE__))) . "/control.php");
class mystory extends story
{
    public function export($productID, $orderBy, $projectID=0)
    {
        /* format the fields of every story in order to export data. */
        if($_POST)
        {
            $this->story->setListValue($productID);
            $this->loadModel('file');
            $this->loadModel('branch');
            $storyLang   = $this->lang->story;
            $storyConfig = $this->config->story;

            /* Create field lists. */
            $fields = $this->post->exportFields ? $this->post->exportFields : explode(',', $storyConfig->list->exportFields);
            foreach($fields as $key => $fieldName)
            {
                $fieldName = trim($fieldName);
                $fields[$fieldName] = isset($storyLang->$fieldName) ? $storyLang->$fieldName : $fieldName;
                unset($fields[$key]);
            }

            /* Get stories. */
            $stories = array();
            if($this->session->storyOnlyCondition)
            {
                $stories = $this->dao->select('*')->from(TABLE_STORY)->where($this->session->storyQueryCondition)
                    ->beginIF($this->post->exportType == 'selected')->andWhere('id')->in($this->cookie->checkedItem)->fi()
                    ->orderBy($orderBy)->fetchAll('id');
            }
            else
            {
                $stmt = $this->dbh->query($this->session->storyQueryCondition . ($this->post->exportType == 'selected' ? " AND t2.id IN({$this->cookie->checkedItem})" : '') . " ORDER BY " . strtr($orderBy, '_', ' '));

                while($row = $stmt->fetch()) $stories[$row->id] = $row;
            }

            /* Get users, products and projects. */
            $users    = $this->loadModel('user')->getPairs('noletter');
            $products = $this->loadModel('product')->getPairs('nocode');

            /* Get related objects id lists. */
            $relatedProductIdList = array();
            $relatedModuleIdList  = array();
            $relatedStoryIdList   = array();
            $relatedPlanIdList    = array();
            $relatedBranchIdList  = array();

            foreach($stories as $story)
            {
                $relatedProductIdList[$story->product] = $story->product;
                $relatedModuleIdList[$story->module]   = $story->module;
                $relatedPlanIdList[$story->plan]       = $story->plan;
                $relatedBranchIdList[$story->branch]   = $story->branch;

                /* Process related stories. */
                $relatedStories = $story->childStories . ',' . $story->linkStories . ',' . $story->duplicateStory;
                $relatedStories = explode(',', $relatedStories);
                foreach($relatedStories as $storyID)
                {
                    if($storyID) $relatedStoryIdList[$storyID] = trim($storyID);
                }
            }

            /* Get related objects title or names. */
            $productsType   = $this->dao->select('id, type')->from(TABLE_PRODUCT)->where('id')->in($relatedProductIdList)->fetchPairs();
            $relatedModules = $this->dao->select('id, name')->from(TABLE_MODULE)->where('id')->in($relatedModuleIdList)->fetchPairs();
            $relatedPlans   = $this->dao->select('id, title')->from(TABLE_PRODUCTPLAN)->where('id')->in(join(',', $relatedPlanIdList))->fetchPairs();
            $relatedStories = $this->dao->select('id,title')->from(TABLE_STORY) ->where('id')->in($relatedStoryIdList)->fetchPairs();
            $relatedFiles   = $this->dao->select('id, objectID, pathname, title')->from(TABLE_FILE)->where('objectType')->eq('story')->andWhere('objectID')->in(@array_keys($stories))->fetchGroup('objectID');
            $relatedSpecs   = $this->dao->select('*')->from(TABLE_STORYSPEC)->where('`story`')->in(@array_keys($stories))->orderBy('version desc')->fetchGroup('story');
            $relatedBranch  = array('0' => $this->lang->branch->all) + $this->dao->select('id, name')->from(TABLE_BRANCH)->where('id')->in($relatedBranchIdList)->fetchPairs();

            //需求1197 项目需求导出时，增加TBC字段的内容；
            /* Count T B C */
            $storiesAB = $this->loadModel('story')->getProjectStories($projectID);
            $storyIdList = array_keys($storiesAB);
            $taskCount = $this->loadModel('task')->getStoryTaskCounts($storyIdList,$projectID);
            $bugCount  = $this->loadModel('bug')->getStoryBugCounts($storyIdList,$projectID);
            $caseCount = $this->loadModel('testcase')->getStoryCaseCounts($storyIdList);

            foreach($stories as $story)
            {
                $story->spec   = '';
                $story->verify = '';
                if(isset($relatedSpecs[$story->id]))
                {
                    $storySpec     = $relatedSpecs[$story->id][0];
                    $story->title  = $storySpec->title;
                    $story->spec   = $storySpec->spec;
                    $story->verify = $storySpec->verify;
                }

                if($this->post->fileType == 'csv')
                {
                    $story->spec = htmlspecialchars_decode($story->spec);
                    $story->spec = str_replace("<br />", "\n", $story->spec);
                    $story->spec = str_replace('"', '""', $story->spec);
                    $story->spec = str_replace('&nbsp;', ' ', $story->spec);

                    $story->verify = htmlspecialchars_decode($story->verify);
                    $story->verify = str_replace("<br />", "\n", $story->verify);
                    $story->verify = str_replace('"', '""', $story->verify);
                    $story->verify = str_replace('&nbsp;', ' ', $story->verify);
                }
                /* fill some field with useful value. */
                if(isset($products[$story->product]))       $story->product = $products[$story->product] . "(#$story->product)";
                if(isset($relatedModules[$story->module]))  $story->module  = $relatedModules[$story->module] . "(#$story->module)";
                if(isset($relatedBranch[$story->branch]))   $story->branch  = $relatedBranch[$story->branch] . "(#$story->branch)";
                if(isset($story->plan))
                {
                    $plans = '';
                    foreach(explode(',', $story->plan) as $planID)
                    {
                        if(empty($planID)) continue;
                        if(isset($relatedPlans[$planID]))$plans .= $relatedPlans[$planID] . "(#$planID) ";
                    }
                    $story->plan = $plans;
                }
                if(isset($relatedStories[$story->duplicateStory])) $story->duplicateStory = $relatedStories[$story->duplicateStory];

                if(isset($storyLang->priList[$story->pri]))             $story->pri          = $storyLang->priList[$story->pri];
                if(isset($storyLang->statusList[$story->status]))       $story->status       = $storyLang->statusList[$story->status];
                if(isset($storyLang->stageList[$story->stage]))         $story->stage        = $storyLang->stageList[$story->stage];
                if(isset($storyLang->reasonList[$story->closedReason])) $story->closedReason = $storyLang->reasonList[$story->closedReason];
                if(isset($storyLang->sourceList[$story->source]))       $story->source       = $storyLang->sourceList[$story->source];

                if(isset($users[$story->openedBy]))     $story->openedBy     = $users[$story->openedBy];
                if(isset($users[$story->assignedTo]))   $story->assignedTo   = $users[$story->assignedTo];
                if(isset($users[$story->lastEditedBy])) $story->lastEditedBy = $users[$story->lastEditedBy];
                if(isset($users[$story->closedBy]))     $story->closedBy     = $users[$story->closedBy];

                $story->openedDate     = substr($story->openedDate, 0, 10);
                $story->assignedDate   = substr($story->assignedDate, 0, 10);
                $story->lastEditedDate = substr($story->lastEditedDate, 0, 10);
                $story->closedDate     = substr($story->closedDate, 0, 10);

                if($story->linkStories)
                {
                    $tmpLinkStories = array();
                    $linkStoriesIdList = explode(',', $story->linkStories);
                    foreach($linkStoriesIdList as $linkStoryID)
                    {
                        $linkStoryID = trim($linkStoryID);
                        $tmpLinkStories[] = isset($relatedStories[$linkStoryID]) ? $relatedStories[$linkStoryID] : $linkStoryID;
                    }
                    $story->linkStories = join("; \n", $tmpLinkStories);
                }

                if($story->childStories)
                {
                    $tmpChildStories = array();
                    $childStoriesIdList = explode(',', $story->childStories);
                    foreach($childStoriesIdList as $childStoryID)
                    {
                        $childStoryID = trim($childStoryID);
                        $tmpChildStories[] = isset($relatedStories[$childStoryID]) ? $relatedStories[$childStoryID] : $childStoryID;
                    }
                    $story->childStories = join("; \n", $tmpChildStories);
                }

                /* Set related files. */
                $story->files = '';
                if(isset($relatedFiles[$story->id]))
                {
                    foreach($relatedFiles[$story->id] as $file)
                    {
                        $fileURL = common::getSysURL() . $this->file->webPath . $file->pathname;
                        $story->files .= html::a($fileURL, $file->title, '_blank') . '<br />';
                    }
                }

                $story->mailto = trim(trim($story->mailto), ',');
                $mailtos = explode(',', $story->mailto);
                $story->mailto = '';
                foreach($mailtos as $mailto)
                {
                    $mailto = trim($mailto);
                    if(isset($users[$mailto])) $story->mailto .= $users[$mailto] . ',';
                }

                $story->reviewedBy = trim(trim($story->reviewedBy), ',');
                $reviewedBys = explode(',', $story->reviewedBy);
                $story->reviewedBy = '';
                foreach($reviewedBys as $reviewedBy)
                {
                    $reviewedBy = trim($reviewedBy);
                    if(isset($users[$reviewedBy])) $story->reviewedBy .= $users[$reviewedBy] . ',';
                }

               $story->taskCountAB = $taskCount[$story->id];
               $story->bugCountAB = $bugCount[$story->id];
               $story->caseCountAB = $caseCount[$story->id];
            }

            if(!(in_array('platform', $productsType) or in_array('branch', $productsType))) unset($fields['branch']);// If products's type are normal, unset branch field.

            $this->post->set('fields', $fields);
            $this->post->set('rows', $stories);
            $this->post->set('kind', 'story');
            $this->fetch('file', 'export2' . $this->post->fileType, $_POST);
        }
        //需求1197 项目需求导出时，增加TBC字段的内容；如果是项目下需求导出，增加T,B,C字段；
        if ($projectID!=0)
        {
            $this->config->story->list->exportFields = str_replace('stage,', 'stage, taskCountAB, bugCountAB, caseCountAB,', $this->config->story->list->exportFields);
        }

        $this->view->allExportFields = $this->config->story->list->exportFields;
        $this->view->customExport    = true;
        $this->display();
    }
}
