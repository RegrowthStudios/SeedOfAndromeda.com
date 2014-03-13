<?php

/**
 * Controller for post-related actions.
 *
 * @package XenForo_Post
 */
class XenPlaza_XPThreadEditingReason_ControllerPublic_Post extends XFCP_XenPlaza_XPThreadEditingReason_ControllerPublic_Post
{
	public function actionSaveInline()
	{
		$this->_assertPostOnly();
		if ($this->_input->inRequest('more_options'))
		{
			return $this->responseReroute(__CLASS__, 'edit');
		}
		
		$result = parent::actionSaveInline();
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);
		$input = $this->_input->filter(array(
			'XP_edit_reason' => XenForo_Input::STRING,
		));
		$input['XP_edit_date'] = XenForo_Application::$time;
		$visitor = XenForo_Visitor::getInstance();
		$input['XP_editor'] = $visitor['user_id'];
		$dw = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post');
		$dw->setExistingData($postId);
		$dw->bulkSet($input);
		$dw->save();
		
		
		return $result;
	}

	/**
	 * Updates an existing post.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionSave()
	{
		
		$result = parent::actionSave();		
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);
		$input = $this->_input->filter(array(
			'XP_edit_reason' => XenForo_Input::STRING,
		));
		$input['XP_edit_date'] = XenForo_Application::$time;
		$visitor = XenForo_Visitor::getInstance();
		$input['XP_editor'] = $visitor['user_id'];
		$dw = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post');
		$dw->setExistingData($postId);
		$dw->bulkSet($input);
		$dw->save();
		return $result;
	}
}