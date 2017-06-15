<?php include '../../../common/view/header.html.php';?>
<div id='titlebar'>
  <div class='heading'>
    <span class='prefix'><?php echo html::icon($lang->icons['report-file']);?></span>
    <strong> <?php echo $title;?></strong>
  </div>
</div>
<div class='side'>
  <?php include '../../view/blockreportlist.html.php';?>
  <div class='panel panel-body' style='padding: 10px 6px'>
    <div class='text proversion'>
      <strong class='text-danger small text-latin'>PRO</strong> &nbsp;<span class='text-important'><?php echo $lang->report->proVersion;?></span>
    </div>
  </div>
</div>
<div class='main'>
  <table class='table table-condensed table-striped table-bordered tablesorter active-disabled' style="word-break:break-all; word-wrap:break-all;">
    <thead>
        <tr class='colhead'>
            <th class='w-id'><?php echo $lang->report->projectID;?></th>
            <th class='w-200px'><?php echo $lang->report->projectName;?></th>
            <th class="w-id"><?php echo $lang->report->develSum;?></th>
            <th class="w-id"><?php echo $lang->report->taskWaitSum;?></th>
            <th class="w-id"><?php echo $lang->report->taskDoingSum;?></th>
            <th class="w-id"><?php echo $lang->report->taskDoneSum;?></th>
            <th class="w-id"><?php echo $lang->report->taskPauseSum;?></th>
            <th class="w-id"><?php echo $lang->report->taskCancelSum;?></th>
            <th class="w-id"><?php echo $lang->report->taskClosedSum;?></th>
            <th class="w-id"><?php echo $lang->report->testSum;?></th>
            <th class="w-id"><?php echo $lang->report->taskWaitSum;?></th>
            <th class="w-id"><?php echo $lang->report->taskDoingSum;?></th>
            <th class="w-id"><?php echo $lang->report->taskDoneSum;?></th>
            <th class="w-id"><?php echo $lang->report->taskPauseSum;?></th>
            <th class="w-id"><?php echo $lang->report->taskCancelSum;?></th>
            <th class="w-id"><?php echo $lang->report->taskClosedSum;?></th>
            <th class="w-id"><?php echo $lang->report->taskSum;?></th>
            <th class="w-id"><?php echo $lang->report->delayedTaskSum;?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($info as $id  =>$project):?>
      <tr class="a-center">
        <td align="center"><?php echo $id;?></td>
        <td><?php echo $project->projectInfo->name;?></td>
        <td align="center"><?php echo $project->develTaskSum;?></td>
        <td align="center"><?php echo isset($project->newDevelTaskStatusSum['wait'])?$project->newDevelTaskStatusSum['wait']:0;?></td>
          <td align="center"><?php echo isset($project->newDevelTaskStatusSum['doing'])?$project->newDevelTaskStatusSum['doing']:0;?></td>
          <td align="center"><?php echo isset($project->newDevelTaskStatusSum['done'])?$project->newDevelTaskStatusSum['done']:0;?></td>
          <td align="center"><?php echo isset($project->newDevelTaskStatusSum['pause'])?$project->newDevelTaskStatusSum['pause']:0;?></td>
          <td align="center"><?php echo isset($project->newDevelTaskStatusSum['cancel'])?$project->newDevelTaskStatusSum['cancel']:0;?></td>
          <td align="center"><?php echo isset($project->newDevelTaskStatusSum['closed'])?$project->newDevelTaskStatusSum['closed']:0;?></td>
        <td align="center"><?php echo $project->testSum;?></td>
          <td align="center"><?php echo isset($project->newTestStatusSum['wait'])?$project->newTestStatusSum['wait']:0;?></td>
          <td align="center"><?php echo isset($project->newTestStatusSum['doing'])?$project->newTestStatusSum['doing']:0;?></td>
          <td align="center"><?php echo isset($project->newTestStatusSum['done'])?$project->newTestStatusSum['done']:0;?></td>
          <td align="center"><?php echo isset($project->newTestStatusSum['pause'])?$project->newTestStatusSum['pause']:0;?></td>
          <td align="center"><?php echo isset($project->newTestStatusSum['cancel'])?$project->newTestStatusSum['cancel']:0;?></td>
          <td align="center"><?php echo isset($project->newTestStatusSum['closed'])?$project->newTestStatusSum['closed']:0;?></td>
          <td align="center"><?php echo $project->taskSum;?></td>
          <td align="center"><?php echo $project->delayedTaskSum;?></td>
      </tr>
    <?php endforeach;?>
    </tbody>
  </table> 
</div>
<?php include '../../../common/view/footer.html.php';?>
