<?php
include '../../control.php';
class myBug extends bug
{
    /**
     * Unlink related bug.
     *
     * @param  int    $bugID
     * @param  int    $bug2Unlink
     * @access public
     * @return string
     */
    public function unlinkBug($bugID, $bug2Unlink = 0)
    {
        /* Unlink related bug. */
        $this->bug->unlinkBug($bugID, $bug2Unlink);
        //bug双向关联
        $this->bug->unlinkBug($bug2Unlink, $bugID);

        die('success');
    }
}
