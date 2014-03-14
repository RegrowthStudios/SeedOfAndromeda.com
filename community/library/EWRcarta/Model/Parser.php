<?php

class EWRcarta_Model_Parser extends XenForo_Model
{
	public function parseSidebar($page = false)
	{
		$sidebar['index'] = $this->getModelFromCache('EWRcarta_Model_Lists')->getIndex();
		$sidebar['pages'] = $this->getModelFromCache('EWRcarta_Model_Lists')->getSideList();
		$sidebar['count'] = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageCount();
		$sidebar['edits'] = $this->getModelFromCache('EWRcarta_Model_History')->getHistoryCount();
		$sidebar['likes'] = $this->getLikeCount();
		$sidebar['views'] = $this->getViewCount();
		$sidebar['files'] = $this->getFileCount();

		if ($page)
		{
			$self = XenForo_Application::get('options')->EWRcarta_restricttree ? $page['page_id'] : 0;
			$sidebar['related'] = $this->getModelFromCache('EWRcarta_Model_Lists')->getRelated($page, $self);
		}
		else
		{
			$sidebar['related'] = false;
		}

		return $sidebar;
	}

	public function getLikeCount()
	{
        $count = $this->_getDb()->fetchRow("
			SELECT SUM(page_likes) AS total
				FROM EWRcarta_pages
		");

		return $count['total'];
	}

	public function getViewCount()
	{
        $count = $this->_getDb()->fetchRow("
			SELECT SUM(page_views) AS total
				FROM EWRcarta_pages
		");

		return $count['total'];
	}

	public function getFileCount()
	{
        $count = $this->_getDb()->fetchRow("
			SELECT COUNT(*) AS count, SUM(file_size) AS size
				FROM xf_attachment
				INNER JOIN xf_attachment_data ON (xf_attachment_data.data_id = xf_attachment.data_id)
			WHERE content_type = 'wiki'
				AND unassociated = '0'
		");

		return $count;
	}

	public function parseTemplates($page)
	{
		$templates = $this->fetchAllKeyed("
			SELECT *
				FROM EWRcarta_templates
		", 'template_name');

		$codePattern = '#\[template=([A-Za-z0-9\-]+)\](.*?)\[/template\]#si';
		$dataPattern = '#([\w\s]+)=(.+)#si';
		$parsPattern = '#\[if=(.+?)([!<>=]{1,3}?)([^!<>=]*?)\](.*?)\[/if\]#si';
		$elsePattern = '#(.*)\[else\s?/?\](.*)#si';

		if (preg_match_all($codePattern, $page['HTML'], $codeMatches))
		{
			foreach ($codeMatches[0] AS $key => $codeMatch)
			{
				$originalCode = $codeMatches[0][$key];
				$variableCode = $codeMatches[2][$key];
				$templateName = strtolower($codeMatches[1][$key]);

				if (!empty($templates[$templateName]))
				{
					$values = array();
					$matches = explode("|", $variableCode);
					$templateText = $templates[$templateName]['template_content'];

					foreach ($matches AS $match)
					{
						if (preg_match($dataPattern, $match, $dataMatch))
						{
							$values[trim($dataMatch[1])] = preg_replace('/<br \/>$/i', "", trim($dataMatch[2]));
						}
					}

					foreach ($values AS $key => $value)
					{
						$templateText = str_replace("{{{".$key."}}}", $value, $templateText);
					}

					if (preg_match_all($parsPattern, $templateText, $parsMatches))
					{
						foreach ($parsMatches[0] AS $key => $parsMatch)
						{
							$original = $parsMatches[0][$key];
							$condition = trim($parsMatches[2][$key]);
							$left = trim($parsMatches[1][$key]);
							$right = trim($parsMatches[3][$key]);
							$pass = trim($parsMatches[4][$key]);

							if (preg_match($elsePattern, $pass, $elseMatch))
							{
								$pass = trim($elseMatch[1]);
								$fail = trim($elseMatch[2]);
							}
							else
							{
								$fail = "";
							}

							switch ($condition)
							{
								case ">":		$replace = ((int)$left > (int)$right) ? $pass : $fail;		break;
								case "<":		$replace = ((int)$left < (int)$right) ? $pass : $fail;		break;
								case "=>":		$replace = ((int)$left >= (int)$right) ? $pass : $fail;		break;
								case "=<":		$replace = ((int)$left <= (int)$right) ? $pass : $fail;		break;
								case "==":		$replace = ((int)$left == (int)$right) ? $pass : $fail;		break;
								case "!=":		$replace = ((int)$left != (int)$right) ? $pass : $fail;		break;
								case "!==":		$replace = ($left !== $right) ? $pass : $fail;				break;
								case "===":		$replace = ($left === $right) ? $pass : $fail;				break;
								case "=!":		$replace = $left;											break;
								default:		$replace = "";
							}

							$templateText = str_replace($original, $replace, $templateText);	
						}
					}

					$page['HTML'] = str_replace($originalCode."<br />", $originalCode, $page['HTML']);	
					$page['HTML'] = str_replace($originalCode, $templateText, $page['HTML']);
				}
			}
		}

		return $page;
	}

	public function parseContents($page)
	{
		$options = XenForo_Application::get('options');
		$headPattern = '#<h(\d+)>(.*?)</h\d+>#i';

		if ($options->EWRcarta_tocreq && $count = preg_match_all($headPattern, $page['HTML'], $headMatches))
		{
			if ($count < $options->EWRcarta_tocreq) { return $page; }

			$contents = '';

			foreach ($headMatches[0] AS $key => $headMatch)
			{
				$headCode = $headMatches[0][$key];
				$headType = $headMatches[1][$key];
				$headName = $headMatches[2][$key];

				$headSlug = strtolower(trim($headName));
				$headSlug = preg_replace('#[^-a-z0-9\s]#', '-', $headSlug);
				$headSlug = preg_replace('#^[-\s]+|[-\s]+$#', '', $headSlug);
				$headSlug = preg_replace('#[-\s]+#', '-', $headSlug);

				$pageLink = XenForo_Link::buildPublicLink('wiki', $page);
				$anchLink = $options->EWRcarta_tocnumber ? ($key+1).'-' : '';
				$headLink = '<h'.$headType.'><a name="'.$anchLink.$headSlug.'"></a>'.$headName.'<span class="gototop">(<a href="'.$pageLink.'#wikiPage">top</a>)</span></h'.$headType.'>';

				$contents .= '<li class="col'.$headType.'"><a href="'.$pageLink.'#'.$anchLink.$headSlug.'">'.$headMatches[2][$key].'</a></li>';

				$page['HTML'] = str_replace($headCode, $headLink, $page['HTML']);
			}

			$contents = '<div class="tableContents"><h2>Contents</h2><ul>'.$contents.'</ul></div><br />';

			if (stripos($page['HTML'], '[TOC]') !== false)
			{
				$page['HTML'] = str_ireplace('[TOC]', $contents, $page['HTML']);
			}
			else
			{
				$page['HTML'] = preg_replace('#(<h(\d+)>)#i', $contents.'$1', $page['HTML'], 1);
			}
		}

		return $page;
	}

	public function parseAutolinks($page)
	{
		$options = XenForo_Application::get('options');

		if ($options->EWRcarta_autolink)
		{
			$pages = $this->_getDb()->fetchAll("
				SELECT *
					FROM EWRcarta_pages
				WHERE page_name != ?
				ORDER BY LENGTH(page_name) DESC
			", $page['page_name']);

			foreach ($pages AS $link)
			{
				$noMatch = '(</a>|</h\d+>|</b>)';

				$linkUrl = XenForo_Link::buildPublicLink('wiki', $link);
				$linkPin = '#(?!(?:[^<]+>|[^>]+'.$noMatch.'))\b'.preg_quote($link['page_name'], '#').'\b#i';
				$linkRpl = '<a href="'.$linkUrl.'">'.$link['page_name'].'</a>';

				$page['HTML'] = preg_replace($linkPin, $linkRpl, $page['HTML'], $options->EWRcarta_autolink);
			}
		}

		return $page;
	}

	public function parsePagePHP($page)
	{
		$page['page_file'] = explode("\n", $page['page_content']);
		$fileName = $page['page_file'][0];
		$extension = explode('.', $fileName);

		if (file_exists($fileName) && end($extension) == "php")
		{
			ob_start();
			include_once($page['page_file'][0]);
			$page['HTML'] = ob_get_contents();
			ob_end_clean();
		}
		else
		{
			$page['HTML'] = 'Could not find PHP file: '.htmlspecialchars($page['page_file'][0]);
		}

		return $page;
	}
}