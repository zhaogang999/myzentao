<?php include '../../../common/view/header.html.php';?>
<?php include '../../../common/view/datepicker.html.php';?>
<?php include '../../../common/ext/view/affix.html.php';?>
<form target='hiddenwin' method='post'>
<table class='table table-fixed active-disabled table-custom'>
<thead class='text-center'>
  <tr>
    <th class='w-60px'><?php echo $lang->task->id?></th>
    <th class='w-150px'><?php echo $lang->task->name?></th>
    <th class='w-150px'><?php echo $lang->task->module?></th>
    <th class='w-150px'><?php echo $lang->task->story?></th>
    <th class='w-70px'><?php echo $lang->task->pri?></th>
    <th class='w-90px'><?php echo $lang->task->type?></th>
    <th class='w-80px'><?php echo $lang->task->estimate?></th>
    <th class='w-120px'><?php echo $lang->task->estStarted?></th>
    <th class='w-120px'><?php echo $lang->task->deadline?></th>
    <th><?php echo $lang->task->desc?></th>
  </tr>
</thead>
<tbody>
  <?php $insert = true;?>
  <?php foreach($taskData as $key => $task):?>
  <tr class='text-top'>
    <td>
      <?php
      if(!empty($task->id))
      {
          echo $task->id . html::hidden("id[$key]", $task->id);
          $insert = false;
      }
      else
      {
          echo $key . " <sub style='vertical-align:sub;color:gray'>{$lang->task->new}</sub>";
      }
      echo html::hidden("project[$key]", $projectID);
      ?>
    </td>
    <td><?php echo html::input("name[$key]", htmlspecialchars($task->name, ENT_QUOTES), "class='form-control'")?></td>
    <td style='overflow:visible'><?php echo html::select("module[$key]", $modules, !empty($task->module) ? $task->module : ((!empty($task->id) and isset($tasks[$task->id])) ? $tasks[$task->id]->module : ''), "class='form-control chosen'")?></td>
    <td style='overflow:visible'><?php echo html::select("story[$key]", $stories, !empty($task->story) ? $task->story : ((!empty($task->id) and isset($tasks[$task->id])) ? $tasks[$task->id]->story : ''), "class='form-control chosen'")?></td>
    <td><?php echo html::select("pri[$key]", $lang->task->priList, !empty($task->pri) ? $task->pri : ((!empty($task->id) and isset($tasks[$task->id])) ? $tasks[$task->id]->pri : ''), "class='form-control'")?></td>
    <td><?php echo html::select("type[$key]", $lang->task->typeList, !empty($task->type) ? $task->type : ((!empty($task->id) and isset($tasks[$task->id])) ? $tasks[$task->id]->type : ''), "class='form-control'")?></td>
    <td><?php echo html::input("estimate[$key]", !empty($task->estimate) ? $task->estimate : ((!empty($task->id) and isset($tasks[$task->id])) ? $tasks[$task->id]->estimate : ''), "class='form-control' autocomplete='off'")?></td>
    <td><?php echo html::input("estStarted[$key]", !empty($task->estStarted) ? $task->estStarted : ((!empty($task->id) and isset($tasks[$task->id])) ? $tasks[$task->id]->estStarted : ''), "class='form-control date'")?></td>
    <td><?php echo html::input("deadline[$key]", !empty($task->deadline) ? $task->deadline : ((!empty($task->id) and isset($tasks[$task->id])) ? $tasks[$task->id]->deadline : ''), "class='form-control date'")?></td>
    <td><?php echo html::textarea("desc[$key]", $task->desc, "class='form-control' cols='50' rows='1'")?></td>
    <!--新增隐藏域-->
    <?php echo html::hidden("assignedTo[$key]", !empty($task->assignedTo) ? $task->assignedTo : ((!empty($task->id) and isset($tasks[$task->id])) ? $tasks[$task->id]->assignedTo : ''), "class='form-control date'")?>
  </tr>
  <?php endforeach;?>
</tbody>
<tfoot>
  <tr>
    <td colspan='10' class='text-center'>
      <?php
      if(!$insert)
      {   
        include '../../../common/view/noticeimport.html.php';
        echo "<button type='button' data-toggle='myModal' class='btn btn-primary'>{$lang->save}</button>";
      }   
      else
      {   
          echo html::submitButton();
      }   
      echo ' &nbsp; ' . html::backButton()
      ?>  
    </td>
  </tr>
</tfoot>
</table>
</form>
<script>
$(function(){affix('thead')})
</script>
<?php include '../../../common/view/footer.html.php';?>
