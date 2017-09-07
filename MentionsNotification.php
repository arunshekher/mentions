<?php


class MentionsNotification extends Mentions
{
	private $siteName;
	private $mentionDate;

	protected $mentions;
	protected $mentioner;
	protected $mentioneeData;




	/**
	 * Stores data from magic setter if property does not exist
	 * @var array
	 */
	private $notificationVars = [];


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

		if (array_key_exists($name, $this->notificationVars)) {
			return $this->notificationVars[$name];
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

		$this->notificationVars[$name] = $value;

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
			$this->mentionDate = $data['datestamp'];
			$this->itemTag = 'chatbox post';
			$this->notifyAll();
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

			$this->mentionDate = $data['comment_datestamp'];

			$this->itemId = $data['comment_id'];
			$this->itemPossessorId = $data['comment_item_id'];
			$this->itemPossessorType = $data['comment_type'];
			$this->itemApproval = $data['comment_blocked'];

			// comment type detection
			$this->commentType = $this->getCommentType($data['comment_type']);

			// comment title
			$this->getAscendantTitle($data['comment_subject']);

			// debug
			$this->log(json_encode($this->notificationVars), 'notify-vars-array-data');

			//todo: check for comment approval before notifying
			//notify
			$this->notifyAll();
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
		$this->log(json_encode($data), 'forums-trigger-data');

		$this->forumData = $data;


		if ( ! $this->hasAtSign($data['post_entry'])) {
			return false;
		}

		$this->log(json_encode($this->forumData), 'forums-trigger-data-3');


		$mentions = $this->getAllMentions($data['post_entry']);



		if ($mentions) {
			$this->mentions = $mentions;
			$this->mentioner = USERNAME;

			// todo: logic to differentiate between new forum post/topic and forum reply
			if (isset($data['thread_id'])) {
				$this->itemTag = 'forum post';
			} else {
				$this->itemTag = 'forum reply';
			}


			//$this->getAscendantTitle($data['thread_name']);
			$this->itemTitle = $data['thread_name'];

			// Debug
			$this->log(json_encode($data['thread_name']), 'forums-thread-name');

			// Debug
			$this->log(json_encode($this->mentions), 'forums-mentions');

			// Debug
			$this->log(json_encode($data), 'forums-trigger-data-2');

			$this->notifyAll();
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
	 * Notify each mentionees in a post
	 */
	private function notifyAll()
	{
		$mentions = $this->mentions;

		if (null === $mentions || $mentions !== (array)$mentions) {
			return false;
		}

		foreach (array_unique($mentions, SORT_STRING) as $mention) {

			if ($this->mentioner === $this->stripAtFrom($mention)) {
				continue;
			}

			$this->mentioneeData = $this->getUserData($mention);

			// Debug
			$this->log(json_encode($this->mentioneeData), 'mentionee-data');

			// Email
			if ($this->mentioneeData && count($this->mentioneeData)) {
				$this->dispatchEmail();
				unset($this->mentioneeData);
			}

		}

	}


	/**
	 * Sends email to notify mentioned user of a mention
	 *
	 * @return boolean
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
			return true;
		}

		return false;
	}


	/**
	 * Parses and returns email body
	 * @return string
	 */
	private function emailBody()
	{
		$EMAIL_TEMPLATE = $this->emailTemplate();

		$mentionee_name = $this->mentioneeData['user_name'];
		$mentioner = $this->mentioner;
		$content = $this->getContentType();
		$date = e107::getParser()->toDate(time());
		$url = $this->getMentionContentLink();
		$url2 = $this->compileContentLink();
		$mention_line = $this->getMentionLine($this->itemTag);

		$bodyVars = [
			'MENTIONEE'    => $mentionee_name,
			'DATE'         => $date,
			'SITENAME'     => SITENAME,
			'MENTIONER'    => $mentioner,
			'CONTENT_TYPE' => $content,
			'URL'          => $url2,
			'MENTION_LINE' => $mention_line
		];

		return e107::getParser()->simpleParse($EMAIL_TEMPLATE, $bodyVars);
	}


	/**
	 * Email template
	 * @return array|string
	 */
	private function emailTemplate()
	{
		//$EMAIL_TEMPLATE = e107::getTemplate('mentions', 'mentions', 'notify');
		$EMAIL_TEMPLATE = '';

		if (empty($EMAIL_TEMPLATE)) {

			$EMAIL_TEMPLATE = '<div>
			<p>Hello {MENTIONEE},</p>
			<p>{MENTION_LINE}</p>
			</div>';

		}

		return $EMAIL_TEMPLATE;
	}


	/**
	 * Returns mention email notification sentense based on content tag
	 * @param $type
	 *
	 * @return string
	 */
	private function getMentionLine($type)
	{
		switch ($type) {
			case 'chatbox post':
				return "$this->mentioner mentioned you in a $this->itemTag on $this->mentionDate.";
				break;
			case 'comment post':
				return "$this->mentioner mentioned you in a $this->itemTag for $this->commentType item titled '$this->itemTitle' on $this->mentionDate.";
				break;
			case 'forum post':
				return "$this->mentioner mentioned you in a $this->itemTag titled '$this->itemTitle' on $this->mentionDate.";
				break;
			case 'forum reply':
				return "$this->mentioner mentioned you in a $this->itemTag to a forum thread titled '$this->itemTitle' on $this->mentionDate.";
				break;
			default:
				return "$this->mentioner mentioned you in an un-resolvable mention!";
				break;
		}
		
	}

	/**
	 * todo: develop this stub
	 *
	 * @return string
	 */
	private function getContentType()
	{
		return $this->itemTag;
	}


	/**
	 * todo: develop this stub
	 *
	 * @return string
	 */
	private function getMentionContentLink()
	{
		return '--LINK---';
	}


	/**
	 * Test link
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
	 * Returns the posessor title based on itemTag
	 * @return mixed|null
	 */
	private function getAscendantTitle($title)
	{
		return $this->itemTitle = $title;
	}


	/**
	 * Gets comment type based on 'comment_type' in comment event data
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
	 * @param $input
	 *
	 * @return string
	 */
	private function commentType($input)
	{
		$input = (int)$input;

		switch ($input) {
			case 0:
				return 'News';
			case 4:
				return 'Poll';
			case 2:
				return 'Downloads';
			default:
				return null;
		}
	}


}
