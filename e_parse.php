<?php

if ( ! defined('e107_INIT')) {
	exit;
}
require __DIR__ . '/MentionsParse.php';

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
		if ($this->prefs['mentions_active'] && $this->isInContext($context)) {
			return $this->parseMentions($text, $context);
		}

		return $text;
	}


}
