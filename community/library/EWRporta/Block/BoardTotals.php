<?php

class EWRporta_Block_BoardTotals extends XenForo_Model
{
	public function getModule()
	{
		$boardTotals = $this->getModelFromCache('XenForo_Model_DataRegistry')->get('boardTotals');

		if (!$boardTotals)
		{
			$boardTotals = $this->getModelFromCache('XenForo_Model_Counters')->rebuildBoardTotalsCounter();
		}

		$boardTotals['most_users'] = XenForo_Application::getSimpleCacheData('EWRporta_MostUsers');

		return $boardTotals;
	}
}