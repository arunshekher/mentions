<?php


class MentionsLinksResource extends Mentions
{
	protected $category;
	protected $data;
	protected $itemLink;


	public function set($category, $data)
	{
		$this->setCategory($category)->setData($data);

		return $this;
	}


	/**
	 * @param mixed $data
	 *
	 * @return \MentionsLinksResource
	 */
	public function setData($data)
	{
		$this->data = $data;

		return $this;
	}


	/**
	 * @param mixed $category
	 *
	 * @return MentionsLinksResource
	 */
	public function setCategory($category)
	{
		$this->category = $category;

		return $this;
	}


	/**
	 * Returns the content entity links for notification email.
	 *
	 * @return string
	 *  html markup for the content link
	 */
	public function get()
	{
		$this->log($this->data, 'from-resource-get-method');
		if (LAN_MENTIONS_TAG_CHATBOX === $this->category) {
			return $this->itemLink =
				SITEURLBASE . e_PLUGIN_ABS . 'chatbox_menu/chat.php';
		}

		if (LAN_MENTIONS_TAG_COMMENT === $this->category) {
			return $this->itemLink = $this->commentItemLink();
		}

		if (LAN_MENTIONS_TAG_FORUM === $this->category) {
			return $this->itemLink = $this->forumItemLink();
		}

		return '[unresolved]';
	}


	private function commentItemLink()
	{
	}


	private function forumItemLink()
	{
		$this->setMissingForumData();

		$data = $this->data;

		$data['thread_sef'] = $this->getThreadSlug();

		$opt = $this->getLinkOptions('forum');

		return e107::url('forum', 'topic', $data, $opt);
	}


	private function setMissingForumData()
	{
		array_merge($this->data, $this->getMissingForumData());
	}


	private function getMissingForumData()
	{
		$sql = e107::getDb();
		$thread_id = (int) $this->data['post_thread'];

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

		return $this->createSlug($this->data['thread_name']);

	}


	private function createSlug($title, $type = null)
	{
		$type = $type ?: e107::getPref('url_sef_translate');

		return eHelper::title2sef($title, $type);
	}


	/**
	 * Returns options for link creation based on core URL preference.
	 *
	 * @param string $type
	 *
	 * @return array
	 *  URL creation options array
	 */
	private function getLinkOptions($type)
	{

		$coreUrlPref = e107::getPref('e_url_list');

		switch ($type) {

			case 'news':
				return ['full' => true];
				break;

			case 'page':
				return ['full' => true];
				break;

			case 'downloads':
				if ($coreUrlPref['download']) {
					return [
						'mode'   => 'full',
						'legacy' => false,
					];
				}
				break;

			case 'forum':
				if ($coreUrlPref['forum']) {
					return [
						'mode'   => 'full',
						'query'  => ['last' => 1],
						'legacy' => false,
					];
				}
				break;
		}

		return ['mode' => 'full', 'legacy' => true];
	}

}
