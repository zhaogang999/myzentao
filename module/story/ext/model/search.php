<?php
/**
 * Get stories through search.
 *
 * @access public
 * @param  int    $productID
 * @param  int    $queryID
 * @param  string $orderBy
 * @param  object $pager
 * @param  string $projectID
 * @access public
 * @return array
 */
public function getBySearch($productID, $queryID, $orderBy, $pager = null, $projectID = '', $branch = 0)
{
    if($projectID != '')
    {
        $products = $this->loadModel('project')->getProducts($projectID);
    }
    else
    {
        $products = $this->loadModel('product')->getPairs();
    }
    $query = $queryID ? $this->loadModel('search')->getQuery($queryID) : '';

    /* Get the sql and form status from the query. */
    if($query)
    {
        $this->session->set('storyQuery', $query->sql);
        $this->session->set('storyForm', $query->form);
    }
    if($this->session->storyQuery == false) $this->session->set('storyQuery', ' 1 = 1');

    $allProduct     = "`product` = 'all'";
    $storyQuery     = $this->session->storyQuery;
    $queryProductID = $productID;
    if(strpos($storyQuery, $allProduct) !== false)
    {
        $storyQuery     = str_replace($allProduct, '1', $storyQuery);
        $queryProductID = 'all';
    }
    $storyQuery = $storyQuery . ' AND `product` ' . helper::dbIN(array_keys($products));
    if($projectID != '')
    {
        foreach($products as $product) $branches[$product->branch] = $product->branch;
        unset($branches[0]);
        $branches = join(',', $branches);
        if($branches) $storyQuery .= " AND `branch`" . helper::dbIN("0,$branches");
        if($this->app->moduleName == 'release' or $this->app->moduleName == 'build')
        {
            $storyQuery .= " AND `status` NOT IN ('draft')";// Fix bug #990.
        }
        else
        {
            $storyQuery .= " AND `status` NOT IN ('draft', 'closed')";
        }
    }
    elseif($branch)
    {
        $allBranch = "`branch` = 'all'";
        //跨产品关联需求时无法搜到预期结果
        //if($branch and strpos($storyQuery, '`branch` =') === false) $storyQuery .= " AND `branch` in('0','$branch')";

        if(strpos($storyQuery, $allBranch) !== false) $storyQuery = str_replace($allBranch, '1', $storyQuery);
    }
    $storyQuery = preg_replace("/`plan` +LIKE +'%([0-9]+)%'/i", "CONCAT(',', `plan`, ',') LIKE '%,$1,%'", $storyQuery);

    return $this->getBySQL($queryProductID, $storyQuery, $orderBy, $pager);
}

/**
 * Get stories by a sql.
 *
 * @param  int    $productID
 * @param  string $sql
 * @param  string $orderBy
 * @param  object $pager
 * @access public
 * @return array
 */
public function getBySQL($productID, $sql, $orderBy, $pager = null)
{
    /* Get plans. */
    $plans = $this->dao->select('id,title')->from(TABLE_PRODUCTPLAN)
        ->where('deleted')->eq('0')
        ->beginIF($productID != 'all' and $productID != '')->andWhere('product')->eq((int)$productID)->fi()
        ->fetchPairs();

    $tmpStories = $this->dao->select('*')->from(TABLE_STORY)->where($sql)
        //跨产品需求搜索无法搜到结果；
        ->beginIF($productID != 'all' and strpos($sql, "`product` =") === false)->andWhere('product')->eq((int)$productID)->fi()
        // ->beginIF($productID != 'all' and $productID != '')->andWhere('product')->eq((int)$productID)->fi()

        ->andWhere('deleted')->eq(0)
        ->orderBy($orderBy)
        ->page($pager)
        ->fetchAll('id');

    if(!$tmpStories) return array();

    /* Process plans. */
    $stories = array();
    foreach($tmpStories as $story)
    {
        $story->planTitle = '';
        $storyPlans = explode(',', trim($story->plan, ','));
        foreach($storyPlans as $planID) $story->planTitle .= zget($plans, $planID, '') . ' ';
        $stories[] = $story;
    }
    return $stories;
}
