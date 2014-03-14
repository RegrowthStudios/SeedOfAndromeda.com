<?php

class EWRporta_ControllerPublic_Forum extends XFCP_EWRporta_ControllerPublic_Forum
{
	public $perms;

	public function actionIndex()
	{
		$response = parent::actionIndex();
		$options = XenForo_Application::get('options');

		if ($response instanceof XenForo_ControllerResponse_View && $options->EWRporta_globalize['index'])
		{
			$response->params['layout1'] = 'index';
			$response->params['layout2'] = 'portal';
		}

		return $response;
	}

	public function actionForum()
	{
		$response = parent::actionForum();
		$options = XenForo_Application::get('options');

		if ($response instanceof XenForo_ControllerResponse_View && $options->EWRporta_globalize['forum'])
		{
			$node_id = !empty($response->params['forum']['node_id']) ? $response->params['forum']['node_id'] : 0;
			$response->params['layout1'] = 'forum-'.$node_id;
			$response->params['layout2'] = 'forum';
		}

		return $response;
	}

	protected function _preDispatch($action)
	{
		parent::_preDispatch($action);

		$this->perms = $this->getModelFromCache('EWRporta_Model_Perms')->getPermissions();
	}
}