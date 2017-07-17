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
            <th class="w-id"><?php echo $lang->report->adjustTaskCount;?></th>
            <th class="w-id"><?php echo $lang->report->delayTaskCount;?></th>
            <th class="w-id"><?php echo $lang->report->planTaskCount;?></th>
            <th width="475"><?php echo $lang->report->delayTaskIDs;?></th>
            <th width="475"><?php echo $lang->report->planTaskIDs;?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($info as $id  =>$project):?>
      <tr class="a-center">
        <td align="center"><?php echo $id;?></td>
        <td><?php echo $project['name'];?></td>
        <td align="center"><?php echo $project['taskCount'];?></td>
        <td align="center"><?php echo isset($project['delayTaskCount'])?$project['delayTaskCount']:0;?></td>
          <td align="center"><?php echo isset($project['planTaskCount'])?$project['planTaskCount']:0;?></td>
          <td><?php echo isset($project['delayTaskIDs'])?$project['delayTaskIDs']:0;?></td>
          <td><?php echo isset($project['planTaskIDs'])?$project['planTaskIDs']:0;?></td>
      </tr>
    <?php endforeach;?>
    </tbody>
  </table> 
</div>
<?php include '../../../common/view/footer.html.php';?>
