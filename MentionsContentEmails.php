<?php


class ContentEmailsFactory
{
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
		$this->setId($id)->setData($data);
	}


	/**
	 * @param mixed $data
	 *
	 * @return ContentEmailsFactory
	 */
	public function setData($data)
	{
		$this->data = $data;

		return $this;
	}


	/**
	 * @param mixed $id
	 *
	 * @return ContentEmailsFactory
	 */
	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}


	public function generate()
	{
		$className = ucfirst($this->id) . 'Email';
		$emailObject = new $className($this->data);
		$emailObject->generateEmailText();
	}

}


class ChatboxEmail
{

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




	/**
	 * CommentEmail constructor.
	 *
	 * @param $data
	 */
	public function __construct($data)
	{
		$link = new ContentLinksFactory('comment', $data);

		$this->setData($data)
			->setType($data['comment_type'])
			->setTitle($data['comment_subject'])
			->setDate($data['comment_datestamp'])
			->setTag($this->fetchTag())
			->setTypeWord($this->getTypeWord())
			->setLink($link->generate());
	}


	/**
	 * @param mixed $date
	 *
	 * @return CommentEmail
	 */
	public function setDate($date)
	{
		$this->date = $date;

		return $this;
	}


	/**
	 * @param mixed $title
	 *
	 * @return CommentEmail
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}


	/**
	 * @param mixed $link
	 *
	 * @return CommentEmail
	 */
	public function setLink($link)
	{
		$this->link = $link;

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
	 * @param mixed $data
	 *
	 * @return CommentEmail
	 */
	public function setData($data)
	{
		$this->data = $data;

		return $this;
	}


	/**
	 * @param mixed $tag
	 *
	 * @return CommentEmail
	 */
	public function setTag($tag)
	{
		$this->tag = $tag;

		return $this;
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


	public function generateEmailText()
	{
		$tp = e107::getParser();
		$vars = $this->getVars();

		return $tp->lanVars(LAN_PLUGIN_MENTIONS_EMAIL_TEXT_COMMENT, $vars);
	}


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


	private function getLink()
	{
		return '<a href="' . $this->link . '">\'' . $this->title . '\'</a>';
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

		return 'Unknown';
	}


	/**
	 * Returns word for comment
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

}


class ForumEmail
{

}