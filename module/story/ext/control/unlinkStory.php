<?php
include '../../control.php';
class myStory extends story
{
    /**
     * Unlink story.
     *
     * @param  int    $storyID
     * @param  string $type
     * @param  int    $story2Unlink
     * @access public
     * @return string
     */
    public function unlinkStory($storyID, $type = '', $story2Unlink = 0)
    {
        /* Unlink related story if type is linkStories else unlink child story. */
        $this->story->unlinkStory($storyID, $type, $story2Unlink);
        //需求双向关联
        $this->story->unlinkStory($story2Unlink, $type, $storyID);

        die('success');
    }
}