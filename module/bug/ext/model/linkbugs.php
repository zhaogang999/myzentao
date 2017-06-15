<?php
/**
 * Link related bugs.
 *
 * @param  int    $bugID
 * @access public
 * @return void
 */
public function linkBugs($bugID)
{
    if($this->post->bugs == false) return;

    $bug       = $this->getById($bugID);
    $bugs2Link = $this->post->bugs;

    $bugs = implode(',', $bugs2Link) . ',' . trim($bug->linkBug, ',');
    $this->dao->update(TABLE_BUG)->set('linkBug')->eq(trim($bugs, ','))->where('id')->eq($bugID)->exec();
    if(dao::isError()) die(js::error(dao::getError()));
    $this->loadModel('action')->create('bug', $bugID, 'linkRelatedBug', '', implode(',', $bugs2Link));

    //Bug双向关联
    foreach ($bugs2Link as $val)
    {
        $linkbugs = $this->dao->select('linkBug')->FROM(TABLE_BUG)->where('id')->eq(trim($val, ','))->fetch();
        $linkBugsAB = $linkbugs->linkBugs .','. $bugID;

        $this->dao->update(TABLE_BUG)->set('linkBug')->eq(trim($linkBugsAB, ','))->where('id')->eq(trim($val, ','))->exec();
        if(dao::isError()) die(js::error(dao::getError()));

        $this->loadModel('action')->create('bug', $val, 'linkRelatedBug', '', $bugID);
    }
}