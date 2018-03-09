<?php
if ( ! defined('e107_INIT')) {
	exit;
}
require_once __DIR__ . '/Mentions.php';

class MentionsParse extends Mentions
{

	/**
	 * Parses mentions in user submitted text
	 *
	 * @param string $text
	 *  The text string to be parsed.
	 * @return string
	 *  Parsed text string.
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
	 * @param string $input
	 *  String to be parsed for user mentions.
	 * @return bool
	 *  Returns with boolean 'false' if no match and the matched string if match.
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
	 * Determines if the context of current text is in plugins preferred context
	 * @param string $context
	 *  Context marker for current text
	 * @return bool
	 *  true if yes, false if not
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
	 * Gets admin chosen text context preference as workable solution
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
