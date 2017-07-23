<?php


/**
 * Class MentionsPlugin
 */
abstract class MentionsPlugin
{
	/**
	 * @var mixed
	 */
	protected $mentionsPrefs;

	/**
	 * MentionsPlugin constructor.
	 *
	 */
	public function __construct()
	{
		$this->mentionsPrefs
			= $this->prefs();
	}


	protected function prefs()
	{
		return e107::getPlugPref('mentions');
	}

}