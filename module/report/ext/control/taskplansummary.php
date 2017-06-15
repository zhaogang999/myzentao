<?php
include '../../control.php';
class myReport extends report
{
    /**
     * 任务计划调整统计
     *
     * @return void
     */
    public function taskPlanSummary()
    {
        $info = $this->report->taskPlanSummary();
        
        $this->view->info       = $info;
        $this->view->title      = $this->lang->report->taskPlanSummary;
        $this->view->position[] = $this->lang->report->taskPlanSummary;
        $this->view->submenu    = 'project';
        $this->display();
    }
}
