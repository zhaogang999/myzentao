<?php
/**
 * Build bug search form.
 *
 * @param  int    $products
 * @param  int    $queryID
 * @param  int    $actionURL
 * @access public
 * @return void
 */
public function buildBugSearchForm($products, $queryID, $actionURL)
{
    $modules = array();
    $builds  = array('' => '', 'trunk' => $this->lang->trunk);
    foreach($products as $product)
    {
        $productModules = $this->loadModel('tree')->getOptionMenu($product->id);
        $productBuilds  = $this->loadModel('build')->getProductBuildPairs($product->id, 0, $params = 'noempty|notrunk');
        foreach($productModules as $moduleID => $moduleName)
        {
            $modules[$moduleID] = ((count($products) >= 2 and $moduleID) ? $product->name : '') . $moduleName;
        }
        foreach($productBuilds as $buildID => $buildName)
        {
            $builds[$buildID] = ((count($products) >= 2 and $buildID) ? $product->name . '/' : '') . $buildName;
        }
    }

    $branchGroups = $this->loadModel('branch')->getByProducts(array_keys($products), 'noempty');
    $branchPairs  = array();
    $productType  = 'normal';
    $productNum   = count($products);
    $productPairs = array(0 => '');
    foreach($products as $product)
    {
        $productPairs[$product->id] = $product->name;
        if($product->type != 'normal')
        {
            $productType = $product->type;
            if($product->branch)
            {
                $branchPairs[$product->branch] = (count($products) > 1 ? $product->name . '/' : '') . $branchGroups[$product->id][$product->branch];
            }
            else
            {
                $productBranches = isset($branchGroups[$product->id]) ? $branchGroups[$product->id] : array(0);
                if(count($products) > 1)
                {
                    foreach($productBranches as $branchID => $branchName) $productBranches[$branchID] = $product->name . '/' . $branchName;
                }
                $branchPairs += $productBranches;
            }
        }
    }

    $this->config->bug->search['module']    = 'projectBug';
    $this->config->bug->search['actionURL'] = $actionURL;
    $this->config->bug->search['queryID']   = $queryID;
    unset($this->config->bug->search['fields']['project']);
    $this->config->bug->search['params']['product']['values']       = $productPairs + array('all' => $this->lang->product->allProductsOfProject);
    $this->config->bug->search['params']['plan']['values']          = $this->loadModel('productplan')->getForProducts($products);
    //优化搜索功能增加空选项；
    $this->config->bug->search['params']['module']['values']        = array('' => '') + $modules;

    $this->config->bug->search['params']['openedBuild']['values']   = $builds;
    $this->config->bug->search['params']['resolvedBuild']['values'] = $this->config->bug->search['params']['openedBuild']['values'];
    if($productType == 'normal')
    {
        unset($this->config->bug->search['fields']['branch']);
        unset($this->config->bug->search['params']['branch']);
    }
    else
    {
        $this->config->bug->search['fields']['branch']           = sprintf($this->lang->product->branch, $this->lang->product->branchName[$productType]);
        $this->config->bug->search['params']['branch']['values'] = array('' => '') + $branchPairs;
    }
    $this->config->bug->search['params']['status'] = array('operator' => '=', 'control' => 'select', 'values' => $this->lang->bug->statusList);

    $this->loadModel('search')->setSearchParams($this->config->bug->search);
}
