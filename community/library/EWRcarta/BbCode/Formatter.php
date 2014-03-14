<?php

class EWRcarta_BbCode_Formatter extends XFCP_EWRcarta_BbCode_Formatter
{
    protected $_tags;

    public function getTags()
    {
        $this->_tags = parent::getTags();

		$headTags = array(
			'hasOption' => false,
			'plainChildren' => true,
			'stopSmilies' => true,
			'trimLeadingLinesAfter' => 1,
		);

        $this->_tags['h2'] = $headTags + array('replace' => array('<h2>', '</h2>'));
        $this->_tags['h3'] = $headTags + array('replace' => array('<h3>', '</h3>'));
        $this->_tags['h4'] = $headTags + array('replace' => array('<h4>', '</h4>'));
        $this->_tags['h5'] = $headTags + array('replace' => array('<h5>', '</h5>'));
        $this->_tags['h6'] = $headTags + array('replace' => array('<h6>', '</h6>'));

        $this->_tags['wiki'] = array(
			'trimLeadingLinesAfter' => 1,
			'callback' => array($this, 'renderTagWiki')
		);

        $this->_tags['toggle'] = array(
			'trimLeadingLinesAfter' => 1,
			'replace' => array('<div class="ToggleContents">', '</div>')
		);

        $this->_tags['contents'] = array(
			'trimLeadingLinesAfter' => 1,
			'replace' => array('<div class="contents">', '</div>')
		);

        return $this->_tags;
    }

	public function renderTagWiki(array $tag, array $rendererStates)
	{
		$text = $this->renderSubTree($tag['children'], $rendererStates);
		$topt = $tag['option'];

		if ($page = XenForo_Model::create('EWRcarta_Model_Pages')->getPageBySlug($text))
		{
			if ($this->_view && $topt == 'full')
			{
				$perms = XenForo_Model::create('EWRcarta_Model_Perms')->getPermissions();

				if ($perms['view'] && $cache = XenForo_Model::create('EWRcarta_Model_Cache')->getCache($page))
				{
					$page['HTML'] = $cache['cache_content'];
					$page['cache'] = $cache['cache_date'];
					$template = $this->_view->createTemplateObject('EWRcarta_BBcode', array('page' => $page));

					return $template->render();
				}
			}

			return '<a href="'.XenForo_Link::buildPublicLink('wiki', $page).'">' . $page['page_name'] . '</a>';
		}
		else
		{
			return '[wiki]'.$text.'[/wiki]';
		}
	}
}