<?php
include '../../control.php';
class myReport extends report
{
    /**
     * 项目任务统计
     *
     * @return void
     */
    public function taskSummary()
    {
        $info = $this->report->taskSummary();
    
        $this->view->info       = $info;
        $this->view->title      = $this->lang->report->taskSummary;
        $this->view->position[] = $this->lang->report->taskSummary;
        $this->view->submenu    = 'project';
        $this->display();
    }
}
