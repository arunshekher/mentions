<?php


trait Sluggable
{
	private function sluggify($title, $type = null)
	{
		$type = $type ?: e107::getPref('url_sef_translate');

		return eHelper::title2sef($title, $type);
	}

}


class ContentLinksFactory
{
	private $instance;

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
		$this->setId($id)->setData($data)->setInstance($this->createInstance());

	}


	/**
	 * @param mixed $instance
	 *
	 * @return ContentLinksFactory
	 */
	public function setInstance($instance)
	{

		$this->instance = $instance;

		return $this;
	}


	/**
	 * @param mixed $data
	 *
	 * @return ContentLinksFactory
	 */
	public function setData($data)
	{
		$this->data = $data;

		return $this;
	}


	/**
	 * @param mixed $id
	 *
	 * @return ContentLinksFactory
	 */
	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}


	/**
	 * @return mixed
	 */
	private function createInstance()
	{
		$className = ucfirst($this->id) . 'Links';

		return new $className($this->data);
	}


	/**
	 * Generates requested content link
	 *
	 * @return string
	 */
	public function generate()
	{
		return $this->instance->createLink();
	}


	/**
	 * Returns requested content link
	 *
	 * @return string
	 */
	public function link()
	{
		return $this->instance->createLink();
	}


	/**
	 * Returns title (@todo: only in case of ForumLinks class)
	 *
	 * @return mixed
	 */
	public function title()
	{
		if ($this->instance instanceof ForumLinks) {
			return $this->instance->getTitle();
		}

		return null;
	}

}


/**
 * Class ChatboxLinks
 */
class ChatboxLinks
{
	private $data;


	/**
	 * ChatboxLinks constructor.
	 *
	 * @param $data
	 */
	public function __construct($data)
	{
		$this->setData($data);
	}


	/**
	 * @param mixed $data
	 *
	 * @return ChatboxLinks
	 */
	public function setData($data)
	{
		$this->data = $data;

		return $this;
	}


	public function createLink()
	{
		return SITEURLBASE . e_PLUGIN_ABS . 'chatbox_menu/chat.php';
	}


}


/**
 * Class CommentLinks
 */
class CommentLinks
{

	use Sluggable;

	/**
	 * @var link generated link
	 */
	public $link;
	/**
	 * @var linkConfig url configuration
	 */
	private $linkConfig;

	private $data;
	private $type;


	/**
	 * CommentLinks constructor.
	 *
	 * @param $commentData
	 */
	public function __construct($data)
	{
		$this->setData($data)->setType((int)$data['comment_type'])
			->setLinkConfig($this->getLinkConfig());
	}


	/**
	 * Sets link configuration
	 *
	 * @param mixed $linkConfig
	 *
	 * @return CommentLinks
	 */
	public function setLinkConfig($linkConfig)
	{
		$this->linkConfig = $linkConfig;

		return $this;
	}


	/**
	 * Sets type
	 *
	 * @param mixed $type
	 *
	 * @return CommentLinks
	 */
	protected function setType($type)
	{
		$this->type = $type;

		return $this;
	}


	/**
	 * Sets data
	 *
	 * @param mixed $data
	 *
	 * @return CommentLinks
	 */
	protected function setData($data)
	{
		$this->data = $data;

		return $this;
	}


	/**
	 * Returns options for link creation based on core URL preference.
	 *
	 * @param string $type
	 *
	 * @return array
	 *  URL creation options array
	 * @todo for URL configuration types, preference 'url_config' should be
	 *     considered rather than 'e_url_list'
	 */
	private function getLinkConfig()
	{
		$type = $this->type;

		$urlPref = e107::getPref('e_url_list');

		if ($type === 0) {
			return ['full' => true];
		}

		if ($type === 'page') {
			return ['full' => true];
		}

		if ($type === 2 && $urlPref['download']) {
			return ['mode' => 'full', 'legacy' => false];
		}

		if ($type === 'profile') {
			return ['full' => true];
		}

		return ['mode' => 'full', 'full' => true, 'legacy' => true];
	}


	/**
	 * Create comment link
	 *
	 * @return string|null
	 */
	public function createLink()
	{
		$config = $this->linkConfig;

		if ($this->type === 0) {
			/** @var  $type : news */

			$urlData = [
				'news_id'  => $this->data['comment_item_id'],
				'news_sef' => $this->sluggify($this->data['comment_subject']),
			];

			return e107::getUrl()->create('news/view/item', $urlData, $config);
		}

		if ($this->type === 2) {
			/** @var  $type : download */

			$urlData = [
				'download_id'  => $this->data['comment_item_id'],
				'download_sef' => $this->sluggify($this->data['comment_subject']),
			];

			return e107::url('download', 'item', $urlData, $config);
		}

		if ($this->type === 4) {
			/** @var  $type : poll */

			return SITEURLBASE . e_PLUGIN_ABS . 'poll/oldpolls.php?' . $this->data['comment_item_id'];
		}

		if ($this->type === 'page') {

			$urlData = [
				'page_id'    => $this->data['comment_item_id'],
				'page_title' => $this->data['comment_subject'],
				'page_sef'   => $this->sluggify($this->data['comment_subject']),
			];

			return e107::getUrl()->create('page/view', $urlData, $config);
		}

		if ($this->type === 'profile') {

			$urlData = [
				'id'   => $this->data['comment_item_id'],
				'name' => $this->data['comment_subject'],
			];

			return e107::getUrl()
				->create('user/profile/view', $urlData, $config);
		}

		if ($this->type === null) {
			return SITEURLBASE;
		}

		return null;
	}


}


/**
 * Class ForumLinks
 */
class ForumLinks
{
	use Sluggable;

	private $data;

	private $title;


	/**
	 * ForumLinks constructor.
	 *
	 * @param $data
	 */
	public function __construct($data)
	{
		$this->setData($data)->setMissingForumData()
			->setTitle($this->data['thread_name']);
	}


	/**
	 * @param mixed $title
	 *
	 * @return ForumLinks
	 */
	private function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}


	/**
	 * Sets missing data for link generation
	 *
	 * @return $this
	 */
	private function setMissingForumData()
	{
		if (is_array($this->data)) {

			$this->data =
				array_merge($this->data, $this->fetchMissingForumData());

			// create thread_sef
			$this->data['thread_sef'] = $this->getThreadSlug();
		}

		return $this;
	}


	/**
	 * Fetches missing forum data from `#forum` and `#forum_thread` tables
	 *
	 * @return array|bool|null
	 */
	private function fetchMissingForumData()
	{
		$sql = e107::getDb();

		$thread_id = (int)$this->data['post_thread'];

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


	/**
	 * Returns thread_name slug
	 *
	 * @return mixed|string
	 */
	private function getThreadSlug()
	{
		return $this->sluggify($this->data['thread_name']);

	}


	/**
	 * Sets forum data
	 *
	 * @param mixed $data
	 *
	 * @return ForumLinks
	 */
	private function setData($data)
	{
		$this->data = $data;

		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}


	/**
	 * Creates forum link
	 *
	 * @return string
	 */
	public function createLink()
	{
		return e107::url('forum', 'topic', $this->data, $this->getLinkConfig());
	}


	/**
	 * Returns forum link configuration
	 *
	 * @return array
	 */
	private function getLinkConfig()
	{
		$urlConfig = e107::findPref('e_url_list/forum');

		if ($urlConfig) {

			return [
				'mode'   => 'full',
				'legacy' => false,
				'query'  => ['last' => 1],
			];
		}

		return ['mode' => 'full', 'legacy' => true];
	}


}
