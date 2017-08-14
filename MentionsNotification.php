<?php


class MentionsNotification extends Mentions
{
	protected $mentions;
	protected $mentioner;
	protected $mentioneeData;

	protected $entityTag;
	protected $entityId;
	protected $entityApproval;
	protected $entityPossessorId;
	protected $entityPossessorType;


	protected $entityData;
	protected $entityMessage;

	protected $entityPointerUrl;



	/**
	 * Public static method to call perform()
	 */
	public static function execute()
	{
		$mn = new MentionsNotification;
		$mn->perform();
	}


	/**
	 * Listen to and perform notification
	 */
	private function perform()
	{
		if (USER && (strtolower($_SERVER['REQUEST_METHOD']) === 'post' || e_AJAX_REQUEST)) {

			if ($this->prefs['notify_chatbox_mentions']) {
				//$this->chatboxMentionsNotifyOld();
				e107::getEvent()->register('user_chatbox_post_created',
					['MentionsNotification', 'chatboxMentionsNotify']);
			}

			if ($this->prefs['notify_comment_mentions']) {
				e107::getEvent()->register('user_comment_posted',
					['MentionsNotification', 'commentsMentionsNotify']);
			}

			if ($this->prefs['notify_forum_topic_mentions']) {
				e107::getEvent()->register('user_forum_topic_created',
					['MentionsNotification', 'forumsMentionsNotify']);
			}

			if ($this->prefs['notify_forum_reply_mentions']) {
				e107::getEvent()->register('user_forum_post_created',
					['MentionsNotification', 'forumsMentionsNotify']);
			}

		}
	}


	/**
	 * @param $data
	 *
	 * @return bool
	 */
	public function chatboxMentionsNotify($data)
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
			$this->entityTag = 'chatbox post';
			$this->notifyAllMentioned();
		}
	}


	/**
	 * Comments mentions notify
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function commentsMentionsNotify($data)
	{
		// Debug
		$this->log(json_encode($data), 'comments-trigger-data');

		if ( ! $this->hasAtSign($data['comment_comment'])) {
			return false;
		}
		$mentions = $this->getAllMentions($data['comment_comment']);
		if ($mentions) {
			$this->mentions = $mentions;
			$this->mentioner = $data['comment_author_name'];
			$this->entityTag = 'comment post';
			$this->entityId = $data['comment_id'];
			$this->entityPossessorId = $data['comment_item_id'];
			$this->entityPossessorType = $data['comment_type'];
			$this->entityApproval = $data['comment_blocked'];
			$this->notifyAllMentioned();
		}
	}


	/**
	 * Forum mentions notify
	 *
	 * @param $data
	 */
	public function forumsMentionsNotify($data)
	{
		// Debug
		$this->log(json_encode($data), 'forums-trigger-data');

		$mentions = $this->getAllMentions($data['post_entry']);
		if ($mentions) {
			$this->mentions = $mentions;
			$this->mentioner = USERNAME;
			$this->entityTag =
				'forum post'; // todo: find a logic to differentiate between new forum post/topic and forum reply
			$this->notifyAllMentioned();
		}
	}


	/**
	 *
	 */
	private function chatboxMentionsNotifyOld()
	{
		if ($_POST['chat_submit'] && $_POST['cmessage'] !== '') {
			// Debug
			// $this->log(json_encode([USERNAME, $_POST['cmessage']]));
			if ( ! $this->hasAtSign($_POST['cmessage'])) {
				return false;
			}
			$mentions = $this->getAllMentions($_POST['cmessage']);
			if ($mentions) { // todo: logic to check if array
				$this->mentions = $mentions;
				$this->mentioner = USERNAME;
				$this->entityTag = 'chatbox post';
				$this->notifyAllMentioned();
			}
		}
	}


	/**
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
	 * @return array | null
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
	 * Notify all mentionees in a post
	 */
	private function notifyAllMentioned()
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
			// $this->log(json_encode($this->mentioneeData), 'mentionee-data');

			// Email
			if ($this->mentioneeData && count($this->mentioneeData)) {
				$this->email($this->mentioneeData);
				unset($this->mentioneeData);
			}

		}

	}


	/**
	 * Send an email to notify of a mention
	 *
	 * @param array $userData - user data
	 * @param array $contentData - Content details
	 *
	 * @return boolean
	 */
	private function email($userData)
	{
		$mail = e107::getEmail();

		$MENTIONS_NOTIFY = $this->template();

		$mentionee_name = $userData['user_name'];
		$mentioner = $this->mentioner;
		$content = $this->getContentType();
		$date = e107::getParser()->toDate(time());
		$url = $this->getMentionContentLink();
		$url2 = $this->compileContentLink();

		$bodyVars = [
			'MENTIONEE'    => $mentionee_name,
			'DATE'         => $date,
			'SITENAME'     => SITENAME,
			'MENTIONER'    => $mentioner,
			'CONTENT_TYPE' => $content,
			'URL'          => $url2,
		];

		$body = e107::getParser()->simpleParse($MENTIONS_NOTIFY, $bodyVars);

		$email = [];
		$email['email_subject'] = $this->prepSubjectLine();
		$email['send_html'] = true;
		$email['email_body'] = $body;
		$email['template'] = 'default';
		$email['e107_header'] = $userData['user_id'];

		$sendEmail =
			$mail->sendEmail($userData['user_email'], $userData['user_name'],
				$email);

		if ($sendEmail) {
			unset($body, $bodyVars, $email);
			return true;
		}

		return false;
	}


	/**
	 * @return array|string
	 */
	private function template()
	{
		//$MENTIONS_NOTIFY = e107::getTemplate('mentions', 'mentions', 'notify');
		$MENTIONS_NOTIFY = '';

		if (empty($MENTIONS_NOTIFY)) {

			$MENTIONS_NOTIFY = '<div>
			<h4>You have been mentioned!</h4>
			<p>Hello {MENTIONEE},</p>
			<p>{MENTIONER} mentioned you in a {CONTENT_TYPE} at {SITENAME} on {DATE}.</p>
			<p>Follow {URL} to have a look.</p>
			</div>';

		}

		return $MENTIONS_NOTIFY;
	}


	/**
	 * Preps subject line
	 * @return string
	 */
	public function prepSubjectLine()
	{
		$subjectLine = trim($this->prefs['email_subject_line']);
		if (null !== $subjectLine && $subjectLine !== '') {
			return str_replace('{MENTIONER}', $this->mentioner, $subjectLine);
		}
		return 'You were mentioned by ' . $this->mentioner;
	}

	/**
	 * todo: develop this stub
	 *
	 * @return string
	 */
	private function getContentType()
	{
		return $this->entityTag;
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
	 * @return string
	 */
	private function compileContentLink()
	{
		switch ($this->entityTag) {
			case 'chatbox post':
				$url = SITEURLBASE . e_PLUGIN_ABS . 'chatbox_menu/chat.php';
				return '<a href="' . $url . '">this link</a>';
			case 'comment post':
				return '';
			case 'forum reply':
				return '';
			default:
				return '[unresolved]';
		}
	}


	/**
	 * @param $input
	 *
	 * @return string
	 */
	protected function findCommentType($input)
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
		$input = (int) $input;
		switch ($input) {
			case 0:
				return 'news';
			case 4:
				return 'poll';
			case 2:
				return 'downloads';
		}
	}

	/**
	 * Debug log method
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




}
