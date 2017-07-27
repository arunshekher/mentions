<?php

if ( ! defined('e107_INIT')) {
	exit;
}
require __DIR__ . '/Mentions.php';

class mentions_parse extends Mentions
{


	/**
	 * @param string $text
	 * @param string $context
	 *
	 * @return string
	 */
	public function toHtml($text, $context = '')
	{
		if ($this->prefs['mentions_active'] && $this->isInContextOf($context)) {
			return $this->parseMentions($text, $context);
		}

		return $text;
	}


}
