<?php
if ( ! defined('e107_INIT')) {
	exit;
}

class MentionsNotification extends Mentions
{

	private $mentioneeData;
	private $mentions;
	private $mentioner;
	private $date;

	private $tag;

	private $title;
	private $link;
	private $commentType;

	private $mail;
	private $eventData;


	/**
	 * MentionsNotification constructor.
	 *
	 */
	public function __construct()
	{
		Mentions::__construct();
		$this->mail = e107::getEmail();
	}



	/**
	 * Sets the properties for MentionsNotification object.
	 *
	 * @param array $properties
	 */
	private function setProperties(array $properties)
	{
		foreach ($properties as $name => $value) {
			if (null !== $value) {
				$this->$name = $value;
			}
		}
	}


	/**
	 * Sets tag - an identifier that helps the ensuing member methods differentiate
	 *  - between what they are dealing with - 'forum', 'chatbox' or 'comment'
	 * @param mixed $tag
	 */
	public function setTag($tag)
	{
		$this->tag = $tag;
	}



	/**
	 * Sets eventData
	 * @param mixed $eventData
	 * @param bool  $appendFlag
	 */
	public function setEventData($eventData, $appendFlag = false)
	{
		if ( ! $appendFlag) {
			$this->eventData = $eventData;
		} else {
			$this->eventData = array_merge($this->eventData, $eventData);
		}

	}


	/**
	 * Sets comment type
	 * @param mixed $commentType
	 */
	public function setCommentType($commentType)
	{
		$this->commentType = $commentType;
	}




	/**
	 * Public static alias for MentionsNotification::perform()
	 *
	 */
	public static function execute()
	{
		$notification = new MentionsNotification;
		$notification->perform();
	}


	/**
	 * Listens to 'user_chatbox_post_created', 'user_comment_posted'
	 *  - and 'user_forum_post_created' event triggers and performs
	 *  - mentions notification by e-mail.
	 *
	 */
	private function perform()
	{
		if (USER && $this->prefs['mentions_active']
			&& (strtolower($_SERVER['REQUEST_METHOD']) === 'post'
				|| e_AJAX_REQUEST)) {

			if ($this->prefs['notify_chatbox_mentions']) {
				e107::getEvent()->register('user_chatbox_post_created',
					['MentionsNotification', 'chatbox']);
			}

			if ($this->prefs['notify_comment_mentions']
				&& ($this->prefs['mentions_contexts'] === 2
					|| $this->prefs['mentions_contexts'] === 3)) {
				e107::getEvent()->register('user_comment_posted',
					['MentionsNotification', 'comment']);
			}

			if ($this->prefs['notify_forum_mentions']) {

				// forum 'thread' and 'reply' covered - for now
				e107::getEvent()->register('user_forum_post_created',
					['MentionsNotification', 'forum']);
			}
		}
	}


	/**
	 * Does 'Chatbox' mentions notifications using 'user_chatbox_post_created'
	 *  - event data.
	 *
	 * @param array $data
	 *  'Chatbox' event trigger _POST data
	 * @return bool
	 *  Returns false if no mention in 'cmessage'.
	 */
	public function chatbox($data)
	{
		// set tag - chatbox
		$this->setTag(LAN_MENTIONS_TAG_CHATBOX);

		// Debug
		// $this->log($data, 'chatbox-event-data');

		// if no mentions abort
		if ( ! $this->hasAtSign($data['cmessage'])) {
			return false;
		}

		$mentions = $this->fetchAllMentions($data['cmessage']);

		if ($mentions) {

			$this->setProperties([
				'mentions' => $mentions,
				'mentioner' => USERNAME,
				'date' => $this->getDate($data['datestamp']),
				'link' => $this->getContentLink()
				]);
			
			// notify
			$this->notifyAll();
		}
		unset($mentions);
	}

	/**
	 * Does comments mentions notifications using 'user_comment_posted'
	 *  - event data.
	 *
	 * @param array $data
	 *  'Comments' event trigger _POST data
	 *
	 * @return bool
	 *  Returns false if no mention in 'comment_comment'.
	 * @todo revisit to tidy up the sequence of routines
	 */
	public function comment($data)
	{
		// set tag - comment
		$this->setTag(LAN_MENTIONS_TAG_COMMENT);

		// Debug
		// $this->log($data, 'comments-event-data');

		// if no '@' signs or if comment is blocked - abort
		$hasAt = $this->hasAtSign($data['comment_comment']);

		if ( ! $hasAt || $data['comment_blocked'] ) {
			return false;
		}

		// get mentions
		$mentions = $this->fetchAllMentions($data['comment_comment']);

		if ($mentions) {

			$this->setCommentType($this->solveCommentType($data['comment_type']));
			$this->setEventData($data);

			$this->setProperties([
				'mentions' => $mentions,
				'mentioner' => $data['comment_author_name'],
				'date' => $this->getDate($data['comment_datestamp']),
				'title' => $data['comment_subject'],
				'link' => $this->getContentLink()
			]);

			// notify
			$this->notifyAll();
		}
		unset($mentions);
	}

	/**
	 * Does forum mentions notifications using 'user_forum_post_created'
	 *  - event data.
	 *
	 * @param array $data
	 *   Forum event trigger _POST data
	 * @return bool
	 *  Returns false if no mention in 'post_entry'.
	 * @todo revisit to tidy up the sequence of routines
	 */
	public function forum($data)
	{
		// set tag - forum
		$this->setTag(LAN_MENTIONS_TAG_FORUM);

		// Debug
		// $this->log($data, 'forum-event-data');

		// if no mentions abort
		if ( ! $this->hasAtSign($data['post_entry'])) {
			return false;
		}

		// get mentions
		$mentions = $this->fetchAllMentions($data['post_entry']);

		if ($mentions) {

			$this->setEventData($data);

			$this->setProperties([
					'mentions' => $mentions,
				    'mentioner' => USERNAME,
				    'date' => $this->getDate($data['post_datestamp'])
			]);

			// get more forum data
			$forumInfo = $this->getRequisiteForumData($data['post_thread']);
			$this->setEventData($forumInfo, true);

			$title = $forumInfo['thread_name'];
			$link = $this->getContentLink();

			// set more properties
			$this->setProperties([
				'title' => $title,
				'link' => $link
			]);

			// notify
			$this->notifyAll();
		}
		unset($mentions);
	}


	/**
	 * Checks if the string passed in has an '@' sign
	 *
	 * @param string $input
	 *  String passed in as argument
	 * @return bool
	 */
	protected function hasAtSign($input)
	{
		return strpos($input, '@') !== false;
	}


	/**
	 * Fetches all mentions in the message
	 *
	 * @param string $message
	 *  String to match for mentions
	 * @return array|null
	 */
	private function fetchAllMentions($message)
	{
		$pattern = '/(?<=\W|^)@([a-z0-9_.]*)/mis';

		if (preg_match_all($pattern, $message, $matches) !== false) {
			return $matches[0] ?: null;
		}

		return null;
	}


	/**
	 * Filters mentions array for duplicates and 'mentioner' themselves.
	 * @param $mentions
	 *  Mentions array passed by reference.
	 * @return array
	 *  Filtered array.
	 */
	private function filterMentions(&$mentions)
	{
		$mentions = array_values(array_unique($mentions, SORT_STRING));

		foreach ($mentions as $key => $value) {
			if ($this->mentioner === $this->stripAtFrom($value)) {
				unset($mentions[$key]);
			}
		}

		return $mentions;
	}

	/**
	 * Prepares mention date
	 *
	 * @param integer $date
	 *  Timestamp to be formatted
	 * @param string $format
	 *  Format type identifier - short | long | relative
	 * @return string
	 *  Formatted date as html
	 */
	private function getDate($date, $format = 'long')
	{
		return $this->parse->toDate($date, $format);
	}


	/**
	 * Notify each and every 'mentionee' in _POST data
	 *  - except the 'mentioner' themself
	 *
	 */
	private function notifyAll()
	{
		$mentions = $this->mentions;

		if (null === $mentions || $mentions !== (array)$mentions) {
			return false;
		}

		// filter mentions for duplicates and 'mentioner' themself
		$uniqueMentions = $this->filterMentions($mentions);

		// Debug
		$this->log($uniqueMentions, 'unique-mentions');

		$maxEmails = $this->prefs['max_emails'];

		for ($i = 0; $i < $maxEmails; $i++ ) {

			// being paranoid - the 'mentioner' should NEVER get an email.
			$mentionee = $this->stripAtFrom($uniqueMentions[$i]);

			if ($this->mentioner === $mentionee) {
				continue;
			}

			// get 'mentionee' details - email, username, userid
			$this->mentioneeData = $this->getUserData($uniqueMentions[$i]);

			// send email
			if (null !== $this->mentioneeData['user_email']
				&& null !== $this->mentioneeData['user_name']) {

				$this->dispatchEmail();
				continue;

			}
		}

	}


	/**
	 * Dispatches email to the mentioned user
	 *
	 * @return bool
	 *  true if success false if failure.
	 */
	private function dispatchEmail()
	{
		$mail = $this->mail;

		$emailContent = [
			'email_subject' =>  $this->emailSubject(),
			'send_html' => true,
			'email_body' =>  $this->emailBody(),
			'template' => 'default',
			'e107_header' => $this->mentioneeData['user_id'],
			'extra_header' => 'X-e107-Plugin : Mentions-Plugin-v'
		];

		$userEmail = $this->mentioneeData['user_email'];
		$userName = $this->mentioneeData['user_name'];

		// send email
		$emailSent = $mail->sendEmail($userEmail, $userName, $emailContent);

		// Debug
		$this->log($emailContent, 'email-content-array-log');

		if (true === $emailSent) {
			$emailContent = null;
			$mail = null;
			//unset($emailContent, $mail);
			return $emailSent;
		}
		// Debug
		$this->log($emailSent, 'email-send-error-log');

		return false;
	}


	/**
	 * Parses and returns email body
	 *
	 * @return string
	 */
	private function emailBody()
	{
		$bodyVars = [
			'MENTIONEE'     => $this->mentioneeData['user_name'],
			'MENTIONER'     => $this->mentioner,
			'MENTION_TEXT' => $this->emailText(),
		];

		return $this->parse->simpleParse($this->emailTemplate(), $bodyVars);
	}


	/**
	 * Returns email content html
	 *
	 * @return string
	 *  Html for email content
	 */
	private function emailTemplate()
	{
		$template = e107::getTemplate('mentions', 'email');

		if (empty($template)) {

			$template = '<div>
				<p>' . LAN_MENTIONS_EMAIL_HELLO . ' {MENTIONEE},</p>
				<p>{MENTION_TEXT}</p>
			</div>';
		}

		return $template;
	}


	/**
	 * Returns mention 'email notification citation' based on content tag
	 *
	 * @return string
	 *  Notification email passage/citation.
	 * @internal param string $tag
	 *  Tag name of the 'content type' for which the email text is requested.
	 */
	private function emailText()
	{
		switch ($this->tag) {
			case LAN_MENTIONS_TAG_CHATBOX:
				return $this->chatboxMailText();
				break;

			case LAN_MENTIONS_TAG_COMMENT:
				return $this->commentMailText();
				break;

			case LAN_MENTIONS_TAG_FORUM:

				return $this->forumMailText();
				break;

			default:
				return $this->parse->lanVars(LAN_MENTIONS_EMAIL_VERSE_UNRESOLVED,
					$this->mentioner);
				break;
		}
	}


	/**
	 * Parses and returns text for 'forum' notification email.
	 * @return string
	 *  Parsed text passage for 'forum' notification email.
	 */
	private function forumMailText()
	{
		$link = '<a href="' . $this->link . '">\'' . $this->title . '\'</a>';

		$vars = [
			'user'  => $this->mentioner,
			'tag'   => $this->tag,
			'date'  => $this->date,
			'link' => $link,
		];

		return $this->parse->lanVars(LAN_MENTIONS_EMAIL_VERSE_FORUM_NEW, $vars);
	}

	/**
	 * Parses and returns text for 'comment' notification email.
	 * @return string
	 *  Parsed text passage for 'comment' notification email.
	 */
	private function commentMailText()
	{

		if (empty($this->link)) {
			$link = $this->title;
		} else {
			$link = '<a href="' . $this->link . '">\'' . $this->title . '\'</a>';
		}


		$vars = [
			'user'  => $this->mentioner,
			'tag'   => $this->tag,
			'date'  => $this->date,
			'type'  => $this->commentType,
			'title' => $this->title,
			'link'  => $link
		];

		return $this->parse->lanVars(LAN_MENTIONS_EMAIL_VERSE_COMMENT_NEW, $vars);
	}


	/**
	 * Parses and returns text for 'chatbox' notification email.
	 * @return string
	 *  Parsed text passage for 'chatbox' notification email.
	 */
	private function chatboxMailText()
	{
		$link = '<a href="' . $this->link . '">' . $this->tag . '</a>';
		$vars = [
			'user' => $this->mentioner,
			'date' => $this->date,
			'link' => $link
		];

		return $this->parse->lanVars(LAN_MENTIONS_EMAIL_VERSE_CHATBOX_NEW, $vars);
	}


	/**
	 * Fetches the subject line for email based on plugin preference
	 *
	 * @return string
	 *  Email subject line.
	 */
	public function emailSubject()
	{
		$subjectLine = trim($this->prefs['email_subject_line']);
		if (null !== $subjectLine && $subjectLine !== '') {
			return str_replace('{MENTIONER}', $this->mentioner, $subjectLine);
		}

		return LAN_MENTIONS_EMAIL_SUBJECTLINE . $this->mentioner;
	}


	/**
	 * Decides comment's inheritor's 'name' from 'comment_type'
	 *
	 * @param mixed $input
	 *  'comment_type' obtained from event data
	 * @return string
	 *  The name of 'comment_type' in words.
	 */
	protected function solveCommentType($input)
	{
		if (ctype_digit($input)) {

			return $this->nameCommentType($input);
		}

		return $input;
	}


	/**
	 * Returns 'comment_type' name based on the current
	 *  - e107 numerical 'comment_types' specification.
	 *
	 * @param int $number
	 *
	 * @return string
	 *  'comment_type' string
	 */
	private function nameCommentType($number)
	{
		$number = (int)$number;

		switch ($number) {
			case 0:
				return LAN_MENTIONS_COMMENT_NEWS;
			case 4:
				return LAN_MENTIONS_COMMENT_POLL;
			case 2:
				return LAN_MENTIONS_COMMENT_DOWNLOADS;
			default:
				return LAN_MENTIONS_COMMENT_UNKNOWN;
		}
	}



	/**
	 * Get forum thread 'title' and other requisite data from 'forum' &
	 *  - 'forum_thread' tables
	 *
	 * @param integer $thread_id
	 *  Id of forum thread.
	 * @return array|bool
	 *  Data array or false on error.
	 */
	private function getRequisiteForumData($thread_id)
	{
		$sql = e107::getDb();
		$thread_id = (int)$thread_id;

		$query = "SELECT f.forum_sef, f.forum_id, ft.thread_id, ft.thread_name FROM `#forum` AS f "
			. "LEFT JOIN `#forum_thread` AS ft ON f.forum_id = ft.thread_forum_id "
			. " WHERE ft.thread_id = {$thread_id} ";

		$result = $sql->gen($query);

		if ($result) {
			return $sql->fetch($result);
		}

		return $result;
	}


	/**
	 * Returns the content entity links for notification email.
	 *
	 * @return string
	 *  html markup for the content link
	 */
	private function getContentLink()
	{
		switch ($this->tag) {

			case LAN_MENTIONS_TAG_CHATBOX:
				return SITEURLBASE . e_PLUGIN_ABS . 'chatbox_menu/chat.php';
				break;

			case LAN_MENTIONS_TAG_COMMENT: // news, downloads, polls, and other third party plugins
				return $this->getCommentItemLink();
				break;

			case LAN_MENTIONS_TAG_FORUM:
				return $this->getForumItemLink();
				break;

			default:
				return '[unresolved]';
				break;

		}
	}


	/**
	 * Parse and return 'forum' item link
	 * @return string
	 *  Forum item URL
	 */
	private function getForumItemLink()
	{
		$data = [
			'forum_sef' => $this->eventData['forum_sef'],
			'thread_id' => $this->eventData['thread_id'],
			'thread_sef' => $this->createSlug($this->eventData['thread_name'])
		];

		$opt = $this->getLinkOptions('forum');
		return e107::url('forum', 'topic', $data, $opt);
	}


	/**
	 * Parse and return 'comment' items link based on comment type.
	 * @return string
	 *  Comment item URL
	 */
	private function getCommentItemLink()
	{
		$type = $this->commentType;

		$opt = $this->getLinkOptions($type);

		switch ($type) {

			case LAN_MENTIONS_COMMENT_NEWS: // news

				$news = [
					'news_id' => $this->eventData['comment_item_id'],
					'news_sef' => $this->createSlug($this->eventData['comment_subject'])
					];
				return e107::getUrl()->create('news/view/item', $news, $opt);
				//return e107::url('news/view', 'item', $news, ['mode' => full]); // todo: find out if this will work for news
				break;

			case LAN_MENTIONS_COMMENT_DOWNLOADS: // downloads

				$download = [
					'download_id' => $this->eventData['comment_item_id'],
					'download_sef' => $this->createSlug($this->eventData['comment_subject'])
				];
				return e107::url('download', 'item', $download, $opt);
				break;


			case LAN_MENTIONS_COMMENT_POLL: // poll

				// does not support on the fly url generation now I suppose - plugin has no e_url addon
				return SITEURLBASE . e_PLUGIN_ABS . 'poll/oldpolls.php?' . $this->eventData['comment_item_id'];
				break;

			case 'page':
				$page = [
					'page_id' => $this->eventData['comment_item_id'],
					'page_title' => $this->eventData['comment_subject'],
					'page_sef' => $this->createSlug($this->eventData['comment_subject'])
				];
				return e107::getUrl()->create('page/view', $page, $opt);
				break;
				
			case LAN_MENTIONS_COMMENT_UNKNOWN:
				return SITEURLBASE;
				break;


			default:
				return null;
		}

	}


	/**
	 * Create slug from title
	 * @param  string $title
	 * @param string $type
	 *
	 * @return string
	 *  The slug string
	 */
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
				return [ 'full' => true ];
				break;

			case 'page':
				return [ 'full' => true ];
				break;

			case 'downloads':
				if ($coreUrlPref['download']) {
					return [ 'mode' => 'full',
					         'legacy' => false ];
				}
				break;

			case 'forum':
				if ($coreUrlPref['forum']) {
					return [
						'mode' => 'full',
						'query' => ['last' => 1],
						'legacy' => false ];
				}
				break;
		}

		return [ 'mode' => 'full', 'legacy' => true ];
	}


}
