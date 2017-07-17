<?php


class Mentions
{
	protected $userMention;
	protected $userName;
	protected $userId;


	/**
	 * mention_parse constructor.
	 */
	public function __construct()
	{
	}


	/**
	 * @param $text
	 *
	 * @return string
	 */
	protected function parseMentions($text)
	{
		$mText = '';
		$pattern = '#(@\w+)#mis';
		$pieces = preg_split($pattern, $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

		foreach ($pieces as $piece) {
			if ($this->isUserName($piece)) {
				$mText .= $this->makeUserLink($piece);
			} else {
				$mText .= $piece;
			}
		}
		return $mText;
	}


	/**
	 * @param $input
	 *
	 * @return bool
	 * TODO - rename as hasUserName
	 */
	protected function isUserName($input)
	{
		$pattern = '/(^|\s)(@\w+)/';
		if (preg_match($pattern, $input, $matches)) {
			return $matches[0];
		}
		return false;
	}


	/**
	 * @param $mention
	 *
	 * @return string
	 * TODO - if user exist get userid from username and use it for link
	 */
	protected function makeUserLink($mention)
	{
		//preg_replace('/@([^@ ]+)/', '<a href="/$1">@$1</a> ', $comment);
		$data = $this->getUserData($mention);

		if ($data['user_name'] === ltrim($mention, '@')) {
			return '<a href="/user.php?id.' . $data['user_id'] . '">' . $mention . '</a>';
		}
		return $mention;

	}


	protected function getUserData($mention)
	{
		$username = e107::getParser()->toDB(ltrim($mention, '@'));
		// TODO - DB call
		$row = e107::getDb()->retrieve("user", "user_name, user_id", "user_name = '" . $username . "' ");
		return $row;
	}


	protected function extractUserName($input)
	{

	}
}