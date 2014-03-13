<?php

class XenResource_ControllerAdmin_Prefix extends XenForo_ControllerAdmin_Abstract
{
	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('resourceManager');
	}

	public function actionIndex()
	{
		$prefixModel = $this->_getPrefixModel();

		$prefixGroups = $prefixModel->getAllPrefixGroups();
		$prefixes = $prefixModel->getPrefixesByGroups(array(), array(), $prefixCount);

		$prefixGroups = $prefixModel->mergePrefixesIntoGroups($prefixes, $prefixGroups);

		$viewParams = array(
			'prefixGroups' => $prefixGroups,
			'prefixCount' => $prefixCount,
		);

		return $this->responseView('XenResource_ViewAdmin_Prefix_List', 'resource_prefix_list', $viewParams);
	}

	protected function _getPrefixAddEditResponse(array $prefix,
		$viewName = 'XenResource_ViewAdmin_Prefix_Edit',
		$templateName = 'resource_prefix_edit',
		$viewParams = array())
	{
		$userGroups = $this->_getUserGroupModel()->getAllUserGroups();

		$prefixModel = $this->_getPrefixModel();
		$phraseModel = $this->_getPhraseModel();

		if (!empty($prefix['prefix_id']))
		{
			$selCategoryIds = $prefixModel->getCategoryAssociationsByPrefix($prefix['prefix_id']);

			$selUserGroupIds = explode(',', $prefix['allowed_user_group_ids']);
			if (in_array(-1, $selUserGroupIds))
			{
				$allUserGroups = true;
				$selUserGroupIds = array_keys($userGroups);
			}
			else
			{
				$allUserGroups = false;
			}

			$masterTitle = $phraseModel->getMasterPhraseValue(
				$prefixModel->getPrefixTitlePhraseName($prefix['prefix_id'])
			);
		}
		else
		{
			$selCategoryIds = array();
			$allUserGroups = true;
			$selUserGroupIds = array_keys($userGroups);
			$masterTitle = '';
		}

		if (!$selCategoryIds)
		{
			$selCategoryIds = array(0);
		}

		$displayStyles = array(
			'',
			'prefix prefixPrimary',
			'prefix prefixSecondary',
			'prefix prefixGreen',
			'prefix prefixOlive',
			'prefix prefixLightGreen',
			'prefix prefixBlue',
			'prefix prefixRoyalBlue',
			'prefix prefixSkyBlue',
			'prefix prefixRed',
			'prefix prefixOrange',
			'prefix prefixYellow',
			'prefix prefixGray',
			'prefix prefixSilver',
		);

		$viewParams = array_merge(array(
			'prefix' => $prefix,
			'prefixGroupOptions' => $prefixModel->getPrefixGroupOptions($prefix['prefix_group_id']),

			'selCategoryIds' => $selCategoryIds,
			'allUserGroups' => $allUserGroups,
			'selUserGroupIds' => $selUserGroupIds,
			'masterTitle' => $masterTitle,

			'displayStyles' => $displayStyles,
			'displayStylesOther' => !in_array($prefix['css_class'], $displayStyles),

			'categories' => $this->_getCategoryModel()->getAllCategories(),
			'userGroups' => $userGroups
		), $viewParams);
		return $this->responseView($viewName, $templateName, $viewParams);
	}

	public function actionAdd()
	{
		return $this->_getPrefixAddEditResponse($this->_getPrefixModel()->getDefaultPrefixValues());
	}

	public function actionEdit()
	{
		$prefixId = $this->_input->filterSingle('prefix_id', XenForo_Input::UINT);
		$prefix = $this->_getPrefixOrError($prefixId);

		return $this->_getPrefixAddEditResponse($prefix);
	}

	public function actionSave()
	{
		$this->_assertPostOnly();

		$prefixId = $this->_input->filterSingle('prefix_id', XenForo_Input::UINT);

		$input = $this->_input->filter(array(
			'title' => XenForo_Input::STRING,
			'prefix_group_id' => XenForo_Input::UINT,
			'display_order' => XenForo_Input::UINT,
			'css_class' => XenForo_Input::STRING,
			'usable_user_group_type' => XenForo_Input::STRING,
			'user_group_ids' => array(XenForo_Input::UINT, 'array' => true),
			'resource_category_ids' => array(XenForo_Input::UINT, 'array' => true),
		));

		if ($input['usable_user_group_type'] == 'all')
		{
			$allowedGroupIds = array(-1); // -1 is a sentinel for all groups
		}
		else
		{
			$allowedGroupIds = $input['user_group_ids'];
		}

		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Prefix');
		if ($prefixId)
		{
			$dw->setExistingData($prefixId);
		}
		$dw->bulkSet(array(
			'prefix_group_id' => $input['prefix_group_id'],
			'display_order' => $input['display_order'],
			'css_class' => $input['css_class'],
			'allowed_user_group_ids' => $allowedGroupIds
		));
		$dw->setExtraData(XenResource_DataWriter_Prefix::DATA_TITLE, $input['title']);
		$dw->save();

		$this->_getPrefixModel()->updatePrefixCategoryAssociationByPrefix($dw->get('prefix_id'), $input['resource_category_ids']);

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('resource-prefixes') . $this->getLastHash($dw->get('prefix_id'))
		);
	}

	/**
	 * Deletes the specified prefix
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionDelete()
	{
		if ($this->isConfirmedPost())
		{
			return $this->_deleteData(
				'XenResource_DataWriter_Prefix', 'prefix_id',
				XenForo_Link::buildAdminLink('resource-prefixes')
			);
		}
		else // show confirmation dialog
		{
			$prefixId = $this->_input->filterSingle('prefix_id', XenForo_Input::UINT);
			$prefix = $this->_getPrefixOrError($prefixId);

			$viewParams = array(
				'prefix' => $prefix
			);
			return $this->responseView('XenResource_ViewAdmin_Prefix_Delete', 'resource_prefix_delete', $viewParams);
		}
	}

	protected function _getPrefixGroupAddEditResponse(array $prefixGroup)
	{
		if (!empty($prefixGroup['prefix_group_id']))
		{
			$masterTitle = $this->_getPhraseModel()->getMasterPhraseValue(
				$this->_getPrefixModel()->getPrefixGroupTitlePhraseName($prefixGroup['prefix_group_id'])
			);
		}
		else
		{
			$masterTitle = '';
		}

		$viewParams = array(
			'prefixGroup' => $prefixGroup,
			'masterTitle' => $masterTitle
		);

		return $this->responseView('XenResource_ViewAdmin_Prefix_Group_Edit', 'resource_prefix_group_edit', $viewParams);
	}

	public function actionAddGroup()
	{
		return $this->_getPrefixGroupAddEditResponse(array(
			'display_order' => 1
		));
	}

	public function actionEditGroup()
	{
		$prefixGroupId = $this->_input->filterSingle('prefix_group_id', XenForo_Input::UINT);
		$prefixGroup = $this->_getPrefixGroupOrError($prefixGroupId);

		return $this->_getPrefixGroupAddEditResponse($prefixGroup);
	}

	public function actionSaveGroup()
	{
		$this->_assertPostOnly();

		$prefixGroupId = $this->_input->filterSingle('prefix_group_id', XenForo_Input::UINT);

		$input = $this->_input->filter(array(
			'title' => XenForo_Input::STRING,
			'display_order' => XenForo_Input::UINT
		));

		$dw = XenForo_DataWriter::create('XenResource_DataWriter_PrefixGroup');
		if ($prefixGroupId)
		{
			$dw->setExistingData($prefixGroupId);
		}
		$dw->set('display_order', $input['display_order']);
		$dw->setExtraData(XenResource_DataWriter_Prefix::DATA_TITLE, $input['title']);
		$dw->save();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('resource-prefixes') . $this->getLastHash('group_' . $dw->get('prefix_group_id'))
		);
	}

	public function actionDeleteGroup()
	{
		$prefixGroupId = $this->_input->filterSingle('prefix_group_id', XenForo_Input::UINT);

		if ($this->isConfirmedPost())
		{
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_PrefixGroup');
			$dw->setExistingData($prefixGroupId);
			$dw->delete();

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildAdminLink('resource-prefixes'));
		}
		else
		{
			$viewParams = array(
				'prefixGroup' => $this->_getPrefixGroupOrError($prefixGroupId)
			);

			return $this->responseView(
				'XenResource_ViewAdmin_Prefix_Group_Delete',
				'resource_prefix_group_delete', $viewParams);
		}
	}

	/**
	 * Gets a valid prefix group or throws an exception.
	 *
	 * @param integer $prefixGroupId
	 *
	 * @return array
	 */
	protected function _getPrefixGroupOrError($prefixGroupId)
	{
		$info = $this->_getPrefixModel()->getPrefixGroupById($prefixGroupId);
		if (!$info)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('requested_prefix_group_not_found'), 404));
		}

		return $this->_getPrefixModel()->preparePrefixGroup($info);
	}

	/**
	 * Gets a valid prefix or throws an exception.
	 *
	 * @param integer $prefixId
	 *
	 * @return array
	 */
	protected function _getPrefixOrError($prefixId)
	{
		$info = $this->_getPrefixModel()->getPrefixById($prefixId);
		if (!$info)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('requested_prefix_not_found'), 404));
		}

		return $this->_getPrefixModel()->preparePrefix($info);
	}

	/**
	 * @return XenResource_Model_Prefix
	 */
	protected function _getPrefixModel()
	{
		return $this->getModelFromCache('XenResource_Model_Prefix');
	}

	/**
	 * @return XenForo_Model_Phrase
	 */
	protected function _getPhraseModel()
	{
		return $this->getModelFromCache('XenForo_Model_Phrase');
	}

	/**
	 * @return XenResource_Model_Category
	 */
	protected function _getCategoryModel()
	{
		return $this->getModelFromCache('XenResource_Model_Category');
	}

	/**
	 * @return XenForo_Model_UserGroup
	 */
	protected function _getUserGroupModel()
	{
		return $this->getModelFromCache('XenForo_Model_UserGroup');
	}
}