<?php

class EWRporta_ControllerPublic_Articles extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		$categorySlug = $this->_input->filterSingle('category_slug', XenForo_Input::STRING);
		$category = $this->getModelFromCache('EWRporta_Model_Categories')->getCategoryBySlug($categorySlug);
		
		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));

		$this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('articles', $category, array('page' => $page)));

		$options = XenForo_Application::get('options');

		if ($options->EWRporta_stylechoice['force'] && $options->EWRporta_stylechoice['style'])
		{
			$this->setViewStateChange('styleId', $options->EWRporta_stylechoice['style']);
		}

		if (!empty($category['style_id']))
		{
			$this->setViewStateChange('styleId', $category['style_id']);
		}
		
		$breadCrumbs = array();
		if ($category)
		{
			$breadCrumbs['articles'] = array(
				 'value' => new XenForo_Phrase('articles'),
				 'href' => XenForo_Link::buildPublicLink('full:articles'), 
			);
		}

		$viewParams = array(
			'isPortal' => true,
			'layout1' => 'articles-'.$category['category_slug'],
			'layout2' => 'articles',
			'layout3' => 'portal',
			'category' => $category['category_slug'],
			'title' => $category['category_name'],
			'breadCrumbs' => $breadCrumbs,
			'page' => max(1, $page),
		);

		return $this->responseView('EWRporta_ViewPublic_Portal', 'EWRporta_Portal', $viewParams);
	}

	public static function getSessionActivityDetailsForList(array $activities)
	{
        $output = array();

        foreach ($activities as $key => $activity)
		{
			$output[$key] = array(
				new XenForo_Phrase('viewing_portal'),
				new XenForo_Phrase('index'),
				XenForo_Link::buildPublicLink('portal'),
				false
			);
        }

        return $output;
	}
}