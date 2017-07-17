<?php
/**
 * Get report data of deadline bugs per day.
 *
 * @access public
 * @return array
 */
public function getDataOfbugsDeadline()
{
    return $this->dao->select('deadline AS name, COUNT(*) AS value')->from(TABLE_BUG)
        ->where($this->reportCondition())->groupBy('name')
        ->having('name != 0000-00-00')
        ->orderBy('deadline')->fetchAll();
}