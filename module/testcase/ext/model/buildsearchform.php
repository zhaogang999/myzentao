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
    $this->config->testcase->search['params']['product']['values'] = array($productID => $products[$productID], 'all' => $this->lang->testcase->allProduct);
    //优化搜索功能搜索条件增加空选项
    $this->config->testcase->search['params']['module']['values']  = array('' => '') + $this->loadModel('tree')->getOptionMenu($productID, $viewType = 'case');
    //新增相关需求
    $this->config->testcase->search['params']['story']['values']= $this->loadModel('story')->getProductStoryPairs($productID);

    if($this->session->currentProductType == 'normal')
    {
        unset($this->config->testcase->search['fields']['branch']);
        unset($this->config->testcase->search['params']['branch']);
    }
    else
    {
        $this->config->testcase->search['fields']['branch'] = $this->lang->product->branch;
        $this->config->testcase->search['params']['branch']['values'] = array('' => '') + $this->loadModel('branch')->getPairs($productID, 'noempty') + array('all' => $this->lang->branch->all);
    }
    if(!$this->config->testcase->needReview) unset($this->config->testcase->search['params']['status']['values']['wait']);
    $this->config->testcase->search['actionURL'] = $actionURL;
    $this->config->testcase->search['queryID']   = $queryID;

    $this->loadModel('search')->setSearchParams($this->config->testcase->search);
}
