<?php

class EWRporta_Block_Controller_RecentAttachments extends XFCP_EWRporta_Block_Controller_RecentAttachments
{
	public function actionRecentAttachments()
	{
		$options = $this->getModelFromCache('EWRporta_Model_Options')->getOptionsByBlock('RecentAttachments');
		$options = $this->getModelFromCache('EWRporta_Model_Options')->prepareOptions($options);
		
		$forums = $options['recentattachments_forum']['option_value'];
		
		$start = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$stop = $options['recentattachments_fulllimit']['option_value'];
		$count = $this->getModelFromCache('EWRporta_Block_RecentAttachments')->getAttachmentsCount($forums);
		
		$viewParams = array(
			'linkType' => $options['recentattachments_fulllink']['option_value'],
			'start' => $start,
			'stop' => $stop,
			'count' => $count,
			'attachments' => $this->getModelFromCache('EWRporta_Block_RecentAttachments')->getAttachments($start, $stop, $forums),
		);

		return $this->responseView('EWRporta_Block_ViewPublic_RecentAttachments', 'EWRblock_RecentAttachments_Simple', $viewParams);
	}
}