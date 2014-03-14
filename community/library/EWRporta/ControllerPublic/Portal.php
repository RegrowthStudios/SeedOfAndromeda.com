<?php

class EWRporta_ControllerPublic_Portal extends XenForo_ControllerPublic_Abstract
{
	public $perms;

	public function actionIndex()
	{
		if ($this->_routeMatch->getResponseType() == 'rss')
		{
			$cache = $this->getModelFromCache('EWRporta_Model_Caches')->getCacheByBlockId('RecentNews');
			$threads = unserialize($cache['results']);
			
			$viewParams = array(
				'threads' => $threads['RecentNews']
			);
			
			return $this->responseView('EWRporta_ViewPublic_Portal', '', $viewParams);
		}
		
		$this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('portal'));

		$options = XenForo_Application::get('options');

		if ($options->EWRporta_stylechoice['force'] && $options->EWRporta_stylechoice['style'])
		{
			$this->setViewStateChange('styleId', $options->EWRporta_stylechoice['style']);
		}

		$viewParams = array(
			'cookie' => $this->perms['custom'],
			'isPortal' => true,
			'layout1' => 'portal',
			'page' => max(1, $this->_input->filterSingle('page', XenForo_Input::UINT)),
		);

		return $this->responseView('EWRporta_ViewPublic_Portal', 'EWRporta_Portal', $viewParams);
	}

	public function actionRevert()
	{
		if ($cookies = XenForo_Helper_Cookie::getCookie('EWRporta'))
		{
			foreach ($cookies AS $key => $cookie)
			{
				XenForo_Helper_Cookie::deleteCookie('EWRporta['.$key.'][order]');
				XenForo_Helper_Cookie::deleteCookie('EWRporta['.$key.'][position]');
			}
		}

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('portal/blocks'));
	}

	public function actionBlocks()
	{
		if (!$this->perms['custom']) { return $this->responseNoPermission(); }

		if ($this->_request->isPost())
		{
			$order = 1;
			$blocks = $this->_input->filterSingle('blocks', XenForo_Input::ARRAY_SIMPLE);

			foreach ($blocks AS $key => $block)
			{
				XenForo_Helper_Cookie::setCookie('EWRporta['.$key.'][order]', $order++, 31536000);
				XenForo_Helper_Cookie::setCookie('EWRporta['.$key.'][position]', $block, 31536000);
			}

			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('portal/blocks'));
		}

		$blocks = $this->getModelFromCache('EWRporta_Model_Blocks')->getBlocks(true, 'portal');

		$_blocks = array(
			'top-left' => array(),
			'top-right' => array(),
			'mid-left' => array(),
			'mid-right' => array(),
			'btm-left' => array(),
			'btm-right' => array(),
			'sidebar' => array()
		);

		foreach ($blocks AS $block)
		{
			switch ($block['position'])
			{
				case 'top-left':	$_blocks['top-left'][] = $block;	break;
				case 'top-right':	$_blocks['top-right'][] = $block;	break;
				case 'mid-left':	$_blocks['mid-left'][] = $block;	break;
				case 'mid-right':	$_blocks['mid-right'][] = $block;	break;
				case 'btm-left':	$_blocks['btm-left'][] = $block;	break;
				case 'btm-right':	$_blocks['btm-right'][] = $block;	break;
				case 'sidebar':		$_blocks['sidebar'][] = $block;		break;
				default:			$_blocks['disabled'][] = $block;
			}
		}

		$viewParams = array(
			'adminModules' => false,
			'blocks' => $_blocks,
		);

		return $this->responseView('EWRporta_ViewPublic_Blocks', 'EWRporta_Blocks', $viewParams);
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

	protected function _preDispatch($action)
	{
		parent::_preDispatch($action);

		$this->perms = $this->getModelFromCache('EWRporta_Model_Perms')->getPermissions();
	}
}