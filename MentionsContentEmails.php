<?php


trait FamilialTraits
{
	/**
	 * Sets Link
	 *
	 * @param mixed $link
	 *
	 * @return $this
	 */
	public function setLink($link)
	{
		$this->link = $link;

		return $this;
	}


	/**
	 * Sets Date
	 *
	 * @param mixed $date
	 *
	 * @return $this
	 */
	public function setDate($date)
	{
		$this->date = $date;

		return $this;
	}


	/**
	 * Sets Tag
	 *
	 * @param mixed $tag
	 *
	 * @return $this
	 */
	public function setTag($tag)
	{
		$this->tag = $tag;

		return $this;
	}


	/**
	 * Sets Data
	 *
	 * @param mixed $data
	 *
	 * @return $this
	 */
	public function setData($data)
	{
		$this->data = $data;

		return $this;
	}


	/**
	 * Sets Title
	 *
	 * @param mixed $title
	 *
	 * @return $this
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}


	/**
	 * Formats and returns date and time from unix timestamp.
	 *
	 * @param        $date
	 * @param string $format
	 *
	 * @return string
	 */
	protected function prepareDate($date, $format = 'long')
	{
		return e107::getParser()->toDate($date, $format);
	}


}


class ContentEmailsFactory
{
	private $instance;

	private $id;
	private $data;


	/**
	 * ContentEmailsFactory constructor.
	 *
	 * @param $id
	 * @param $data
	 */
	public function __construct($id, $data)
	{
		$this->setId($id)->setData($data)->setInstance($this->createInstance());
	}


	/**
	 * @param mixed $instance
	 *
	 * @return ContentEmailsFactory
	 */
	private function setInstance($instance)
	{
		$this->instance = $instance;

		return $this;
	}


	/**
	 * @param mixed $data
	 *
	 * @return ContentEmailsFactory
	 */
	private function setData($data)
	{
		$this->data = $data;

		return $this;
	}


	/**
	 * @param mixed $id
	 *
	 * @return ContentEmailsFactory
	 */
	private function setId($id)
	{
		$this->id = $id;

		return $this;
	}


	/**
	 * Creates instance of the requested class
	 *
	 * @return mixed
	 */
	private function createInstance()
	{
		$className = ucfirst($this->id) . 'Email';

		return new $className($this->data);
	}


	/**
	 * Returns generated email text
	 *
	 * @return string
	 */
	public function generate()
	{
		return $this->instance->getEmailText();
	}


	/**
	 * Returns generated email text
	 *
	 * @return string
	 */
	public function email()
	{
		return $this->instance->getEmailText();
	}

}


class ChatboxEmail
{
	private $tag;
	private $data;
	private $message;
	private $link;
	private $date;


	use FamilialTraits;


	/**
	 * ChatboxEmail constructor.
	 *
	 * @param $data
	 */
	public function __construct($data)
	{
		$chat = new ContentLinksFactory('chatbox', $data);

		$this->setData($data)->setTag($this->fetchTag())
			->setDate($this->prepareDate($data['datestamp']))
			->setLink($chat->link());
	}


	/**
	 * Returns word translation for content tag
	 *
	 * @return string
	 */
	private function fetchTag()
	{
		return LAN_MENTIONS_TAG_CHATBOX;
	}


	/**
	 * @param mixed $message
	 *
	 * @return ChatboxEmail
	 */
	public function setMessage($message)
	{
		$this->message = $message;

		return $this;
	}


	/**
	 * Generates and returns variable substituted e-mail LAN text
	 *
	 * @return string
	 */
	public function getEmailText()
	{
		$tp = e107::getParser();
		$vars = $this->getVars();

		return $tp->lanVars(LAN_PLUGIN_MENTIONS_EMAIL_TEXT_CHATBOX, $vars);
	}


	/**
	 * Returns array of variable values for variable substituted LAN generation.
	 *
	 * @return array
	 */
	private function getVars()
	{
		return [
			'-tag-'  => $this->tag,
			'-date-' => $this->date,
			'-link-' => $this->getLink(),
		];
	}


	/**
	 * @return string
	 */
	private function getLink()
	{
		return '<a href="' . $this->link . '">\'' . $this->tag . '\'</a>';
	}
}


class CommentEmail
{
	private $tag; // todo: make it assign to a core LAN if available
	private $data;

	private $title;
	private $type;
	private $typeWord;
	private $link;
	private $date;

	use FamilialTraits;


	/**
	 * CommentEmail constructor.
	 *
	 * @param $data
	 */
	public function __construct($data)
	{
		$link = new ContentLinksFactory('comment', $data);

		$this->setData($data)->setType($data['comment_type'])
			->setTitle($data['comment_subject'])
			->setDate($this->prepareDate($data['comment_datestamp']))
			->setTag($this->fetchTag())->setTypeWord($this->getTypeWord())
			->setLink($link->generate());
	}


	/**
	 * @param $typeWord
	 *
	 * @return CommentEmail
	 */
	public function setTypeWord($typeWord)
	{
		$this->typeWord = $typeWord;

		return $this;
	}


	/**
	 * @param mixed $type
	 *
	 * @return CommentEmail
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}


	/**
	 * Returns word translation for content tag
	 *
	 * @return string
	 */
	private function fetchTag()
	{
		if (defined(COMLAN_8)) {
			return strtolower(COMLAN_8);
		}

		return LAN_MENTIONS_TAG_COMMENT;
	}


	/**
	 * Returns word for comment type (uses core LAN)
	 *
	 * @return string
	 */
	private function getTypeWord()
	{

		if (0 === (int)$this->type) {
			return COMLAN_TYPE_1;
		}

		if (2 === (int)$this->type) {
			return COMLAN_TYPE_2;
		}

		if (4 === (int)$this->type) {
			return COMLAN_TYPE_4;
		}

		if ('page' === $this->type) {
			return COMLAN_TYPE_PAGE;
		}

		if ('profile' === $this->type) {
			return COMLAN_TYPE_8;
		}

		return 'unknown';
	}


	/**
	 * Generates and returns e-mail text
	 *
	 * @return string
	 */
	public function getEmailText()
	{
		$tp = e107::getParser();
		$vars = $this->getVars();

		return $tp->lanVars(LAN_PLUGIN_MENTIONS_EMAIL_TEXT_COMMENT, $vars);
	}


	/**
	 * @return array
	 */
	private function getVars()
	{
		return [
			'-tag-'   => $this->tag, // todo: the i18n form of TAG
			'-date-'  => $this->date,
			'-type-'  => $this->typeWord,
			'-title-' => $this->title,
			'-link-'  => $this->getLink(),
		];
	}


	/**
	 * @return string
	 */
	private function getLink()
	{
		return '<a href="' . $this->link . '">\'' . $this->title . '\'</a>';
	}

}


class ForumEmail
{
	private $tag;
	private $data;

	private $title;
	private $link;
	private $date;

	use FamilialTraits;


	/**
	 * ForumEmail constructor.
	 *
	 * @param $data
	 */
	public function __construct($data)
	{
		$forum = new ContentLinksFactory('forum', $data);

		$this->setTitle($forum->title())->setData($data)
			->setTag($this->fetchTag())->setLink($forum->link())
			->setDate($this->prepareDate($data['post_datestamp']));
	}


	/**
	 * Returns word translation for content tag
	 *
	 * @return string
	 */
	private function fetchTag()
	{
		if (defined(ONLINE_EL13)) {
			return strtolower(ONLINE_EL13);
		}

		return LAN_MENTIONS_TAG_FORUM;
	}


	/**
	 * Generates and returns e-mail text
	 *
	 * @return string
	 */
	public function getEmailText()
	{
		$tp = e107::getParser();
		$vars = $this->getVars();

		return $tp->lanVars(LAN_PLUGIN_MENTIONS_EMAIL_TEXT_FORUM, $vars);
	}


	/**
	 * @return array
	 */
	private function getVars()
	{
		return [
			'-tag-'   => $this->tag,
			'-date-'  => $this->date,
			'-title-' => $this->title,
			'-link-'  => $this->getLink(),
		];
	}


	/**
	 * @return string
	 */
	private function getLink()
	{
		return '<a href="' . $this->link . '">\'' . $this->title . '\'</a>';
	}


}