<?php

e107::lan('mentions', 'front', true);

class Mentions
{

	protected $prefs;
	protected $parse;


	/**
	 * Mentions constructor.
	 */
	public function __construct()
	{
		$this->prefs = e107::getPlugPref('mentions');
		$this->parse = e107::getParser();
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

}
