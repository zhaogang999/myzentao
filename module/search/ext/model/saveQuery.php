<?php
/**
 * Save current query to db.bug6830 搜索条件保存后无法在菜单栏显示;待官方修复后本文件删除
 *
 * @access public
 * @return void
 */
public function saveQuery()
{
    global  $config;
    $sqlVar  = $this->post->module  . 'Query';
    $formVar = $this->post->module  . 'Form';
    $sql     = $this->session->$sqlVar;
    if(!$sql) $sql = ' 1 = 1 ';

    $query = fixer::input('post')
        ->add('account', $this->app->user->account)
        ->add('form', serialize($this->session->$formVar))
        ->add('sql',  $sql)
        ->skipSpecial('sql,form')
        ->remove('onMenuBar')
        ->get();
    $this->dao->insert(TABLE_USERQUERY)->data($query)->autoCheck()->check('title', 'notempty')->exec();

    if(!dao::isError())
    {
        $queryID = $this->dao->lastInsertID();
        /* Set this query show on menu bar. */
        if($this->post->onMenuBar)
        {
            $queryModule      = $query->module == 'task' ? 'project' : ($query->module == 'story' ? 'product' : $query->module);
            $featureBarConfig = $this->dao->select('*')->from(TABLE_CONFIG)->where('owner')->eq($this->app->user->account)->andWhere('module')->eq('common')->andWhere('section')->eq('customMenu')->andWhere('`key`')->like($config->global->flow . "_feature_{$queryModule}_%")->fetch();

            $this->app->loadLang($queryModule);
            if(!isset($this->lang->$queryModule->featureBar)) return $queryID;

            $method    = key($this->lang->$queryModule->featureBar);
            $newConfig = array();
            if(isset($featureBarConfig->id)) $newConfig = json_decode($featureBarConfig->value);
            if(empty($newConfig))
            {
                $order = 1;
                foreach($this->lang->$queryModule->featureBar[$method] as $menuKey => $menuName)
                {
                    $menu = new stdclass();
                    $menu->name  = $menuKey;
                    $menu->order = $order;
                    $newConfig[] = $menu;
                    $order++;
                }
            }

            $menu = new stdclass();
            $menu->name  = 'QUERY' . $queryID;
            $menu->order = $order;
            $newConfig[] = $menu;

            $featureBarConfig = new stdclass();
            $featureBarConfig->owner   = $this->app->user->account;
            $featureBarConfig->module  = 'common';
            $featureBarConfig->section = 'customMenu';
            $featureBarConfig->key     = $config->global->flow . "_feature_{$queryModule}_{$method}";
            $featureBarConfig->value   = json_encode($newConfig);
            $this->dao->replace(TABLE_CONFIG)->data($featureBarConfig)->exec();
        }
        return $queryID;
    }
    return false;
}
