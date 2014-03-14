<?php

class EWRcarta_Model_Lists extends XenForo_Model
{
	public function getPages()
	{
        $pages = $this->_getDb()->fetchAll("
			SELECT page_id, page_slug, page_name, page_parent, page_date
				FROM EWRcarta_pages
			ORDER BY page_name ASC
		");

		return $pages;
	}

	public function getPageList($parent = 0, &$fullPageList = array(), $depth = 0, $pages = false, $alphaKey = false, $letter = false, $self = 0)
	{
		if (!$pages) { $pages = $this->getPages(); }

		foreach ($pages AS $page)
		{
			if ($page['page_parent'] == $parent)
			{
				$page['page_depth'] = $depth;
				$page['page_indent'] = "";

				for ($counter = 1; $counter <= $depth; $counter++)
				{
					$page['page_indent'] .= "&nbsp; &nbsp; ";
				}

				if ($alphaKey)
				{
					$tempLetter = $letter ? $letter : strtoupper(substr($page['page_name'], 0, 1));
					$fullPageList[$tempLetter][$page['page_id']] = $page;					
					$this->getPageList($page['page_id'], $fullPageList, $depth+1, $pages, $alphaKey, $tempLetter);
				}
				else
				{
					if ($self && $depth > 1 && $page['page_parent'] != $self) { break; }
					
					$fullPageList[$page['page_id']] = $page;					
					$this->getPageList($page['page_id'], $fullPageList, $depth+1, $pages, false, false, $self);
				}
			}
		}

		return $fullPageList;
	}

	public function getIndex()
	{
		return $this->_getDb()->fetchRow("SELECT page_name FROM EWRcarta_pages WHERE page_slug = 'index'");
	}

	public function getSideList()
	{
        $pages = $this->_getDb()->fetchAll("
			SELECT page_id, page_slug, page_name
				FROM EWRcarta_pages
			WHERE page_index != '0'
			ORDER BY page_index ASC, page_name ASC
		");

		return $pages;
	}

	public function getRelated($page, $self = 0)
	{
		$related = array();

		if ($page['page_parent'])
		{
			$topPage = $this->_getDb()->fetchRow("
				SELECT page_id, page_slug, page_name, page_parent
					FROM EWRcarta_pages
				WHERE page_id = ?
			", $page['page_parent']);
			
			if ($self) { $topPage['page_parent'] = 0; }

			$related = $this->getRelated($topPage, $self);
		}
		else
		{
			$related[$page['page_id']] = $page;
			$related = $this->getPageList($page['page_id'], $related, 1, false, false, false, $self);
		}

		return $related;
	}

	public function getCrumbs($page, &$breadCrumbs = array())
	{
		$breadCrumbs[$page['page_slug']] = array(
			 'value' => $page['page_name'],
			 'href' => XenForo_Link::buildPublicLink('full:wiki', $page), 
		);

		if ($page['page_parent'])
		{
			$topPage = $this->_getDb()->fetchRow("
				SELECT page_slug, page_name, page_parent
					FROM EWRcarta_pages
				WHERE page_id = ?
			", $page['page_parent']);

			$breadCrumbs = $this->getCrumbs($topPage, $breadCrumbs);
		}

		return $breadCrumbs;
	}

	public function getTemplates()
	{
        $templates = $this->_getDb()->fetchAll("
			SELECT template_name
				FROM EWRcarta_templates
			ORDER BY template_name ASC
		");

		return $templates;
	}
}