<?php

class XenResource_ReportHandler_Update extends XenForo_ReportHandler_Abstract
{
	/**
	 * Gets report details from raw array of content (eg, a post record).
	 *
	 * @see XenForo_ReportHandler_Abstract::getReportDetailsFromContent()
	 */
	public function getReportDetailsFromContent(array $content)
	{
		/* @var $updateModel XenResource_Model_Update */
		$updateModel = XenForo_Model::create('XenResource_Model_Update');

		$update = $updateModel->getUpdateById($content['resource_update_id']);
		if (!$update)
		{
			return array(false, false, false);
		}

		if (empty($content['resource']))
		{
			$content['resource'] = XenForo_Model::create('XenResource_Model_Resource')->getResourceById(
				$update['resource_id']
			);
			if (empty($content['resource']))
			{
				return array(false, false, false);
			}
		}
		if (empty($content['category']))
		{
			$content['category'] = XenForo_Model::create('XenResource_Model_Category')->getCategoryById(
				$content['resource']['resource_category_id']
			);
			if (empty($content['category']))
			{
				return array(false, false, false);
			}
		}

		return array(
			$content['resource_update_id'],
			$content['resource']['user_id'],
			array(
				'username' => $content['resource']['username'],
				'update' => $content,
				'resource' => $content['resource'],
				'category' => $content['category']
			)
		);
	}

	/**
	 * Gets the visible reports of this content type for the viewing user.
	 *
	 * @see XenForo_ReportHandler_Abstract:getVisibleReportsForUser()
	 */
	public function getVisibleReportsForUser(array $reports, array $viewingUser)
	{
		/* @var $updateModel XenResource_Model_Update */
		$updateModel = XenForo_Model::create('XenResource_Model_Update');

		foreach ($reports AS $reportId => $report)
		{
			$content = unserialize($report['content_info']);

			if (!isset($content['update'], $content['resource'], $content['category']))
			{
				unset($reports[$reportId]);
			}
			else if (!$updateModel->canManageReportedUpdate(
				$content['update'], $content['resource'], $content['category'], $null, $viewingUser
			))
			{
				unset($reports[$reportId]);
			}
		}

		return $reports;
	}

	/**
	 * Gets the title of the specified content.
	 *
	 * @see XenForo_ReportHandler_Abstract:getContentTitle()
	 */
	public function getContentTitle(array $report, array $contentInfo)
	{
		return new XenForo_Phrase('resource_update_in_x', array('title' => $contentInfo['resource']['title']));
	}

	/**
	 * Gets the link to the specified content.
	 *
	 * @see XenForo_ReportHandler_Abstract::getContentLink()
	 */
	public function getContentLink(array $report, array $contentInfo)
	{
		return XenForo_Link::buildPublicLink('resources/update',
			$contentInfo['resource'],
			array('resource_update_id' => $report['content_id'])
		);
	}

	/**
	 * A callback that is called when viewing the full report.
	 *
	 * @see XenForo_ReportHandler_Abstract::viewCallback()
	 */
	public function viewCallback(XenForo_View $view, array &$report, array &$contentInfo)
	{
		$parser = XenForo_BbCode_Parser::create(
			XenForo_BbCode_Formatter_Base::create('Base', array('view' => $view))
		);

		return $view->createTemplateObject('report_resource_update_content', array(
			'report' => $report,
			'content' => $contentInfo,
			'bbCodeParser' => $parser
		));
	}
}