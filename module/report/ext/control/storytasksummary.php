<?php
include '../../control.php';
class myReport extends report
{
    /**
     * 项目下需求任务统计.
     *
     * @return void
     */
    public function storyTaskSummary()
    {
        $info = $this->report->storyTaskSummary();
    
        $this->view->info       = $info;
        $this->view->title      = $this->lang->report->storyTaskSummary;
        $this->view->position[] = $this->lang->report->storyTaskSummary;
        $this->view->submenu    = 'project';
        $this->display();
    }
}
