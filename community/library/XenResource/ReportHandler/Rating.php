<?php

class XenResource_ReportHandler_Rating extends XenForo_ReportHandler_Abstract
{
	/**
	 * Gets report details from raw array of content (eg, a post record).
	 *
	 * @see XenForo_ReportHandler_Abstract::getReportDetailsFromContent()
	 */
	public function getReportDetailsFromContent(array $content)
	{
		/* @var $ratingModel XenResource_Model_Rating */
		$ratingModel = XenForo_Model::create('XenResource_Model_Rating');

		$rating = $ratingModel->getRatingById($content['resource_rating_id'], array(
			'join' => XenResource_Model_Rating::FETCH_USER
		));
		if (!$rating)
		{
			return array(false, false, false);
		}

		if (empty($content['resource']))
		{
			$content['resource'] = XenForo_Model::create('XenResource_Model_Resource')->getResourceById(
				$rating['resource_id']
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

		$userId = isset($content['user']['user_id']) ? $content['user']['user_id'] : $content['user_id'];

		if (isset($content['user']['username']))
		{
			$username = $content['user']['username'];
		}
		else if (isset($content['username']))
		{
			$username = $content['username'];
		}
		else
		{
			$username = '';
		}

		return array(
			$content['resource_rating_id'],
			$userId,
			array(
				'username' => $username,
				'rating' => $rating,
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
		/* @var $ratingModel XenResource_Model_Rating */
		$ratingModel = XenForo_Model::create('XenResource_Model_Rating');

		foreach ($reports AS $reportId => $report)
		{
			$content = unserialize($report['content_info']);

			if (!$ratingModel->canManageReportedRating(
				$content['rating'], $content['resource'], $content['category'], $null, $viewingUser
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
		return new XenForo_Phrase('resource_review_in_x', array('title' => $contentInfo['resource']['title']));
	}

	/**
	 * Gets the link to the specified content.
	 *
	 * @see XenForo_ReportHandler_Abstract::getContentLink()
	 */
	public function getContentLink(array $report, array $contentInfo)
	{
		return XenForo_Link::buildPublicLink('resources/reviews',
			$contentInfo['resource'],
			array('resource_rating_id' => $report['content_id'])
		);
	}

	/**
	 * A callback that is called when viewing the full report.
	 *
	 * @see XenForo_ReportHandler_Abstract::viewCallback()
	 */
	public function viewCallback(XenForo_View $view, array &$report, array &$contentInfo)
	{
		return $view->createTemplateObject('report_resource_rating_content', array(
			'report' => $report,
			'content' => $contentInfo
		));
	}
}