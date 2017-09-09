<?php

e107::lan('mentions', 'front', true);

class Mentions
{

	protected $prefs;


	/**
	 * Mentions constructor.
	 */
	public function __construct()
	{
		$this->prefs = e107::getPlugPref('mentions');
	}


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
	 * Converts mention to user profile link if user matched exists in database
	 *
	 * @param $mention
	 *
	 * @return string
	 */
	protected function createUserLinkFrom($mention)
	{
		$data = $this->getUserData($mention);

		if ($data['user_name'] === $this->stripAtFrom($mention)) {
			$userData =
				['id' => $data['user_id'], 'name' => $data['user_name']];
			$link = e107::getUrl()->create('user/profile/view', $userData);

			return '<a href="' . $link . '">' . $mention . '</a>';
		}

		return $mention;
	}


	/**
	 * Get user data drom database
	 *
	 * @param $mention
	 *
	 * @return array
	 */
	protected function getUserData($mention)
	{
		$username = e107::getParser()->toDB($this->stripAtFrom($mention));
		$row = e107::getDb()->retrieve("user", "user_name, user_id, user_email",
			"user_name = '" . $username . "' ");

		return $row;
	}


	/**
	 * Strips '@' sign from mention
	 *
	 * @param $mention
	 *
	 * @return string
	 */
	protected function stripAtFrom($mention)
	{
		return ltrim($mention, '@');
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
