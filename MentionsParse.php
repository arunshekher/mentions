<?php
require_once __DIR__ . '/Mentions.php';


class MentionsParse extends Mentions
{

	/**
	 * Parse mentions in user submitted text
	 *
	 * @param $text
	 *
	 * @return string
	 */
	protected function parseMentions($text, $context = '')
	{
		$mText = '';
		$pattern = '#(^|\w*@\s*[a-z0-9._]+)#mi';
		$phrases = preg_split($pattern, $text, -1,
			PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

		foreach ($phrases as $phrase) {
			$mention = $this->hasUserMentionIn($phrase);
			if ($mention) {
				$mText .= $this->createUserLinkFrom($mention);
			} else {
				$mText .= $phrase;
			}
		}

		return $mText;
	}


	/**
	 * Checks input for user mention match and return that if found
	 *
	 * @param $input
	 *
	 * @return bool
	 */
	protected function hasUserMentionIn($input)
	{
		$pattern = '#^(@[a-z0-9_.]*)$#i';
		if (preg_match($pattern, $input, $matches)) {
			return $matches[0];
		}

		return false;
	}

	/**
	 * @param $context
	 *
	 * @return bool
	 */
	protected function isInContextOf($context)
	{
		$ctxArray = $this->chosenContexts();
		if ((array) $ctxArray !== $ctxArray && null === $ctxArray) {
			return true;
		}
		foreach ($ctxArray as $ctxItem) {
			if ($ctxItem === $context) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Gets admin chosen contexts as indexed array
	 *
	 * @return array|null
	 */
	protected function chosenContexts()
	{
		$contextPref = $this->prefs['mentions_contexts'];

		switch ($contextPref) {
			case 1:
				return ['USER_BODY'];
				break;
			case 2:
				return ['USER_BODY', 'OLDDEFAULT'];
				break;
			case 3:
				return ['USER_BODY', 'BODY', 'OLDDEFAULT'];
				break;
			default:
				return null;
				break;
		}
	}

}