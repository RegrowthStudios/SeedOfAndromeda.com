<?php

class XenResource_ModeratorLogHandler_Rating extends XenForo_ModeratorLogHandler_Abstract
{
	protected function _log(array $logUser, array $content, $action, array $actionParams = array(), $parentContent = null)
	{
		if (!$parentContent)
		{
			$parentContent = XenForo_Model::create('XenResource_Model_Resource')->getResourceById($content['resource_id']);
		}
		if (!$parentContent)
		{
			return false;
		}

		$dw = XenForo_DataWriter::create('XenForo_DataWriter_ModeratorLog');
		$dw->bulkSet(array(
			'user_id' => $logUser['user_id'],
			'content_type' => 'resource_rating',
			'content_id' => $content['resource_rating_id'],
			'content_user_id' => $parentContent['user_id'],
			'content_username' => $parentContent['username'],
			'content_title' => $parentContent['title'] . ' ' . $content['version_string'],
			'content_url' => XenForo_Link::buildPublicLink('resources', $parentContent),
			'discussion_content_type' => 'resource',
			'discussion_content_id' => $content['resource_id'],
			'action' => $action,
			'action_params' => $actionParams
		));
		$dw->save();

		return $dw->get('moderator_log_id');
	}
}