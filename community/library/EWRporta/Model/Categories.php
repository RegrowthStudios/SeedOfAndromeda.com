<?php

class EWRporta_Model_Categories extends XenForo_Model
{
	public function getCategories()
	{
		$categories = $this->fetchAllKeyed("
			SELECT *
				FROM EWRporta_categories
			ORDER BY category_name ASC
		", 'category_id');
		
		$major = array();
		$minor = array();
		
		foreach ($categories AS $category)
		{
			$id = $category['category_id'];
		
			if ($category['category_type'] == 'major')
			{
				$major[$id] = $category;
			}
			else
			{
				$minor[$id] = $category;
			}
		}
		
		return array(
			'major' => $major,
			'minor' => $minor,
		);
	}
	
	public function getCategoriesCount()
	{
		return $this->fetchAllKeyed("
			SELECT EWRporta_categories.*, COUNT(EWRporta_catlinks.catlink_id) AS count
				FROM EWRporta_categories
				INNER JOIN EWRporta_catlinks ON (EWRporta_catlinks.category_id = EWRporta_categories.category_id)
			GROUP BY EWRporta_categories.category_id
			ORDER BY category_name ASC
		", 'category_id');
	}

	public function getCategoryById($categoryId)
	{
		if (!$category = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRporta_categories
			WHERE category_id = ?
		", $categoryId))
		{
			return false;
		}

		return $category;
	}

	public function getCategoryBySlug($categorySlug)
	{
		if (!$category = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRporta_categories
			WHERE category_slug = ?
		", $categorySlug))
		{
			return false;
		}

		return $category;
	}

	public function getCategoryLinkById($linkID)
	{
		if (!$catlink = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRporta_catlinks
			WHERE catlink_id = ?
		", $linkID))
		{
			return false;
		}

        return $catlink;
	}

	public function getCategoryLinks($thread)
	{
		$catlinks = $this->_getDb()->fetchAll("
			SELECT *
			FROM EWRporta_categories
				INNER JOIN EWRporta_catlinks ON (EWRporta_catlinks.category_id = EWRporta_categories.category_id)
			WHERE thread_id = ?
			ORDER BY EWRporta_categories.category_type ASC, EWRporta_categories.category_name ASC
		", $thread['thread_id']);

		return $catlinks;
	}

	public function getCategoryNolinks($thread)
	{
		$categories = $this->_getDb()->fetchAll("
			SELECT EWRporta_categories.*
				FROM EWRporta_categories
				LEFT JOIN EWRporta_catlinks ON (EWRporta_catlinks.category_id = EWRporta_categories.category_id AND EWRporta_catlinks.thread_id = ?)
			WHERE EWRporta_catlinks.thread_id IS NULL
			ORDER BY EWRporta_categories.category_name ASC
		", $thread['thread_id']);
		
		$major = array();
		$minor = array();
		
		foreach ($categories AS $category)
		{
			$id = $category['category_id'];
		
			if ($category['category_type'] == 'major')
			{
				$major[$id] = $category;
			}
			else
			{
				$minor[$id] = $category;
			}
		}
		
		return array(
			'major' => $major,
			'minor' => $minor,
		);

		return $categories;
	}

	public function updateCategory($input)
	{
		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Categories');
		if ($this->getCategoryById($input['category_id']))
		{
			$dw->setExistingData($input['category_id']);
		}
		$dw->bulkSet($input);
		$dw->save();

		XenForo_Db::commit($db);

		return $input;
	}

	public function deleteCategory($category)
	{
		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Categories');
		$dw->setExistingData($category);
		$dw->delete();

		$db->query("
			DELETE FROM EWRporta_catlinks
			WHERE category_id = ?
		", $category['category_id']);

		XenForo_Db::commit($db);

		return true;
	}

	public function updateCategories($input)
	{
		if (!empty($input['oldlinks']))
		{
			$this->deleteCategoryLinks($input['oldlinks'], $input['catlinks']);
		}

		if (!empty($input['newlinks']))
		{
			$this->updateCategoryLinks($input['newlinks'], $input['thread_id']);
		}
			
		$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache(array('block_id'=>'RecentFeatures'));
		$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache(array('block_id'=>'RecentNews'));

		return true;
	}

	public function getCatlinkByCategoryAndThread($categoryID, $threadID)
	{
		if (!$catlink = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRporta_catlinks
			WHERE category_id = ? AND thread_id = ?
		", array($categoryID, $threadID)))
		{
			return false;
		}

        return $catlink;
	}

	public function updateCategoryLinks($newlinks, $threadID)
	{
		foreach ($newlinks AS $categoryID)
		{
			if (!$link = $this->getCatlinkByCategoryAndThread($categoryID, $threadID))
			{
				$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Catlinks');
				$dw->set('category_id', $categoryID);
				$dw->set('thread_id', $threadID);
				$dw->save();
			}
		}

		return true;
	}

	public function deleteCategoryLinks($oldlinks, $catlinks)
	{
		foreach ($oldlinks AS $key => $value)
		{
			if (!array_key_exists($key, $catlinks))
			{
				$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Catlinks');
				$dw->setExistingData(array('catlink_id' => $key));
				$dw->delete();
			}
		}

		return true;
	}
}