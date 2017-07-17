<?php
/**
 * Build story search form.
 *
 * @param  array  $products
 * @param  array  $branchGroups
 * @param  array  $modules
 * @param  int    $queryID
 * @param  string $actionURL
 * @param  string $type
 * @access public
 * @return void
 */
public function buildStorySearchForm($products, $branchGroups, $modules, $queryID, $actionURL, $type = 'projectStory')
{
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

    /* Build search form. */
    if($type == 'projectStory')
    {
        $this->config->product->search['module'] = 'projectStory';
        unset($this->config->product->search['fields']['stage']);
        unset($this->config->product->search['params']['stage']);
    }
    $this->config->product->search['actionURL'] = $actionURL;
    $this->config->product->search['queryID']   = $queryID;
    $this->config->product->search['params']['product']['values'] = $productPairs + array('all' => $this->lang->product->allProductsOfProject);
    $this->config->product->search['params']['plan']['values'] = $this->loadModel('productplan')->getForProducts($products);
    //优化搜索功能增加空选项；
    $this->config->product->search['params']['module']['values'] = array(''=>'') + $modules;

    unset($this->lang->story->statusList['draft']);
    if($productType == 'normal')
    {
        unset($this->config->product->search['fields']['branch']);
        unset($this->config->product->search['params']['branch']);
    }
    else
    {
        $this->config->product->search['fields']['branch'] = sprintf($this->lang->product->branch, $this->lang->product->branchName[$productType]);
        $this->config->product->search['params']['branch']['values'] = array('' => '') + $branchPairs;
    }
    $this->config->product->search['params']['status'] = array('operator' => '=', 'control' => 'select', 'values' => $this->lang->story->statusList);

    $this->loadModel('search')->setSearchParams($this->config->product->search);
}
