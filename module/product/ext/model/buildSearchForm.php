<?php
/**
 * Build search form.
 *
 * @param  int    $productID
 * @param  array  $products
 * @param  int    $queryID
 * @param  int    $actionURL
 * @access public
 * @return void
 */
public function buildSearchForm($productID, $products, $queryID, $actionURL)
{
    $this->config->product->search['actionURL'] = $actionURL;
    $this->config->product->search['queryID']   = $queryID;
    $this->config->product->search['params']['plan']['values']    = $this->loadModel('productplan')->getPairs($productID);
    //1195 跨产品进行需求关联时，默认搜索条件中所属产品为“所有产品”.搜索时默认所属产品为所有产品
    //$this->config->product->search['params']['product']['values'] = array('all' => $this->lang->product->allProduct, $productID => $products[$productID]);
    $this->config->product->search['params']['product']['values'] = array($productID => $products[$productID], 'all' => $this->lang->product->allProduct);
    $this->config->product->search['params']['product']['values'] += $products;

    $this->config->product->search['params']['module']['values']  = $this->loadModel('tree')->getOptionMenu($productID, $viewType = 'story', $startModuleID = 0);
    if($this->session->currentProductType == 'normal')
    {
        unset($this->config->product->search['fields']['branch']);
        unset($this->config->product->search['params']['branch']);
    }
    else
    {
        $this->config->product->search['fields']['branch'] = $this->lang->product->branch;
        $this->config->product->search['params']['branch']['values']  = array('' => '') + $this->loadModel('branch')->getPairs($productID, 'noempty') + array('all' => $this->lang->branch->all);
    }

    $this->loadModel('search')->setSearchParams($this->config->product->search);
}
