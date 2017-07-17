<?php
include '../../control.php';
class mytestsuite extends testsuite
{
    /**
     * Link cases to a test suite.
     *
     * @param  int    $suiteID
     * @param  int    $param
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function linkCase($suiteID, $param = 0, $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        if(!empty($_POST))
        {
            $this->testsuite->linkCase($suiteID);
            $this->locate(inlink('view', "suiteID=$suiteID"));
        }

        /* Save session. */
        $this->session->set('caseList', $this->app->getURI(true));

        /* Get suite and product id. */
        $this->view->products = $this->products = $this->loadModel('product')->getPairs('nocode');
        $suite      = $this->testsuite->getById($suiteID);
        $productID = $this->product->saveState($suite->product, $this->products);

        /* Save session. */
        $this->testsuite->setMenu($this->products, $productID);

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        /* Build the search form. */
        $this->loadModel('testcase');
        //新增相关需求
        $this->config->testcase->search['params']['story']['values']= $this->loadModel('story')->getProductStoryPairs($productID);

        $this->config->testcase->search['params']['product']['values']= array('' => '', $productID => $this->products[$productID], 'all' => $this->lang->testcase->allProduct);
        //优化搜索；所属模块默认为空
        $this->config->testcase->search['params']['module']['values'] = array(''=>'') + $this->loadModel('tree')->getOptionMenu($productID, $viewType = 'case');

        $this->config->testcase->search['module']    = 'testsuite';
        $this->config->testcase->search['actionURL'] = inlink('linkCase', "suiteID=$suiteID&param=myQueryID");
        if($this->session->currentProductType == 'normal')
        {
            unset($this->config->testcase->search['fields']['branch']);
            unset($this->config->testcase->search['params']['branch']);
        }
        else
        {
            $this->config->testcase->search['fields']['branch'] = $this->lang->product->branch;
            $branches = array('' => '') + $this->loadModel('branch')->getPairs($suite->product, 'noempty');
            $this->config->testcase->search['params']['branch']['values'] = $branches;
        }
        if(!$this->config->testcase->needReview) unset($this->config->testcase->search['params']['status']['values']['wait']);
        $this->loadModel('search')->setSearchParams($this->config->testcase->search);

        $this->view->title      = $suite->name . $this->lang->colon . $this->lang->testsuite->linkCase;
        $this->view->position[] = html::a($this->createLink('testsuite', 'browse', "productID=$productID"), $this->products[$productID]);
        $this->view->position[] = $this->lang->testsuite->common;
        $this->view->position[] = $this->lang->testsuite->linkCase;

        $this->view->users   = $this->loadModel('user')->getPairs('noletter');
        $this->view->cases   = $this->testsuite->getUnlinkedCases($suite, $param, $pager);
        $this->view->suiteID = $suiteID;
        $this->view->pager   = $pager;
        $this->view->suite   = $suite;

        $this->display();
    }
}