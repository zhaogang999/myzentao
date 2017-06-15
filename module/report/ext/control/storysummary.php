<?php
include '../../control.php';
class myReport extends report
{
    /**
     * 项目需求统计.
     *
     * @return void
     */
    public function storySummary()
    {
        $info = $this->report->storySummary();

        $this->view->info       = $info;
        $this->view->title      = $this->lang->report->storySummary;
        $this->view->position[] = $this->lang->report->storySummary;
        $this->view->submenu    = 'project';
        $this->display();
    }
}
