<?php

class EWRporta_ViewAdmin_BlockOptions extends XenForo_ViewAdmin_Base
{
	public function renderHtml()
	{
		$options = array();

		foreach ($this->_params['options'] AS $i => $option)
		{
			$x = floor($option['display_order'] / 100);
			$options[$x][$i] = $option;
		}

		$renderedOptions = array();

		foreach ($options AS $x => $optionGroup)
		{
			$renderedOptions[$x] = XenForo_ViewAdmin_Helper_Option::renderPreparedOptionsHtml(
				$this, $optionGroup, $this->_params['canEdit']
			);

			foreach ($renderedOptions[$x] AS &$renderedOption)
			{
				$renderedOption = preg_replace('#options/edit-option/(\w+)#i', 'ewrporta/options/$1/edit', $renderedOption);
			}
		}

		$this->_params['renderedOptions'] = $renderedOptions;
	}
}