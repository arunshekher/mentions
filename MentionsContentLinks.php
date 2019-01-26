<?php

class ContentLinksFactory
{
	private $id;
	private $data;


	/**
	 * ContentLinksFactory constructor.
	 *
	 * @param $id
	 * @param $data
	 */
	public function __construct($id, $data)
	{
		$this->id = $id;
		$this->data = $data;
	}


	public function create()
	{
		if ($this->id === 'forum')
		{
			$forum = new ForumLinks($this->data);
			return $forum->createLink();
		}

		return null;
	}
}

class ChatboxLinks
{

}

class CommentLinks
{

}

class ForumLinks
{

	private $forumData;


	/**
	 * ForumLinks constructor.
	 *
	 * @param $forumData
	 */
	public function __construct($forumData)
	{
		$this->setForumData($forumData)->setMissingForumData();
	}


	/**
	 * @param mixed $forumData
	 *
	 * @return ForumLinks
	 */
	public function setForumData($forumData)
	{
		$this->forumData = $forumData;

		return $this;
	}


	/**
	 * @return $this
	 */
	private function setMissingForumData()
	{
		if (is_array($this->forumData)) {

			array_merge($this->forumData, $this->getMissingForumData());

			// todo: create thread_sef here link so:
			// $this->forumData['thread_sef'] = $this->getThreadSlug();
		}
		return $this;
	}


	public function createLink()
	{
		return e107::url('forum', 'topic', $this->forumData, $this->getLinkOptions());
	}


	/**
	 * @return string
	 * @deprecated - use ForumLinks::createLink()
	 */
	private function createForumItemLink()
	{

		$data = $this->forumData;

		$data['thread_sef'] = $this->getThreadSlug();

		$opt = $this->getLinkOptions();

		return e107::url('forum', 'topic', $data, $opt);
	}


	private function createSlug($title, $type = null)
	{
		$type = $type ?: e107::getPref('url_sef_translate');

		return eHelper::title2sef($title, $type);
	}


	/**
	 * Returns missing forum data from `#forum` and `#forum_thread` tables
	 *
	 * @return array|bool|null
	 */
	private function getMissingForumData()
	{
		$sql = e107::getDb();

		$thread_id = (int) $this->forumData['post_thread'];

		$query = "SELECT f.forum_sef, f.forum_id, ft.thread_id, ft.thread_name 
					FROM `#forum` AS f 
						LEFT JOIN `#forum_thread` AS ft ON f.forum_id = ft.thread_forum_id 
							WHERE ft.thread_id = {$thread_id} ";

		$result = $sql->gen($query);

		if ($result) {
			return $sql->fetch($result);
		}

		return null;
	}


	private function getThreadSlug()
	{

		return $this->createSlug($this->forumData['thread_name']);

	}


	/**
	 * Returns forum link options
	 *
	 * @return array
	 */
	private function getLinkOptions()
	{
		$urlPref = e107::getPref('e_url_list');

		if ($urlPref['forum']) {
			return ['mode'   => 'full', 'legacy' => false, 'query'  => ['last' => 1]];
		}
		return ['mode' => 'full', 'legacy' => true];
	}


}