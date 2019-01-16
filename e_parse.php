<?php

if ( ! defined('e107_INIT')) {
	exit;
}
require_once __DIR__ . '/MentionsParse.php';

class mentions_parse extends MentionsParse
{


	/**
	 * @param string $text
	 * @param string $context
	 *
	 * @return string
	 */
	public function toHtml($text, $context = '')
	{
		if ($this->prefs['mentions_active'] && $this->isInContextOf($context) && e_ADMIN_AREA !== true) {
			return $this->parseMentions($text);
		}

		return $text;
	}


}
