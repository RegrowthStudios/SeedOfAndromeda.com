<?php

class EWRporta_Block_Controller_Donations extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		$driveId = $this->_input->filterSingle('drive_id', XenForo_Input::STRING);
		if (!$driveId)
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('portal'));
		}

		$this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('donations', array('drive_id' => $driveId)));

		$donations = $this->getModelFromCache('EWRporta_Block_Donations')->getDonationsByDriveId($driveId);

		$viewParams = array(
			'drive' => $this->getModelFromCache('EWRporta_Block_Donations')->getDriveById($driveId),
			'donations' => $this->getModelFromCache('EWRporta_Block_Donations')->getDonationsByDriveId($driveId)
		);

		return $this->responseView('EWRporta_ViewPublic_Donations_List', 'EWRblock_Donations_list', $viewParams);
	}

	public function actionThanks()
	{
		$viewParams = array();

		return $this->responseView('EWRporta_ViewPublic_Donations_Thanks', 'EWRblock_Donations_thanks', $viewParams);
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

	protected function _checkCsrf($action)
	{
		if (strtolower($action) == 'thanks')
		{
			return;
		}

		parent::_checkCsrf($action);
	}
}