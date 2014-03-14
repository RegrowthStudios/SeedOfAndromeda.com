<?php

class XenResource_ControllerAdmin_CategoryPermission extends XenForo_ControllerAdmin_Permission_Abstract
{
	protected function _preDispatch($action)
	{
		parent::_preDispatch($action);
		$this->assertAdminPermission('resourceManager');
	}

	/**
	 * Deprecated. Now redirects to the main category display.
	 *
	 * @return XenForo_ControllerResponse_Redirect
	 */
	public function actionIndex()
	{
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
			XenForo_Link::buildAdminLink('resource-categories')
		);
	}

	/**
	 * For a single category, shows page with options to edit  permissions.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionCategoryOptions()
	{
		$category = $this->_getCategoryOrError();
		$categoryId = $category['resource_category_id'];

		$permissionSets = $this->_getPermissionModel()->getUserCombinationsWithContentPermissions('resource_category');
		$groupsWithPerms = array();
		foreach ($permissionSets AS $set)
		{
			if ($set['user_group_id'] && $set['content_id'] == $categoryId)
			{
				$groupsWithPerms[$set['user_group_id']] = true;
			}
		}

		$viewParams = array(
			'category' => $category,
			'userGroups' => $this->_getUserGroupModel()->getAllUserGroups(),
			'groupsWithPerms' => $groupsWithPerms,
			'users' => $this->_getPermissionModel()->getUsersWithContentUserPermissions('resource_category', $categoryId),
			'revoked' => $this->_permissionsAreRevoked($categoryId, 0, 0),
		);

		return $this->responseView('XenResource_ViewAdmin_CategoryPermission_List', 'permission_resource_category', $viewParams);
	}

	/**
	 * Changes the revoke status for the category-wide settings.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionCategoryWideRevoke()
	{
		$this->_assertPostOnly();

		$category = $this->_getCategoryOrError();
		$revoke = $this->_input->filterSingle('revoke', XenForo_Input::UINT);

		$this->_setPermissionRevokeStatus($category['resource_category_id'], 0, 0, $revoke);

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('resource-category-perms', $category)
		);
	}

	/**
	 * Displays a form to edit a user group's permissions for a category.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionUserGroup()
	{
		$category = $this->_getCategoryOrError();
		$categoryId = $category['resource_category_id'];

		$userGroupId = $this->_input->filterSingle('user_group_id', XenForo_Input::UINT);
		$userGroup = $this->_getValidUserGroupOrError($userGroupId);

		$permissionModel = $this->_getPermissionModel();

		$permissions = $permissionModel->getUserCollectionContentPermissionsForGroupedInterface(
			'resource_category', $categoryId, 'resource', $userGroup['user_group_id'], 0
		);

		$viewParams = array(
			'category' => $category,
			'userGroup' => $userGroup,
			'permissions' => $permissions,
			'permissionChoices' => $permissionModel->getPermissionChoices('userGroup', true)
		);

		return $this->responseView('XenResource_ViewAdmin_CategoryPermission_UserGroup', 'permission_resource_category_user_group', $viewParams);
	}

	/**
	 * Updates a user group's permissions for a category.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionUserGroupSave()
	{
		$this->_assertPostOnly();

		$category = $this->_getCategoryOrError();
		$categoryId = $category['resource_category_id'];

		$userGroupId = $this->_input->filterSingle('user_group_id', XenForo_Input::UINT);
		$userGroup = $this->_getValidUserGroupOrError($userGroupId);

		$permissions = $this->_input->filterSingle('permissions', XenForo_Input::ARRAY_SIMPLE);

		$this->_getPermissionModel()->updateContentPermissionsForUserCollection(
			$permissions, 'resource_category', $categoryId, $userGroup['user_group_id'], 0
		);

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('resource-category-perms', $category) . $this->getLastHash("user_group_{$userGroupId}")
		);
	}

	/**
	 * Redirects to the correct page to add permissions for the specified user.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionUserAdd()
	{
		$category = $this->_getCategoryOrError();

		$userName = $this->_input->filterSingle('username', XenForo_Input::STRING);
		$user = $this->_getUserModel()->getUserByName($userName);
		if (!$user)
		{
			return $this->responseError(new XenForo_Phrase('requested_user_not_found'), 404);
		}

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
			XenForo_Link::buildAdminLink('resource-category-perms/user', $category, array('user_id' => $user['user_id']))
		);
	}

	/**
	 * Displays a form to edit a user's permissions for a category.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionUser()
	{
		$category = $this->_getCategoryOrError();
		$categoryId = $category['resource_category_id'];

		$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
		$user = $this->_getValidUserOrError($userId);

		$permissionModel = $this->_getPermissionModel();

		$permissions = $permissionModel->getUserCollectionContentPermissionsForGroupedInterface(
			'resource_category', $categoryId, 'resource', 0, $user['user_id']
		);

		$viewParams = array(
			'category' => $category,
			'user' => $user,
			'permissions' => $permissions,
			'permissionChoices' => $permissionModel->getPermissionChoices('user', true)
		);

		return $this->responseView('XenResource_ViewAdmin_CategoryPermission_User', 'permission_resource_category_user', $viewParams);
	}

	/**
	 * Updates a user's permissions for a category.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionUserSave()
	{
		$this->_assertPostOnly();

		$category = $this->_getCategoryOrError();
		$categoryId = $category['resource_category_id'];

		$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
		$user = $this->_getValidUserOrError($userId);

		$permissions = $this->_input->filterSingle('permissions', XenForo_Input::ARRAY_SIMPLE);

		$this->_getPermissionModel()->updateContentPermissionsForUserCollection(
			$permissions, 'resource_category', $categoryId, 0, $user['user_id']
		);

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('resource-category-perms', $category) . $this->getLastHash("user_{$userId}")
		);
	}

	protected function _permissionsAreRevoked($categoryId, $userGroupId, $userId)
	{
		$permissions = $this->_getPermissionModel()->getContentPermissionsWithValues(
			'resource_category', $categoryId, 'resource', $userGroupId, $userId
		);

		foreach ($permissions AS $permission)
		{
			if ($permission['permission_group_id'] == 'resource'
				&& $permission['permission_id'] == 'view'
				&& $permission['permission_value'] === 'reset'
			)
			{
				return true;
			}
		}

		return false;
	}

	protected function _setPermissionRevokeStatus($categoryId, $userGroupId, $userId, $revoke)
	{
		$update = array('resource' => array('view' => $revoke ? 'reset' : 'unset'));

		$this->_getPermissionModel()->updateContentPermissionsForUserCollection(
			$update, 'resource_category', $categoryId, $userGroupId, $userId
		);
	}

	/**
	 * Gets the specified record or errors.
	 *
	 * @param integer|null $id
	 *
	 * @return array
	 */
	protected function _getCategoryOrError($id = null)
	{
		if ($id === null)
		{
			$id = $this->_input->filterSingle('resource_category_id', XenForo_Input::UINT);
		}

		$info = $this->_getCategoryModel()->getCategoryById($id);
		if (!$info)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('requested_category_not_found'), 404));
		}

		return $info;
	}

	/**
	 * @return XenResource_Model_Category
	 */
	protected function _getCategoryModel()
	{
		return $this->getModelFromCache('XenResource_Model_Category');
	}
}