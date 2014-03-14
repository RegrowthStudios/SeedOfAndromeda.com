<?php

class EWRporta_Block_Controller_RecentThreadx5 extends XFCP_EWRporta_Block_Controller_RecentThreadx5
{
	public function actionRecentThreads()
	{
		$options = $this->getModelFromCache('EWRporta_Model_Options')->getOptionsByBlock('RecentThreadx5');
		$options = $this->getModelFromCache('EWRporta_Model_Options')->prepareOptions($options);
		
		$tab = $this->_input->filterSingle('tab', XenForo_Input::UINT);
		$pos = $this->_input->filterSingle('pos', XenForo_Input::STRING);
		
		switch ($tab)
		{
			case 5:
				$forum = $options['recentthreadx5_forum5']['option_value'];
				break;
			case 4:
				$forum = $options['recentthreadx5_forum4']['option_value'];
				break;
			case 3:
				$forum = $options['recentthreadx5_forum3']['option_value'];
				break;
			case 2:
				$forum = $options['recentthreadx5_forum2']['option_value'];
				break;
			default:
				$forum = $options['recentthreadx5_forum1']['option_value'];
				break;
		}
		
		$threads = $this->getModelFromCache('EWRporta_Block_RecentThreadx5')->getTab(
			$options['recentthreadx5_cutoff']['option_value'],
			$options['recentthreadx5_limit']['option_value'],
			$forum
		);
		
		$viewParams = array(
			'threads' => $threads,
			'position' => $pos,
		);

		return $this->responseView('EWRporta_Block_ViewPublic_RecentThreadx5', 'EWRblock_RecentThreadx5_Simple', $viewParams);
	}
}