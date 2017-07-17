<?php
/**
 * Build search form.
 *
 * @param  int    $productID
 * @param  array  $products
 * @param  int    $queryID
 * @param  string $actionURL
 * @access public
 * @return void
 */
public function buildSearchForm($productID, $products, $queryID, $actionURL)
{
    $this->config->bug->search['actionURL'] = $actionURL;
    $this->config->bug->search['queryID']   = $queryID;
    //优化搜索功能搜索条件增加空选项
    $this->config->bug->search['params']['product']['values']       = array('' => '', $productID => $products[$productID], 'all' => $this->lang->bug->allProduct);
    
    $this->config->bug->search['params']['plan']['values']          = $this->loadModel('productplan')->getPairs($productID);
    //优化搜索功能；增加空选项
    $this->config->bug->search['params']['module']['values']        = array(0 => '') + $this->loadModel('tree')->getOptionMenu($productID, $viewType = 'bug', $startModuleID = 0);

    $this->config->bug->search['params']['project']['values']       = $this->product->getProjectPairs($productID);
    $this->config->bug->search['params']['severity']['values']      = array(0 => '') + $this->lang->bug->severityList; //Fix bug #939.
    $this->config->bug->search['params']['openedBuild']['values']   = $this->loadModel('build')->getProductBuildPairs($productID, 0, $params = '');
    $this->config->bug->search['params']['resolvedBuild']['values'] = $this->config->bug->search['params']['openedBuild']['values'];
    if($this->session->currentProductType == 'normal')
    {
        unset($this->config->bug->search['fields']['branch']);
        unset($this->config->bug->search['params']['branch']);
    }
    else
    {
        $this->config->bug->search['fields']['branch'] = $this->lang->product->branch;
        $this->config->bug->search['params']['branch']['values']  = array('' => '') + $this->loadModel('branch')->getPairs($productID, 'noempty') + array('all' => $this->lang->branch->all);
    }

    $this->loadModel('search')->setSearchParams($this->config->bug->search);
}
