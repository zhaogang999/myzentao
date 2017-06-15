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
          <th class="w-id"><?php echo $lang->report->storySum;?></th>
          <th class="w-id"><?php echo $lang->report->storyWaitSum;?></th>
          <th class="w-id"><?php echo $lang->report->storyDevelopingSum;?></th>
          <th class="w-id"><?php echo $lang->report->storyDevelopedSum;?></th>
          <th class="w-id"><?php echo $lang->report->storyTestingSum;?></th>
          <th class="w-id"><?php echo $lang->report->storyTestedSum;?></th>
          <th class="w-id"><?php echo $lang->report->storyReleasedSum;?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($info as $id  =>$project):?>
      <tr class="a-center">
        <td align="center"><?php echo $id;?></td>
        <td><?php echo $project->projectInfo->name;?></td>
        <td align="center"><?php echo !empty($project->storySum)?array_sum($project->storySum):0;?></td>
        <td align="center"><?php echo isset($project->storySum['projected'])?$project->storySum['projected']:0;?></td>
        <td align="center"><?php echo isset($project->storySum['developing'])?$project->storySum['developing']:0;?></td>
        <td align="center"><?php echo isset($project->storySum['developed'])?$project->storySum['developed']:0;?></td>
        <td align="center"><?php echo isset($project->storySum['testing'])?$project->storySum['testing']:0;?></td>
        <td align="center"><?php echo isset($project->storySum['tested'])?$project->storySum['tested']:0;?></td>
        <td align="center"><?php echo isset($project->storySum['released'])?$project->storySum['released']:0;?></td>
      </tr>
    <?php endforeach;?>
    </tbody>
  </table> 
</div>
<?php include '../../../common/view/footer.html.php';?>
