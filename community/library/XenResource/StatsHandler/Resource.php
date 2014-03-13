<?php

class XenResource_StatsHandler_Resource extends XenForo_StatsHandler_Abstract
{
	public function getStatsTypes()
	{
		return array(
			'resource' => new XenForo_Phrase('resources'),
			'resource_update' => new XenForo_Phrase('resource_updates'),
			'resource_like' => new XenForo_Phrase('resource_likes'),
			'resource_rating' => new XenForo_Phrase('resource_ratings')
		);
	}

	public function getData($startDate, $endDate)
	{
		$db = $this->_getDb();

		$resources = $db->fetchPairs(
			$this->_getBasicDataQuery('xf_resource', 'resource_date', 'resource_state = ?'),
			array($startDate, $endDate, 'visible')
		);

		$resourceUpdates = $db->fetchPairs(
			$this->_getBasicDataQuery('xf_resource_update', 'post_date', 'message_state = ?'),
			array($startDate, $endDate, 'visible')
		);
		foreach ($resourceUpdates AS $key => &$value)
		{
			if (isset($resources[$key])) {
				$value = max(0, $value - $resources[$key]);
			}
		}

		$resourceLikes = $db->fetchPairs(
			$this->_getBasicDataQuery('xf_liked_content', 'like_date', 'content_type = ?'),
			array($startDate, $endDate, 'resource_update')
		);

		$resourceRatings = $db->fetchPairs(
			$this->_getBasicDataQuery('xf_resource_rating', 'rating_date'),
			array($startDate, $endDate)
		);

		return array(
			'resource' => $resources,
			'resource_update' => $resourceUpdates,
			'resource_like' => $resourceLikes,
			'resource_rating' => $resourceRatings
		);
	}
}