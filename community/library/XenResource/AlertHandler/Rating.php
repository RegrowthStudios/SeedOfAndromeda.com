<?php

class XenResource_AlertHandler_Rating extends XenForo_AlertHandler_Abstract
{
	protected $_ratingModel;

	/**
	 * Fetches the content required by alerts.
	 *
	 * @param array $contentIds
	 * @param XenForo_Model_Alert $model Alert model invoking this
	 * @param integer $userId User ID the alerts are for
	 * @param array $viewingUser Information about the viewing user (keys: user_id, permission_combination_id, permissions)
	 *
	 * @return array
	 */
	public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
	{
		$ratingModel = $this->_getRatingModel();

		$ratings = $ratingModel->getRatingsByIds($contentIds, array(
			'join' => XenResource_Model_Rating::FETCH_USER
		));
		$resourceIds = array();
		foreach ($ratings AS $rating)
		{
			$resourceIds[$rating['resource_id']] = $rating['resource_id'];
		}
		$resources = XenForo_Model::create('XenResource_Model_Resource')->getResourcesByIds($resourceIds, array(
			'permissionCombinationId' => $viewingUser['permission_combination_id']
		));

		foreach ($ratings AS $key => &$rating)
		{
			if (!isset($resources[$rating['resource_id']]))
			{
				unset($ratings[$key]);
			}
			else
			{
				$rating['resource'] = $resources[$rating['resource_id']];
				$rating['resource']['title'] = XenForo_Helper_String::censorString($rating['resource']['title']);
			}
		}

		return $ratings;
	}

	/**
	* Determines if the rating is viewable.
	* @see XenForo_AlertHandler_Abstract::canViewAlert()
	*/
	public function canViewAlert(array $alert, $content, array $viewingUser)
	{
		$ratingModel = $this->_getRatingModel();

		$categoryPermissions = XenForo_Permission::unserializePermissions($content['resource']['category_permission_cache']);

		return $ratingModel->canViewRatingAndContainer(
			$content, $content['resource'], $content['resource'], $null, $viewingUser, $categoryPermissions
		);
	}

	protected function _prepareAlertBeforeAction(array $item, $content, array $viewingUser)
	{
		$item['userOriginal'] = $item['user'];

		if ($content['is_anonymous'])
		{
			$item['user']['user_id'] = 0;
			$item['user']['username'] = new XenForo_Phrase('rating_anonymous');
		}

		return $item;
	}

	/**
	 * @return XenResource_Model_Rating
	 */
	protected function _getRatingModel()
	{
		if (!$this->_ratingModel)
		{
			$this->_ratingModel = XenForo_Model::create('XenResource_Model_Rating');
		}

		return $this->_ratingModel;
	}
}