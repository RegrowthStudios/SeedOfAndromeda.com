<?php

class EWRporta_Listener_Template
{
	public static function template_hook($name, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		if ($name === 'thread_view_pagenav_before')
		{
			$promoModel = XenForo_Model::create('EWRporta_Model_Promotes');
			$permsModel = XenForo_Model::create('EWRporta_Model_Perms');
			$perms = $permsModel->getPermissions();

			if ($perms['promote'])
			{
				$hookParams['promotion'] = $promoModel->getPromoteByThreadId($hookParams['thread']['thread_id']);
				$contents .= $template->create('EWRporta_ThreadView', $hookParams);
			}
		}

		switch ($name)
		{
			case 'forum_list_nodes':
			case 'forum_list_sidebar':
			case 'forum_view_pagenav_before':
			case 'forum_view_pagenav_after':
			case 'thread_view_pagenav_before':
			case 'thread_view_share_after':
				$custom = true;
				break;
		}

		if (!empty($custom))
		{
			$params = $template->getParams();

			if (!empty($params['blocks']))
			{
				$hookParams['blocks'] = $params['blocks'];

				switch ($name)
				{
					case 'forum_list_nodes':
						$contents = $template->create('EWRporta_Custom_Top', $hookParams) . $contents;
						$contents .= $template->create('EWRporta_Custom_Btm', $hookParams);
						break;
					case 'forum_list_sidebar':
						$contents = $template->create('EWRporta_Custom_Side', $hookParams);
						break;
					case 'forum_view_pagenav_before':
					case 'thread_view_pagenav_before':
						$contents = $template->create('EWRporta_Custom_Top', $hookParams) . $contents;
						break;
					case 'forum_view_pagenav_after':
					case 'thread_view_share_after':
						$contents .= $template->create('EWRporta_Custom_Btm', $hookParams);
						break;
				}
			}
		}
	}
}