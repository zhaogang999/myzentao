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
          <th class="w-id"><?php echo $lang->report->taskSum;?></th>
          <th class="w-id"><?php echo $lang->report->taskWaitSum;?></th>
          <th class="w-id"><?php echo $lang->report->taskDoingSum;?></th>
          <th class="w-id"><?php echo $lang->report->taskDoneSum;?></th>
          <th class="w-id"><?php echo $lang->report->taskPauseSum;?></th>
          <th class="w-id"><?php echo $lang->report->taskCancelSum;?></th>
          <th class="w-id"><?php echo $lang->report->taskClosedSum;?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($info as $id  =>$project):?>
      <tr class="a-center">
        <td align="center"><?php echo $id;?></td>
        <td><?php echo $project->projectInfo->name;?></td>
        <td align="center"><?php echo $project->newStoryTaskStatusSum?array_sum($project->newStoryTaskStatusSum):0;?></td>
        <td align="center"><?php echo isset($project->newStoryTaskStatusSum['wait'])?$project->newStoryTaskStatusSum['wait']:0;?></td>
        <td align="center"><?php echo isset($project->newStoryTaskStatusSum['doing'])?$project->newStoryTaskStatusSum['doing']:0;?></td>
        <td align="center"><?php echo isset($project->newStoryTaskStatusSum['done'])?$project->newStoryTaskStatusSum['done']:0;?></td>
        <td align="center"><?php echo isset($project->newStoryTaskStatusSum['pause'])?$project->newStoryTaskStatusSum['pause']:0;?></td>
        <td align="center"><?php echo isset($project->newStoryTaskStatusSum['cancel'])?$project->newStoryTaskStatusSum['cancel']:0;?></td>
        <td align="center"><?php echo isset($project->newStoryTaskStatusSum['closed'])?$project->newStoryTaskStatusSum['closed']:0;?></td>
      </tr>
    <?php endforeach;?>
    </tbody>
  </table> 
</div>
<?php include '../../../common/view/footer.html.php';?>
