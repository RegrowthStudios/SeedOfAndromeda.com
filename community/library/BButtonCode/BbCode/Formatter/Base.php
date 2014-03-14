<?php

class BButtonCode_BbCode_Formatter_Base extends XFCP_BButtonCode_BbCode_Formatter_Base
{
	protected $_tags;

	public function getTags()
	{
		$this->_tags = parent::getTags();
		$this->_tags['button'] = array(
			'plainChildren' => true,
			'callback' => array($this, 'renderTagButton')
		);
		return $this->_tags;
	}

	public function renderTagButton(array $tag, array $rendererStates)
	{
		$src = $tag['children'][0];

		$burl = '';
		if (preg_match("#\[url\](.*?)\[\/url\]#", $src, $matches));
		{
			if (!empty($matches[1]))
			{
				$burl = $matches[1];
			}
		}

		$bbground = '';
		if (preg_match("#bgcolor=\'(.*?)\'#", $tag['option'], $matches));
		{
			if (!empty($matches[1]))
			{
				$bbground = $matches[1];
			}
		}

		$bsize = '';
		if (preg_match("#size=\'(.*?)\'#", $tag['option'], $matches));
		{
			if (!empty($matches[1]))
			{
				$bsize = $matches[1];
			}
		}

		$bmargin = '';
		if (preg_match("#margin=\'(.*?)\'#", $tag['option'], $matches));
		{
			if (!empty($matches[1]))
			{
				$bmargin = $matches[1];
			}
		}

		$btext = '';
		if (preg_match("#text=\'(.*?)\'#", $tag['option'], $matches));
		{
			if (!empty($matches[1]))
			{
				$btext = $matches[1];
			}
		}

		$bdesc = '';
		if (preg_match("#desc=\'(.*?)\'#", $tag['option'], $matches));
		{
			if (!empty($matches[1]))
			{
				$bdesc = $matches[1];
			}
		}

		$btitle = '';
		if (preg_match("#title=\'(.*?)\'#", $tag['option'], $matches));
		{
			if (!empty($matches[1]))
			{
				$btitle = $matches[1];
			}
		}

		$bwidth = '';
		if (preg_match("#width=\'(.*?)\'#", $tag['option'], $matches));
		{
			if (!empty($matches[1]))
			{
				$bwidth = $matches[1];
			}
		}

		$btcolor = '';
		if (preg_match("#textcolor=\'(.*?)\'#", $tag['option'], $matches));
		{
			if (!empty($matches[1]))
			{
				$btcolor = $matches[1];
			}
		}

		$bstyle = '';
		if (preg_match("#style=\'(.*?)\'#", $tag['option'], $matches));
		{
			if (!empty($matches[1]))
			{
				$bstyle = $matches[1];
			}
		}

		$bnew = '';
		if (preg_match("#new=\'(.*?)\'#", $tag['option'], $matches));
		{
			if (!empty($matches[1]))
			{
				$bnew = $matches[1];
			}
		}

		if($bnew == 'yes')
		{
			$bnew = '_blank';
		}
		else
		{
			$bnew = '_self';
		}

		$bnofollow = '';
		if (preg_match("#nofollow=\'(.*?)\'#", $tag['option'], $matches));
		{
			if (!empty($matches[1]))
			{
				$bnofollow = $matches[1];
			}
		}

		if($bnofollow == 'yes')
		{
			$bnofollow = 'nofollow';
		}
		else
		{
			$bnofollow = 'follow';
		}

		return  '<a class="bbutton ' . $bsize . ' ' . $bstyle . ' ' . $bmargin . '" href="' . $burl . '" style="background-color: ' . $bbground . '; color: ' . $btcolor . '; width: ' . $bwidth . '; " target="' . $bnew . '" title="' . $btitle . '" rel="' . $bnofollow . '">
                            <span class="bbuttontitle">' . $btext . '</span>
                            <span class="bbuttonsubtitle">' . $bdesc . '</span>
                       </a>';
	}
}