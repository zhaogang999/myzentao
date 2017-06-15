<?php
/**
 * The view file of task module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     task
 * @version     $Id: view.html.php 4808 2013-06-17 05:48:13Z zhujinyonging@gmail.com $
 * @link        http://www.zentao.net
 */
?>
<?php include '../../../common/view/header.html.php';?>
<?php include '../../../common/view/kindeditor.html.php';?>
<div id='titlebar'>
  <div class='heading'>
    <span class='prefix'><?php echo html::icon($lang->icons['task']);?> <strong><?php echo $task->id;?></strong></span>
    <strong style='color: <?php echo $task->color; ?>'><?php echo $task->name;?></strong>
    <?php if($task->deleted):?>
    <span class='label label-danger'><?php echo $lang->task->deleted;?></span>
    <?php endif; ?>
    <?php if($task->fromBug != 0):?>
    <small> <?php echo html::icon($lang->icons['bug']) . " {$lang->task->fromBug}$lang->colon$task->fromBug"; ?></small>
    <?php endif;?>
  </div>
  <div class='actions'>
    <?php
    $browseLink  = $app->session->taskList != false ? $app->session->taskList : $this->createLink('project', 'browse', "projectID=$task->project");
    $actionLinks = '';
    if(!$task->deleted)
    {
        ob_start();
        echo "<div class='btn-group'>";
        common::printIcon('task', 'assignTo',       "projectID=$task->project&taskID=$task->id", $task, 'button', '', '', 'iframe', true);
        common::printIcon('task', 'start',          "taskID=$task->id", $task, 'button', '', '', 'iframe', true);
        common::printIcon('task', 'restart',        "taskID=$task->id", $task, 'button', '', '', 'iframe', true);
        common::printIcon('task', 'recordEstimate', "taskID=$task->id", $task, 'button', '', '', 'iframe', true);
        common::printIcon('task', 'pause',          "taskID=$task->id", $task, 'button', '', '', 'iframe', true);
        common::printIcon('task', 'finish',         "taskID=$task->id", $task, 'button', '', '', 'iframe showinonlybody text-success', true);
        common::printIcon('task', 'close',          "taskID=$task->id", $task, 'button', '', '', 'iframe', true);
        common::printIcon('task', 'activate',       "taskID=$task->id", $task, 'button', '', '', 'iframe text-success', true);
        common::printIcon('task', 'cancel',         "taskID=$task->id", $task, 'button', '', '', 'iframe', true);
        echo '</div>';

        echo "<div class='btn-group'>";
        common::printIcon('task', 'edit',  "taskID=$task->id");
        common::printCommentIcon('task');
        common::printIcon('task', 'create', "productID=0&storyID=0&moduleID=0&taskID=$task->id", '', 'button', 'copy');
        common::printIcon('task', 'delete', "projectID=$task->project&taskID=$task->id", '', 'button', '', 'hiddenwin');
        echo '</div>';

        echo "<div class='btn-group'>";
        common::printRPN($browseLink, $preAndNext);
        echo '</div>';

        $actionLinks = ob_get_contents();
        ob_end_clean();
        echo $actionLinks;
    }
    else
    {
        common::printRPN($browseLink);
    }
    ?>
  </div>
</div>
<div class='row-table'>
  <div class='col-main'>
    <div class='main'>
      <fieldset>
        <legend><?php echo $lang->task->legendDesc;?></legend>
        <div class='article-content'><?php echo $task->desc;?></div>
      </fieldset>
      <?php if($task->fromBug != 0):?>
      <fieldset>
        <legend><?php echo $lang->bug->steps;?></legend>
        <div class='article-content'><?php echo $task->bugSteps;?></div>
      </fieldset>
      <?php else:?>
      <fieldset>
        <legend><?php echo $lang->task->storySpec;?></legend>
        <div class='article-content'><?php echo $task->storySpec;?></div>
        <?php echo $this->fetch('file', 'printFiles', array('files' => $task->storyFiles, 'fieldset' => 'false'));?>
      </fieldset>
      <fieldset>
        <legend><?php echo $lang->task->storyVerify;?></legend>
        <div class='article-content'><?php echo $task->storyVerify;?></div>
      </fieldset>
      <?php endif;?>

      <?php if(isset($task->cases) and $task->cases):?>
              <fieldset>
                  <legend><?php echo $lang->task->case;?></legend>
                  <div class='article-content'>
                      <ul class='list-unstyled'>
                        <?php foreach($task->cases as $caseID => $case) echo '<li>' . html::a($this->createLink('testcase', 'view', "caseID=$caseID", '', true), "#$caseID " . $case, '', "data-toggle='modal' data-type='iframe' data-width='90%'") . '</li>';?>
                      </ul>
                  </div>
              </fieldset>
      <?php endif;?>

      <?php echo $this->fetch('file', 'printFiles', array('files' => $task->files, 'fieldset' => 'true'));?>
<!--新增-->
      <?php if($task->source == 'QA'):?>
        <fieldset>
          <legend><?php echo $lang->task->noItem;?></legend>
          <?php if($auditDetails != array()):?>
            <?php foreach ($auditDetails as $auditDetail):?>
              <table class='table table-form'>
                <tr>
                  <th class='w-80px'><?php echo $lang->task->auditID;?></th>
                  <td class='w-300px'>
                    <?php echo $auditDetail->auditID;?>
                  </td>
                  <th class='w-90px'><?php echo $lang->task->noDec;?></th>
                  <td class='w-300px'>
                    <?php echo $auditDetail->noDec;?>
                  </td>
                </tr>
                <tr>
                  <th><?php echo $lang->task->noType;?></th>
                  <td>
                    <?php echo $lang->task->noTypeList["$auditDetail->noType"];?>
                  </td>
                  <th><?php echo $lang->task->serious;?></th>
                  <td>
                    <?php echo $lang->task->seriousList["$auditDetail->serious"];;?>
                  </td>
                </tr>
                <tr>
                  <th><?php echo $lang->task->cause;?></th>
                  <td class="break-word">
                    <?php echo $auditDetail->cause;?>
                  </td>
                  <th><?php echo $lang->task->measures;?></th>
                  <td class="break-word">
                    <?php echo $auditDetail->measures;?>
                  </td>
                </tr>
              </table>
            <?php endforeach;?>
          <?php else:?>
            无不符合项
          <?php endif;?>
        </fieldset>
      <?php endif;?>
      <?php if(isset($review)):?>
        <fieldset>
          <legend><?php echo $lang->task->review;?></legend>
          <table class='table table-form with-border'>
            <tr>
              <th class='w-80px'><?php echo $lang->task->fileNO;?></th>
              <td class='w-400px'>
                <?php echo $review->fileNO;?>
              </td>
              <th class='w-80px'><?php echo $lang->task->recorder;?></th>
              <td class='w-400px'>
                <?php echo $review->recorder;?>
              </td>
            </tr>
            <tr>
              <th class='w-80px'><?php echo $lang->task->reviewName;?></th>
              <td class='w-350px'>
                <?php echo $review->reviewName;?>
              </td>
              <th class='w-80px'><?php echo $lang->task->reviewDate;?></th>
              <td class='w-350px'>
                <?php echo $review->reviewDate;?>
              </td>
            </tr>
            <tr>
              <th class='w-80px'><?php echo $lang->task->doc;?></th>
              <td class='w-350px'>
                <?php echo $review->doc;?>
              </td>
              <th class='w-80px'><?php echo $lang->task->reviewScope;?></th>
              <td class='w-350px'>
                <?php echo $review->reviewScope;?>
              </td>
            </tr>
            <tr>
              <th class='w-80px'><?php echo $lang->task->referenceDoc;?></th>
              <td class='w-350px'>
                <?php echo $review->referenceDoc;?>
              </td>
              <th class='w-80px'><?php echo $lang->task->reviewPlace;?></th>
              <td class='w-350px'>
                <?php echo $review->reviewPlace;?>
              </td>
            </tr>
            <tr>
              <th class='w-80px'><?php echo $lang->task->reference;?></th>
              <td class='w-350px'>
                <?php echo $review->reference;?>
              </td>
              <th class='w-80px'><?php echo $lang->task->effort;?></th>
              <td class='w-350px'>
                <?php echo $review->effort;?>
              </td>
            </tr>
            <tr>
              <th class='w-80px'><?php echo $lang->task->pages;?></th>
              <td class='w-350px'>
                <?php echo $review->pages;?>
              </td>
              <th class='w-80px'><?php echo $lang->task->conclusion;?></th>
              <td class='w-350px'>
                <?php echo $review->conclusion;?>
              </td>
            </tr>
            <tr>
              <th class='w-80px'><?php echo $lang->task->reviewers;?></th>
              <td colspan="3">
                <?php echo $review->reviewers;?>
              </td>
            </tr>
          </table>
        </fieldset>
        <fieldset>
          <legend><?php echo $lang->task->problem;?></legend>
          <?php if($reviewDetails != array()):?>
            <?php foreach($reviewDetails as $reviewDetail):?>
              <?php /*if ($reviewDetail != ''):*/?>
                <table class='table table-form with-border'>
                  <tr>
                    <th class='w-80px'><?php echo $lang->task->number;?></th>
                    <td class='w-350px'>
                      <?php echo $reviewDetail->number;?>
                    </td>
                    <th class='w-80px'><?php echo $lang->task->reviewer;?></th>
                    <td class='w-350px'>
                      <?php echo $reviewDetail->reviewer;?>
                    </td>
                  </tr>
                  <tr>
                    <th class='w-80px'><?php echo $lang->task->item;?></th>
                    <td class='w-350px'>
                      <?php echo $reviewDetail->item;?>
                    </td>
                    <th class='w-80px'><?php echo $lang->task->line;?></th>
                    <td class='w-350px'>
                      <?php echo $reviewDetail->line;?>
                    </td>
                  </tr>
                  <tr>
                    <th class='w-80px'><?php echo $lang->task->severity;?></th>
                    <td class='w-350px'>
                      <?php echo $reviewDetail->severity;?>
                    </td>
                    <th class='w-80px'><?php echo $lang->task->description;?></th>
                    <td class='w-350px'>
                      <?php echo $reviewDetail->description;?>
                    </td>
                  </tr>
                  <tr>
                    <th class='w-80px'><?php echo $lang->task->proposal;?></th>
                    <td class='w-350px'>
                      <?php echo $reviewDetail->proposal;?>
                    </td>
                    <th class='w-80px'><?php echo $lang->task->changed;?></th>
                    <td class='w-350px'>
                      <?php echo $reviewDetail->changed;?>
                    </td>
                  </tr>
                  <tr>
                    <th class='w-80px'><?php echo $lang->task->action;?></th>
                    <td class='w-350px'>
                      <?php echo $reviewDetail->action;?>
                    </td>
                    <th class='w-80px'><?php echo $lang->task->chkd;?></th>
                    <td class='w-350px'>
                      <?php echo $reviewDetail->chkd;?>
                    </td>
                  </tr>
                </table>
              <?php /*endif;*/?>
            <?php endforeach;?>
          <?php else:?>
              无评审问题
          <?php endif;?>
        </fieldset>
      <?php endif; ?>
      
      <?php include '../../../common/view/action.html.php';?>
      <div class='actions'> <?php if(!$task->deleted) echo $actionLinks;?></div>
      <fieldset id='commentBox' class='hide'>
        <legend><?php echo $lang->comment;?></legend>
        <form method='post' action='<?php echo inlink('edit', "taskID=$task->id&comment=true")?>'>
          <div class="form-group"><?php echo html::textarea('comment', '',"rows='5' class='w-p100'");?></div>
          <?php echo html::submitButton() . html::backButton();?>
        </form>
      </fieldset>
    </div>
  </div>
  <div class='col-side'>
    <div class='main main-side'>
      <fieldset>
        <legend><?php echo $lang->task->legendBasic;?></legend>
        <table class='table table-data table-condensed table-borderless'> 
          <tr>
            <th class='w-80px'><?php echo $lang->task->project;?></th>
            <td><?php if(!common::printLink('project', 'task', "projectID=$task->project", $project->name)) echo $project->name;?></td>
          </tr>  
          <tr>
            <th><?php echo $lang->task->module;?></th>
            <?php
            $moduleTitle = '';
            ob_start();
            if(empty($modulePath))
            {
                $moduleTitle .= '/';
                echo "/";
            }
            else
            {
                if($product)
                {
                    $moduleTitle .= $product->name . '/';
                    echo $product->name . $lang->arrow;
                }
               foreach($modulePath as $key => $module)
               {
                   $moduleTitle .= $module->name;
                   if(!common::printLink('project', 'task', "projectID=$task->project&browseType=byModule&param=$module->id", $module->name)) echo $module->name;
                   if(isset($modulePath[$key + 1]))
                   {
                       $moduleTitle .= '/';
                       echo $lang->arrow;
                   }
               }
            }
            $printModule = ob_get_contents();
            ob_end_clean();
            ?>
            <td title='<?php echo $moduleTitle?>'><?php echo $printModule?></td>
          </tr>  
          <tr class='nofixed'>
            <th><?php echo $lang->task->story;?></th>
            <td>
            <?php 
            if($task->storyTitle and !common::printLink('story', 'view', "storyID=$task->story", $task->storyTitle, '', "class='iframe' data-width='80%'", true, true)) echo $task->storyTitle;
            if($task->needConfirm)
            {
                echo "(<span class='warning'>{$lang->story->changed}</span> ";
                echo html::a($this->createLink('task', 'confirmStoryChange', "taskID=$task->id"), $lang->confirm, 'hiddenwin');
                echo ")";
            }
            ?>
            </td>
          </tr>
          <?php if($task->fromBug):?>
          <tr>
            <th><?php echo $lang->task->fromBug;?></th>
            <td><?php echo html::a($this->createLink('bug', 'view', "bugID=$task->fromBug"), "#$task->fromBug " . $fromBug->title, '_blank');?></td> 
          </tr>
          <?php endif;?>
          <tr>
            <th><?php echo $lang->task->assignedTo;?></th>
            <td><?php echo $task->assignedTo ? $task->assignedToRealName . $lang->at . $task->assignedDate : '';?></td> 
          </tr>
          <!--禅道任务增加关键字字段；需求：858；行号：361-365-->
          <tr>
            <th><?php echo $lang->task->keywords;?></th>
            <td><?php echo $task->keywords;?></td>
          </tr>
          <tr>
            <th><?php echo $lang->task->type;?></th>
            <td><?php echo $lang->task->typeList[$task->type];?></td>
          </tr>
          <tr>
            <th><?php echo $lang->task->status;?></th>
            <td><?php $lang->show($lang->task->statusList, $task->status);?></td>
          </tr>
          <tr>
            <th><?php echo $lang->task->pri;?></th>
            <td><span class='<?php echo 'pri' . zget($lang->task->priList, $task->pri);?>'><?php echo $task->pri == '0' ? '' : zget($lang->task->priList, $task->pri)?></span></td>
          </tr>
          <tr>
            <th><?php echo $lang->task->mailto;?></th>
            <td><?php $mailto = explode(',', str_replace(' ', '', $task->mailto)); foreach($mailto as $account) echo ' ' . zget($users, $account, $account); ?></td>
          </tr>
        </table>
      </fieldset>
      <fieldset>
        <legend><?php echo $lang->task->legendEffort;?></legend>
        <table class='table table-data table-condensed table-borderless'> 
          <tr>
            <th class='w-80px'><?php echo $lang->task->estStarted;?></th>
            <td><?php echo $task->estStarted;?></td>
          </tr>
          <tr>
            <th><?php echo $lang->task->realStarted;?></th>
            <td><?php echo $task->realStarted; ?> </td>
          </tr>  
          <tr>
            <th><?php echo $lang->task->deadline;?></th>
            <td>
            <?php
            echo $task->deadline;
            if(isset($task->delay)) printf($lang->task->delayWarning, $task->delay);
            ?>
            </td>
          </tr>  
          <tr>
            <th><?php echo $lang->task->estimate;?></th>
            <td><?php echo $task->estimate . $lang->workingHour;?></td>
          </tr>  
          <tr>
            <th><?php echo $lang->task->consumed;?></th>
            <td><?php echo round($task->consumed, 2) . $lang->workingHour;?></td>
          </tr>  
          <tr>
            <th><?php echo $lang->task->left;?></th>
            <td><?php echo $task->left . $lang->workingHour;?></td>
          </tr>
        </table>
      </fieldset>
      <fieldset>
        <legend><?php echo $lang->task->legendLife;?></legend>
        <table class='table table-data table-condensed table-borderless'> 
          <tr>
            <th class='w-80px'><?php echo $lang->task->openedBy;?></th>
            <td><?php if($task->openedBy) echo zget($users, $task->openedBy, $task->openedBy) . $lang->at . $task->openedDate;?></td>
          </tr>
          <tr>
            <th><?php echo $lang->task->finishedBy;?></th>
            <td><?php if($task->finishedBy) echo zget($users, $task->finishedBy, $task->finishedBy) . $lang->at . $task->finishedDate;?></td>
          </tr>
          <tr>
            <th><?php echo $lang->task->canceledBy;?></th>
            <td><?php if($task->canceledBy) echo zget($users, $task->canceledBy, $task->canceledBy) . $lang->at . $task->canceledDate;?></td>
          </tr>
          <tr>
            <th><?php echo $lang->task->closedBy;?></th>
            <td><?php if($task->closedBy) echo zget($users, $task->closedBy, $task->closedBy) . $lang->at . $task->closedDate;?></td>
          </tr>
          <tr>
            <th><?php echo $lang->task->closedReason;?></th>
            <td><?php echo $lang->task->reasonList[$task->closedReason];?></td>
          </tr>
          <tr>
            <th><?php echo $lang->task->lastEdited;?></th>
            <td><?php if($task->lastEditedBy) echo zget($users, $task->lastEditedBy, $task->lastEditedBy) . $lang->at . $task->lastEditedDate;?></td>
          </tr>
        </table>
      </fieldset>
    </div>
  </div>
</div>
<?php include '../../../common/view/syntaxhighlighter.html.php';?>
<?php include '../../../common/view/footer.html.php';?>
