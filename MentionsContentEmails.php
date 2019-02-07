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
	private $data;

	private $title;
	private $type;
	private $link;
	private $date;


	/**
	 * CommentEmail constructor.
	 *
	 * @param $data
	 */
	public function __construct($data)
	{
		$this->setData($data)->setType($data['comment_type'])->setLink()
			->setTitle($data['comment_subject'])
			->setDate($data['comment_datestamp']);
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
	public function setLink()
	{
		$link = new ContentLinksFactory('comment', $this->data);

		$this->link = $link->generate();

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
			'-type-'  => $this->type, // todo: the verbose form of comment type
			'-title-' => $this->title,
			'-link-'  => $this->getLink(),
		];
	}


	private function getLink()
	{
		return '<a href="' . $this->link . '">\'' . $this->title . '\'</a>';
	}

}


class ForumEmail
{

}