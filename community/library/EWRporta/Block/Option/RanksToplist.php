<?php

class EWRporta_Block_Option_RanksToplist
{
	public static function renderSelect(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		return self::_render('option_list_option_select', $view, $fieldPrefix, $preparedOption, $canEdit);
	}
	
	public static function getLeagueOptions($selectedLeague, $unspecifiedPhrase = false)
	{
		$leaguesModel = XenForo_Model::create('EWRtorneo_Model_Leagues');

		$allLeagues = $leaguesModel->getLeagues();
		$options = array();
		
		foreach ($allLeagues AS $league)
		{
			$options[] = array(
				'label' => $league['league_name'],
				'value' => $league['league_id'],
				'selected' => ($selectedLeague == $league['league_id'])
			);
		}

		if ($unspecifiedPhrase)
		{
			$options = array_merge(array(array
			(
				'label' => $unspecifiedPhrase,
				'value' => 0,
				'selected' => ($selectedLeague == 0)
			)), $options);
		}

		return $options;
	}
	
	protected static function _render($templateName, XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$preparedOption['formatParams'] = self::getLeagueOptions(
			$preparedOption['option_value'],
			sprintf('(%s)', new XenForo_Phrase('unspecified'))
		);

		return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
			$templateName, $view, $fieldPrefix, $preparedOption, $canEdit
		);
	}
}