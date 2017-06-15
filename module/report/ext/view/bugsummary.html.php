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
          <th width="36" rowspan="2"><?php echo $lang->report->productID;?></th>
          <th class='w-200px' rowspan="2"><?php echo $lang->report->product;?></th>
          <th class='w-id' rowspan="2"><?php echo $lang->report->bugSum;?></th>
          <th colspan='4'><?php echo $lang->report->severity;?></th>
          <th colspan='2'><?php echo $lang->report->bugStatus;?></th>
          <th colspan='10'><?php echo $lang->report->bugResolution;?></th>
        </tr>
        <tr class='colhead'>
          <th class="w-id"><?php echo $lang->report->bugSeverity['1'];?></th>
          <th class="w-id"><?php echo $lang->report->bugSeverity['2'];?></th>
          <th class="w-id"><?php echo $lang->report->bugSeverity['3'];?></th>
          <th class="w-id"><?php echo $lang->report->bugSeverity['4'];?></th>
          <th class="w-id"><?php echo $lang->report->bugOpen;?></th>
          <th class="w-id"><?php echo $lang->report->bugClosed;?></th>
          <th class="w-id"><?php echo $lang->report->bydesign;?></th>
          <th class="w-id"><?php echo $lang->report->duplicate;?></th>
          <th class="w-id"><?php echo $lang->report->external;?></th>
          <th class="w-id"><?php echo $lang->report->fixed;?></th>
          <th class="w-id"><?php echo $lang->report->notrepro;?></th>
          <th class="w-id"><?php echo $lang->report->postponed;?></th>
          <th class="w-id"><?php echo $lang->report->willnotfix;?></th>
          <th class="w-id"><?php echo $lang->report->tostory;?></th>
          <th class="w-id"><?php echo $lang->report->improve;?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($info as $id  =>$product):?>
      <tr class="a-center">
        <td align="center"><?php echo $id;?></td>
        <td><?php echo $product->productInfo->name;?></td>
        <td align="center"><?php echo $product->bugSum;?></td>
        <td align="center"><?php echo isset($product->bugSeveritySum['1'])?$product->bugSeveritySum['1']:0;?></td>
        <td align="center"><?php echo isset($product->bugSeveritySum['2'])?$product->bugSeveritySum['2']:0;?></td>
        <td align="center"><?php echo isset($product->bugSeveritySum['3'])?$product->bugSeveritySum['3']:0;?></td>
        <td align="center"><?php echo isset($product->bugSeveritySum['4'])?$product->bugSeveritySum['4']:0;?></td>
        <td align="center"><?php echo isset($product->bugStatusSum['active'])?$product->bugStatusSum['active']:0;?></td>
        <td align="center"><?php echo isset($product->bugStatusSum['resolved'])?$product->bugStatusSum['resolved']:0;?></td>
        <td align="center"><?php echo isset($product->bugResolutionSum['bydesign'])?$product->bugResolutionSum['bydesign']:0;?></td>
        <td align="center"><?php echo isset($product->bugResolutionSum['duplicate'])?$product->bugResolutionSum['duplicate']:0;?></td>
        <td align="center"><?php echo isset($product->bugResolutionSum['external'])?$product->bugResolutionSum['external']:0;?></td>
        <td align="center"><?php echo isset($product->bugResolutionSum['fixed'])?$product->bugResolutionSum['fixed']:0;?></td>
        <td align="center"><?php echo isset($product->bugResolutionSum['notrepro'])?$product->bugResolutionSum['notrepro']:0;?></td>
        <td align="center"><?php echo isset($product->bugResolutionSum['postponed'])?$product->bugResolutionSum['postponed']:0;?></td>
        <td align="center"><?php echo isset($product->bugResolutionSum['willnotfix'])?$product->bugResolutionSum['willnotfix']:0;?></td>
        <td align="center"><?php echo isset($product->bugToStorySum)?$product->bugToStorySum:0;?></td>
        <td align="center"><?php echo isset($product->bugResolutionSum['improve'])?$product->bugResolutionSum['improve']:0;?></td>
      </tr>
    <?php endforeach;?>
    </tbody>
  </table> 
</div>
<?php include '../../../common/view/footer.html.php';?>
