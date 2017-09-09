<?php


class MentionsNotification extends Mentions
{
	private $mentions;
	private $mentioner;
	private $mentionDate;
	private $mentioneeData;




	/**
	 * Stores attribute data as array key value pair if property does not exist
	 * @var array
	 */
	private $dataVars = [];


	/**
	 * Magic set
	 * @param $name
	 * @param $value
	 */
	private function __set($name, $value)
	{
		$this->set($name, $value);
	}


	/**
	 * Magic get
	 * @param $name
	 *
	 * @return mixed|null
	 */
	private function __get($name)
	{
		return $this->get($name);
	}


	/**
	 * Magic isset
	 * @param $name
	 */
	protected function __isset($name)
	{
		// TODO: Implement __isset() method.
	}


	/**
	 * Magic unset
	 * @param $name
	 */
	protected function __unset($name)
	{
		// TODO: Implement __unset() method.
	}

	/**
	 * Gets property
	 * @param $name
	 *
	 * @return mixed|null
	 */
	private function get($name)
	{
		if (property_exists($this, $name)) {
			return $this->$name;
		}

		if (array_key_exists($name, $this->dataVars)) {
			return $this->dataVars[$name];
		}

		return null;
	}


	/**
	 * Sets property
	 * @param $name
	 * @param $value
	 */
	private function set($name, $value)
	{
		if (property_exists($this, $name)) {
			$this->$name = $value;
		}

		$this->dataVars[$name] = $value;

	}


	/**
	 * Prepares mention date
	 *
	 * @param        $date
	 * @param string $format
	 *
	 * @return \HTML
	 */
	private function createMentionDate($date, $format = 'long')
	{
		return e107::getParser()->toDate($date, $format);
	}


	/**
	 * Public static method to call perform() which listen to event triggers
	 * and performs mentions notification
	 */
	public static function execute()
	{
		$mn = new MentionsNotification;
		$mn->perform();
	}


	/**
	 * Listens to event triggers and performs mentions notification
	 */
	private function perform()
	{
		if (USER && (strtolower($_SERVER['REQUEST_METHOD']) === 'post' || e_AJAX_REQUEST)) {

			if ($this->prefs['notify_chatbox_mentions']) {
				e107::getEvent()->register('user_chatbox_post_created',
					['MentionsNotification', 'chatbox']);
			}

			if ($this->prefs['notify_comment_mentions']) {
				e107::getEvent()->register('user_comment_posted',
					['MentionsNotification', 'comment']);
			}

			if ($this->prefs['notify_forum_topic_mentions']) {
				e107::getEvent()->register('user_forum_topic_created',
					['MentionsNotification', 'forum']);
			}

			if ($this->prefs['notify_forum_reply_mentions']) {
				e107::getEvent()->register('user_forum_post_created',
					['MentionsNotification', 'forum']);
			}

		}
	}


	/**
	 * Does Chatbox mentions notifications
	 * @param $data
	 *
	 * @return bool
	 */
	public function chatbox($data)
	{
		// Debug
		$this->log(json_encode($data), 'chatbox-trigger-data');

		if ( ! $this->hasAtSign($data['cmessage'])) {
			return false;
		}

		$mentions = $this->getAllMentions($data['cmessage']);

		if ($mentions) {
			$this->mentions = $mentions;
			$this->mentioner = USERNAME;
			$this->mentionDate = $this->createMentionDate($data['datestamp']);
			$this->itemTag = 'chatbox post';
			// notify
			$this->notifyAll();
			// todo: unset some vars if done.
		}
	}

	/**
	 * Does Comments mentions notifications
	 *
	 * @param $data
	 * @return bool
	 */
	public function comment($data)
	{
		// Debug
		$this->log(json_encode($data), 'comments-trigger-data');

		// if no mentions abort
		if ( ! $this->hasAtSign($data['comment_comment'])) {
			return false;
		}

		$mentions = $this->getAllMentions($data['comment_comment']);

		if ($mentions) {
			$this->mentions = $mentions;
			$this->mentioner = $data['comment_author_name'];
			$this->itemTag = 'comment post';

			// sets date
			$this->mentionDate = $this->createMentionDate($data['comment_datestamp']);

			//todo: remove these after checking
			$this->itemId = $data['comment_id'];
			$this->itemPossessorId = $data['comment_item_id'];
			$this->itemPossessorType = $data['comment_type'];
			$this->itemApproval = $data['comment_blocked'];

			// comment type detection
			$this->commentType = $this->getCommentType($data['comment_type']);

			// comment title
			$this->itemTitle = $data['comment_subject'];

			// debug
			$this->log(json_encode($this->dataVars), 'notify-vars-array-data');

			//todo: check for comment approval before notifying
			//notify
			$this->notifyAll();
			// todo: unset some vars if done.
		}
	}


	/**
	 * Does Forum mentions notifications
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function forum($data)
	{
		// Debug
		$this->log(json_encode((array) $data), 'forums-trigger-data');

		/*
		foreach ($data as $k => $v) {
			//$this->log("$k = $v", 'forums-trigger-data');
			if ($k ===  'thread_name') {
				//$this->forumTitle = $v;
				$this->log($v, 'forums-trigger-data-title');
				$this->itemTitle = $v;
			}
		}
		*/

		//$forumData = preg_split("/[\n]+/", json_encode($data));

		//$forumData = explode("\n", json_encode($data));

		//convert into proper json format
		//$jsonData = implode(',', $forumData);





		if ( ! $this->hasAtSign($data['post_entry'])) {
			return false;
		}


		// $this->log($this->itemTitle, 'forums-trigger-data-4');

		

		$mentions = $this->getAllMentions($data['post_entry']);



		if ($mentions) {
			$this->mentions = $mentions;
			$this->mentioner = USERNAME;

			// todo: logic to differentiate between new forum post/topic and forum reply
			/*
			if (isset($data['thread_id'])) {
				$this->itemTag = 'forum post';
			} else {
				$this->itemTag = 'forum reply';
			}
			*/

			$this->itemTag = 'forum post';

			// date/time
			$this->mentionDate = $this->createMentionDate($data['post_datestamp']);

			$this->itemTitle = $data['thread_name'];



			$this->notifyAll();
			// todo: unset some vars if done.
		}
	}


	/**
	 * Does Debug logging
	 *
	 * @param string $content
	 * @param string $logname
	 */
	private function log($content, $logname = 'mentions')
	{
		$path = e_PLUGIN . 'mentions/' . $logname . '.txt';
		file_put_contents($path, $content . "\n", FILE_APPEND);
		unset($path, $content);
	}


	/**
	 * Checks if the string passed in as argument has an @ sign
	 * @param $input
	 *
	 * @return bool
	 */
	protected function hasAtSign($input)
	{
		return strpos($input, '@') !== false;
	}


	/**
	 * Gets all mentions in the message
	 *
	 * @param $message
	 *
	 * @return array|null
	 */
	private function getAllMentions($message)
	{
		$pattern = '/(?<=\W|^)@([a-z0-9_.]*)/mis';

		if (preg_match_all($pattern, $message, $matches) !== false) {
			return $matches[0];
		}

		return null;
	}


	/**
	 * Notify each mentionees in a post after making sure
	 * that the mentioner is not the mentionee
	 *
	 */
	private function notifyAll()
	{
		$mentions = $this->mentions;

		if (null === $mentions || $mentions !== (array)$mentions) {
			return false;
		}

		foreach (array_unique($mentions, SORT_STRING) as $mention) {

			// no notification if 'mentioner' is the 'mentionee'
			$mentionee = $this->stripAtFrom($mention);

			if ($this->mentioner === $mentionee) {
				continue;
			}

			// 'mentionee' details - email, username, userid
			$this->mentioneeData = $this->getUserData($mention);

			// Debug
			$this->log(json_encode($this->mentioneeData), 'mentionee-data');

			// Email
			if (null !== $this->mentioneeData['user_email'] && null !== $this->mentioneeData['user_name']) {

				$this->dispatchEmail();
				unset($this->mentioneeData);
				continue;

			}

		}

	}


	/**
	 * Dispatches email to the mentioned user
	 *
	 * @return boolean - true if success false if failed
	 */
	private function dispatchEmail()
	{
		$mail = e107::getEmail();

		$body = $this->emailBody();

		$email = [];
		$email['email_subject'] = $this->emailSubject();
		$email['send_html'] = true;
		$email['email_body'] = $body;
		$email['template'] = 'default';
		$email['e107_header'] = $this->mentioneeData['user_id'];

		$sendEmail = $mail->sendEmail($this->mentioneeData['user_email'],
			$this->mentioneeData['user_name'], $email);

		if ($sendEmail) {
			unset($body, $email);
			return $sendEmail;
		}

		// Debug
		$this->log($sendEmail, 'send-email-error-log');

		return false;
	}


	/**
	 * Parses and returns email body
	 * @return string
	 * todo : tidy-up this method
	 */
	private function emailBody()
	{
		$EMAIL_TEMPLATE = $this->emailTemplate();

		$mentionee_name = $this->mentioneeData['user_name'];
		$mentioner = $this->mentioner;
		$url = $this->getMentionContentLink();
		$url2 = $this->compileContentLink();
		$mention_verse = $this->getMentionVerse($this->itemTag);

		$bodyVars = [
			'MENTIONEE'    => $mentionee_name,
			'MENTIONER'    => $mentioner,
			'MENTION_VERSE' => $mention_verse,
			'URL'          => $url2,
		];

		return e107::getParser()->simpleParse($EMAIL_TEMPLATE, $bodyVars);
	}


	/**
	 * Returns Email template
	 * @return string
	 */
	private function emailTemplate()
	{
		//$EMAIL_TEMPLATE = e107::getTemplate('mentions', 'mentions', 'notify');
		$EMAIL_TEMPLATE = '';

		if (empty($EMAIL_TEMPLATE)) {

			$EMAIL_TEMPLATE = '<div>
				<p>Hello {MENTIONEE},</p>
				<p>{MENTION_VERSE}</p>
			</div>';

		}

		return $EMAIL_TEMPLATE;
	}


	/**
	 * Returns mention email notification sentense based on content tag
	 * @param $type
	 *
	 * @return string
	 * @todo make language files compatible
	 */
	private function getMentionVerse($type)
	{
		switch ($type) {
			case 'chatbox post':
				$vars = [
					'user' => $this->mentioner,
					'tag' => $this->itemTag,
					'date' => $this->mentionDate
				];

				return $this->parse->lanVars(LAN_MENTIONS_EMAIL_VERSE_CHATBOX, $vars);
				break;

			case 'comment post':
				$vars = [
					'user' => $this->mentioner,
					'tag' => $this->itemTag,
					'date' => $this->mentionDate,
					'type' => $this->commentType,
					'title' => $this->itemTitle
				];

				return $this->parse->lanVars(LAN_MENTIONS_EMAIL_VERSE_COMMENT, $vars);
				break;

			case 'forum post':
				$vars = [
					'user'  => $this->mentioner,
					'tag'   => $this->itemTag,
					'date'  => $this->mentionDate
				];

				return $this->parse->lanVars(LAN_MENTIONS_EMAIL_VERSE_FORUM, $vars);
				break;

			default:
				return $this->parse->lanVars(LAN_MENTIONS_EMAIL_VERSE_UNRESOLVED, $this->mentioner);
				break;
		}
		
	}

	/**
	 * Experimental: Mention link returner
	 * todo: develop this stub
	 *
	 * @return string
	 */
	private function getMentionContentLink()
	{
		return '--LINK---';
	}


	/**
	 * Experimental: Link compiler method
	 * @return string
	 */
	private function compileContentLink()
	{
		switch ($this->itemTag) {
			case 'chatbox post':
				$url = SITEURLBASE . e_PLUGIN_ABS . 'chatbox_menu/chat.php';
				return '<a href="' . $url . '">this link</a>';
				break;
			case 'comment post':
				return '--COMMENT-LINK--';
				break;
			case 'forum reply':
				return '--FORUM-LINK--';
				break;
			default:
				return '[unresolved]';
				break;
		}
	}


	/**
	 * Preps subject line
	 *
	 * @return string
	 */
	public function emailSubject()
	{
		$subjectLine = trim($this->prefs['email_subject_line']);
		if (null !== $subjectLine && $subjectLine !== '') {
			return str_replace('{MENTIONER}', $this->mentioner, $subjectLine);
		}

		return 'You were mentioned by ' . $this->mentioner;
	}


	/**
	 * Gets comment's ascendant name from 'comment_type'
	 * received from comment event data
	 *
	 * @param $input
	 *
	 * @return string
	 */
	protected function getCommentType($input)
	{
		if (ctype_digit($input)) {
			return $this->commentType($input);
		}

		return $input;
	}


	/**
	 * Returns comment type name string based on e107 comment type spec.
	 * @param $input
	 *
	 * @return string
	 */
	private function commentType($input)
	{
		$input = (int) $input;

		switch ($input) {
			case 0:
				return 'news';
			case 4:
				return 'poll';
			case 2:
				return 'downloads';
			default:
				return 'unknown';
		}
	}


}
