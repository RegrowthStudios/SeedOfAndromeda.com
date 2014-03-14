<?php

class EWRporta_ViewPublic_Custom extends XFCP_EWRporta_ViewPublic_Custom
{
	public function renderHtml()
	{
        $response = parent::renderHtml();
		if (empty($this->_params['layout1'])) { return $response; }

		$this->_params['layout2'] = empty($this->_params['layout2']) ? false : $this->_params['layout2'];
		$this->_params['layout3'] = empty($this->_params['layout3']) ? false : $this->_params['layout3'];
		$this->_params['cookie'] = empty($this->_params['cookie']) ? false : $this->_params['cookie'];
		$this->_params['category'] = empty($this->_params['category']) ? false : $this->_params['category'];
		$isPortal = empty($this->_params['isPortal']) ? false : true;
		$isArticle = empty($this->_params['isArticle']) ? false : true;

		$blocksModel = XenForo_Model::create('EWRporta_Model_Blocks');
		$blocks = $blocksModel->getBlocks($this->_params['cookie'], $this->_params['layout1'], $this->_params['layout2'], $this->_params['layout3']);

		if (empty($blocks)) { return $response; }

		if ($isArticle && !empty($this->_params['thread']['first_post_id']))
		{
			$this->_params['posts'][$this->_params['thread']['first_post_id']]['attachments'] = false;
			$this->_params['posts'][$this->_params['thread']['first_post_id']]['signature'] = false;
		}

		$cachesModel = XenForo_Model::create('EWRporta_Model_Caches');
		$caches = $cachesModel->getCaches();

		$optionsModel = XenForo_Model::create('EWRporta_Model_Options');
		$options = $optionsModel->getOptions();

		$visitor = XenForo_Visitor::getInstance();

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
			if ($block['position'] == 'disabled') { continue; }
			if (!$isPortal && ($block['position'] == 'mid-left' || $block['position'] == 'mid-right')) { continue; }

			if (!empty($block['groups']))
			{
				$groups = explode(',', $block['groups']);
				$member = false;

				foreach ($groups AS $group)
				{
					if ($visitor->isMemberOf($group)) { $member = true; break; }
				}

				if ($block['display'] == 'hide' && $member) { continue; }
				if ($block['display'] == 'show' && !$member) { continue; }
			}

			$block['layout'] = $this->_params['layout1'];
			$block['category'] = $this->_params['category'];
			$block['caches'] = !empty($caches[$block['block_id']]) ? $caches[$block['block_id']] : false;
			$block['options'] = !empty($options[$block['block_id']]) ? $options[$block['block_id']] : false;

			$page = $isPortal ? $this->_params['page'] : false;
			$params = $blocksModel->getBlockParams($block, $page);
			if (!empty($params[$block['block_id']]) && $params[$block['block_id']] == 'killModule') { continue; }

			if (!empty($params['option']['parseBB']))
			{
				$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
				$bbCodeOptions = array('states' => array('viewAttachments' => true));
				XenForo_ViewPublic_Helper_Message::bbCodeWrapMessages($params[$block['block_id']], $bbCodeParser, $bbCodeOptions);
			}

			if (!empty($params['option']['parseText']))
			{
				foreach ($params[$block['block_id']] AS &$message)
				{
					$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('XenForo_BbCode_Formatter_Text'));
					$message['messageText'] = $bbCodeParser->render(str_ireplace('\n', ' ', $message['message']));
					$message['messageText'] = str_ireplace("\n", " ", $message['messageText']);
				}
			}

			$object = $this->createTemplateObject('EWRblock_'.$block['block_id'], $params);

			switch ($block['position'])
			{
				case 'top-left':	$_blocks['top-left'][] = $object;	break;
				case 'top-right':	$_blocks['top-right'][] = $object;	break;
				case 'mid-left':	$_blocks['mid-left'][] = $object;	break;
				case 'mid-right':	$_blocks['mid-right'][] = $object;	break;
				case 'btm-left':	$_blocks['btm-left'][] = $object;	break;
				case 'btm-right':	$_blocks['btm-right'][] = $object;	break;
				case 'sidebar':		$_blocks['sidebar'][] = $object;	break;
			}
		}

		$this->_params['blocks'] = $_blocks;

		return $response;
	}
}