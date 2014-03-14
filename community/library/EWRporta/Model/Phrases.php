<?php

class EWRporta_Model_Phrases extends XenForo_Model
{
	public function getPhrasesByBlock($blockId, $languageId = 0)
	{
		$title = 'EWRblock_'.$blockId;

		return $this->fetchAllKeyed('
			SELECT *
				FROM xf_phrase
			WHERE title LIKE ?
				AND language_id = ?
		', 'title', array($title.'%', $languageId));
	}

	public function deletePhrasesByBlock($blockId)
	{
		$title = 'EWRblock_'.$blockId;

		$db = $this->_getDb();
		$db->delete('xf_phrase', 'language_id = 0 AND title LIKE ' . $db->quote($title.'%'));

		return;
	}

	public function importPhrasesXml(SimpleXMLElement $xml, $blockId)
	{
		$existingPhrases = $this->getPhrasesByBlock($blockId);
		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		$phrases = XenForo_Helper_DevelopmentXml::fixPhpBug50670($xml->phrase);
		foreach ($phrases AS $phrase)
		{
			$phraseName = (string)$phrase['title'];

			$dw = XenForo_DataWriter::create('XenForo_DataWriter_Phrase');
			if (isset($existingPhrases[$phraseName]))
			{
				$dw->setExistingData($existingPhrases[$phraseName], true);
			}
			$dw->bulkSet(array(
				'language_id' => '0',
				'title' => $phraseName,
				'phrase_text' => (string)$phrase
			));
			$dw->save();
		}

		XenForo_Db::commit($db);

		return;
	}

	public function appendPhrasesXml(DOMElement $rootNode, $blockId)
	{
		$document = $rootNode->ownerDocument;

		$phrases = $this->getPhrasesByBlock($blockId);
		foreach ($phrases AS $phrase)
		{
			$phraseNode = $document->createElement('phrase');
			$phraseNode->setAttribute('title', $phrase['title']);
			$phraseNode->appendChild($document->createCDATASection($phrase['phrase_text']));

			$rootNode->appendChild($phraseNode);
		}
	}
}