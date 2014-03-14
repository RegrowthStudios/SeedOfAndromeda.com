<?php

/**
* Data writer for categories
*/
class XenResource_DataWriter_Category extends XenForo_DataWriter
{
	/**
	 * Title of the phrase that will be created when a call to set the
	 * existing data fails (when the data doesn't exist).
	 *
	 * @var string
	 */
	protected $_existingDataErrorPhrase = 'requested_category_not_found';

	/**
	 * Option that represents whether associated caches will be automatically
	 * rebuilt. Defaults to true.
	 *
	 * @var string
	 */
	const OPTION_REBUILD_CACHE = 'rebuildCache';

	const DATA_FIELD_IDS = 'fieldIds';

	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields()
	{
		return array(
			'xf_resource_category' => array(
				'resource_category_id' => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'category_title'       => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 100,
					'requiredError' => 'please_enter_valid_title'
				),
				'category_description' => array('type' => self::TYPE_STRING, 'default' => 0),
				'parent_category_id'   => array('type' => self::TYPE_UINT, 'default' => 0,
					'verification' => array('$this', '_validateParentCategoryId')
					),
				'depth'                => array('type' => self::TYPE_UINT, 'default' => 0),
				'lft'                  => array('type' => self::TYPE_UINT, 'default' => 0),
				'rgt'                  => array('type' => self::TYPE_UINT, 'default' => 0),
				'display_order'        => array('type' => self::TYPE_UINT, 'default' => 1),
				'resource_count'       => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
				'last_update'          => array('type' => self::TYPE_UINT, 'default' => 0),
				'last_resource_title'  => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 100),
				'last_resource_id'     => array('type' => self::TYPE_UINT, 'default' => 0),
				'category_breadcrumb'  => array('type' => self::TYPE_SERIALIZED, 'default' => 'a:0:{}'),
				'allow_local'          => array('type' => self::TYPE_BOOLEAN, 'default' => 1),
				'allow_external'       => array('type' => self::TYPE_BOOLEAN, 'default' => 1),
				'allow_commercial_external' => array('type' => self::TYPE_BOOLEAN, 'default' => 1),
				'allow_fileless'       => array('type' => self::TYPE_BOOLEAN, 'default' => 1),
				'thread_node_id'       => array('type' => self::TYPE_UINT, 'default' => 0),
				'thread_prefix_id'     => array('type' => self::TYPE_UINT, 'default' => 0),
				'always_moderate_create'      => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
				'always_moderate_update'      => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
				'field_cache'          => array('type' => self::TYPE_SERIALIZED, 'default' => ''),
				'prefix_cache'         => array('type' => self::TYPE_SERIALIZED, 'default' => ''),
				'require_prefix'       => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
				'featured_count'       => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
			)
		);
	}

	/**
	* Gets the actual existing data out of data that was passed in. See parent for explanation.
	*
	* @param mixed
	*
	* @return array|bool
	*/
	protected function _getExistingData($data)
	{
		if (!$id = $this->_getExistingPrimaryKey($data))
		{
			return false;
		}

		return array('xf_resource_category' => $this->_getCategoryModel()->getCategoryById($id));
	}

	/**
	* Gets SQL condition to update the existing record.
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
		return 'resource_category_id = ' . $this->_db->quote($this->getExisting('resource_category_id'));
	}

	/**
	* Gets the default set of options for this data writer.
	*
	* @return array
	*/
	protected function _getDefaultOptions()
	{
		return array(
			self::OPTION_REBUILD_CACHE => true
		);
	}

	protected function _validateParentCategoryId(&$parentId)
	{
		if ($this->isUpdate() && $parentId != 0 && $parentId != $this->getExisting('parent_category_id'))
		{
			$possibleParents = $this->_getCategoryModel()->getPossibleParentCategories($this->getMergedExistingData());
			if (!isset($possibleParents[$parentId]))
			{
				$this->error(new XenForo_Phrase('please_select_valid_parent_category'), 'parent_category_id');
				return false;
			}
		}

		return true;
	}

	protected function _preSave()
	{
		if ($this->isChanged('thread_node_id') || $this->isChanged('thread_prefix_id'))
		{
			if (!$this->get('thread_node_id'))
			{
				$this->set('thread_prefix_id', 0);
			}
			else
			{
				$forum = $this->getModelFromCache('XenForo_Model_Forum')->getForumById($this->get('thread_node_id'));
				if (!$forum)
				{
					$this->set('thread_node_id', 0);
					$this->set('thread_prefix_id', 0);
				}
				else if ($this->get('thread_prefix_id'))
				{
					$prefix = $this->getModelFromCache('XenForo_Model_ThreadPrefix')->getPrefixIfInForum(
						$this->get('thread_prefix_id'), $forum['node_id']
					);
					if (!$prefix)
					{
						$this->set('thread_prefix_id', 0);
					}
				}
			}
		}
	}

	protected function _postSave()
	{
		if ($this->isInsert()
			|| $this->isChanged('display_order')
			|| $this->isChanged('parent_category_id')
			|| $this->isChanged('category_title')
		)
		{
			$this->_getCategoryModel()->rebuildCategoryStructure();
		}

		$newFieldIds = $this->getExtraData(self::DATA_FIELD_IDS);
		if (is_array($newFieldIds))
		{
			$this->_updateFieldAssociations($newFieldIds);
			$this->_getFieldModel()->rebuildFieldCategoryAssociationCache(array($this->get('resource_category_id')));
		}

		if ($this->isInsert() || $this->isChanged('parent_category_id'))
		{
			if ($this->getOption(self::OPTION_REBUILD_CACHE))
			{
				XenForo_Application::defer('Permission', array(), 'Permission', true);
			}
		}
	}

	protected function _updateFieldAssociations(array $fieldIds)
	{
		$fieldIds = array_unique($fieldIds);

		$db = $this->_db;
		$categoryId = $this->get('resource_category_id');

		$db->delete('xf_resource_field_category', 'resource_category_id = ' . $db->quote($categoryId));

		foreach ($fieldIds AS $fieldId)
		{
			$db->insert('xf_resource_field_category', array(
				'field_id' => $fieldId,
				'resource_category_id' => $categoryId
			));
		}

		return $fieldIds;
	}

	/**
	 * Post-delete handling.
	 */
	protected function _postDelete()
	{
		$categoryId = $this->get('resource_category_id');
		$db = $this->_db;

		$db->update('xf_resource_category',
			array('parent_category_id' => $this->get('parent_category_id')),
			'parent_category_id = ' . $this->_db->quote($categoryId)
		);

		$db->delete('xf_resource_field_category', 'resource_category_id = ' . $db->quote($categoryId));

		$this->_getCategoryModel()->rebuildCategoryStructure();

		if ($this->getOption(self::OPTION_REBUILD_CACHE))
		{
			XenForo_Application::defer('Permission', array(), 'Permission', true);
		}
	}

	/**
	 * Called when a resource is updated in this category.
	 *
	 * @param XenResource_DataWriter_Resource $resource
	 */
	public function resourceUpdate(XenResource_DataWriter_Resource $resource)
	{
		if ($resource->get('resource_state') != 'visible')
		{
			// nothing to do
			return;
		}

		if ($resource->isUpdate() && $resource->isChanged('resource_category_id'))
		{
			$this->updateResourceCount(1);
			$this->updateFeaturedCount();

			$oldCat = XenForo_DataWriter::create('XenResource_DataWriter_Category', XenForo_DataWriter::ERROR_SILENT);
			if ($oldCat->setExistingData($resource->getExisting('resource_category_id')))
			{
				$oldCat->resourceRemoved($resource);
				$oldCat->save();
			}
		}
		else if ($resource->isChanged('resource_state'))
		{
			$this->updateResourceCount(1);
		}

		if ($resource->get('last_update') >= $this->get('last_update'))
		{
			$this->set('last_update', $resource->get('last_update'));
			$this->set('last_resource_title', $resource->get('title'));
			$this->set('last_resource_id', $resource->get('resource_id'));
		}

		if ($resource->isUpdate() && $resource->isChanged('resource_state'))
		{
			$this->updateFeaturedCount();
		}
	}

	/**
	 * Called when a resource is removed from view in this category.
	 * Can apply to moves, deletes, etc.
	 *
	 * @param XenResource_DataWriter_Resource $resource
	 */
	public function resourceRemoved(XenResource_DataWriter_Resource $resource)
	{
		if ($resource->getExisting('resource_state') != 'visible')
		{
			// nothing to do
			return;
		}

		$this->updateResourceCount(-1);
		$this->updateFeaturedCount();

		if ($this->get('last_resource_id') == $resource->get('resource_id'))
		{
			$this->updateLastUpdate();
		}
	}

	public function updateLastUpdate()
	{
		$resource = $this->_db->fetchRow($this->_db->limit(
			"
				SELECT *
				FROM xf_resource
				WHERE resource_category_id = ?
					AND resource_state = 'visible'
				ORDER BY last_update DESC
			", 1
		), $this->get('resource_category_id'));
		if (!$resource)
		{
			$this->set('resource_count', 0);
			$this->set('last_update', 0);
			$this->set('last_resource_title', '');
			$this->set('last_resource_id', 0);
		}
		else
		{
			$this->set('last_update', $resource['last_update']);
			$this->set('last_resource_title', $resource['title']);
			$this->set('last_resource_id', $resource['resource_id']);
		}
	}

	public function updateResourceCount($adjust = null)
	{
		if ($adjust === null)
		{
			$this->set('resource_count', $this->_db->fetchOne("
				SELECT COUNT(*)
				FROM xf_resource
				WHERE resource_category_id = ?
					AND resource_state = 'visible'
			", $this->get('resource_category_id')));
		}
		else
		{
			$this->set('resource_count', $this->get('resource_count') + $adjust);
		}
	}

	public function updateFeaturedCount($adjust = null)
	{
		if ($adjust === null)
		{
			$this->set('featured_count', $this->_db->fetchOne("
				SELECT COUNT(*)
				FROM xf_resource_feature AS feature
				INNER JOIN xf_resource AS resource ON (resource.resource_id = feature.resource_id)
				WHERE resource.resource_category_id = ?
					AND resource_state = 'visible'
			", $this->get('resource_category_id')));
		}
		else
		{
			$this->set('featured_count', $this->get('featured_count') + $adjust);
		}
	}

	public function rebuildCounters()
	{
		$this->updateLastUpdate();
		$this->updateResourceCount();
		$this->updateFeaturedCount();
	}

	/**
	 * @return XenResource_Model_Resource
	 */
	protected function _getResourceModel()
	{
		return $this->getModelFromCache('XenResource_Model_Resource');
	}

	/**
	 * @return XenResource_Model_Category
	 */
	protected function _getCategoryModel()
	{
		return $this->getModelFromCache('XenResource_Model_Category');
	}

	/**
	 * @return XenResource_Model_ResourceField
	 */
	protected function _getFieldModel()
	{
		return $this->getModelFromCache('XenResource_Model_ResourceField');
	}
}