<?php
include '../../control.php';
class myReport extends report
{
    /**
     * Bug整体情况.
     *
     * @return void
     */
    public function bugSummary()
    {
        $info = $this->report->bugSummary();

        $this->view->info       = $info;
        $this->view->title      = $this->lang->report->bugSummary;
        $this->view->position[] = $this->lang->report->bugSummary;
        $this->view->submenu    = 'test';
        $this->display();
    }
}
