<?php
if ( ! defined('e107_INIT')) {
	exit;
}

class MentionsNotification extends Mentions
{
	private $mentionDate;
	private $mentioneeData;
	private $mentions;
	private $mentioner;

	private $itemTag;
	private $itemTitle;
	private $commentType;

	private $mail;
	private $eventData = [];


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
	 * Public static method to call perform()
	 *
	 */
	public static function execute()
	{
		$notification = new MentionsNotification;
		$notification->perform();
	}


	/**
	 * Listens to event triggers and performs mentions notification
	 *
	 */
	private function perform()
	{
		if (USER && $this->prefs['mentions_active'] && (strtolower($_SERVER['REQUEST_METHOD']) === 'post' || e_AJAX_REQUEST)) {

			if ($this->prefs['notify_chatbox_mentions']) {
				e107::getEvent()->register('user_chatbox_post_created',
					['MentionsNotification', 'chatbox']);
			}

			if ($this->prefs['notify_comment_mentions']) {
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
	 * Does Chatbox mentions notifications
	 *
	 * @param array $data
	 *  'Chatbox' _POST data
	 * @return bool
	 *  Returns false if no mention in 'Chatbox' message.
	 */
	public function chatbox($data)
	{

		// $this->log($data, 'chatbox-event-data');

		// if no mentions abort
		if ( ! $this->hasAtSign($data['cmessage'])) {
			return false;
		}

		$mentions = $this->getAllMentions($data['cmessage']);

		// Debug
		// $this->log($mentions, 'chatbox-mentions-test');

		if ($mentions) {
			$this->mentions = $mentions;
			$this->mentioner = USERNAME;
			$this->mentionDate = $this->getMentionDate($data['datestamp']);
			$this->itemTag = LAN_MENTIONS_TAG_CHATBOX;

			// notify
			$this->notifyAll();

			// todo: unset some vars
		}
	}

	/**
	 * Does Comments mentions notifications
	 *
	 * @param array $data
	 *  'Comments' _POST data
	 *
	 * @return bool
	 *  Returns false if no mention in 'comment_comment'.
	 */
	public function comment($data)
	{
		// Debug
		// $this->log($data, 'comments-event-data');

		// if no '@' signs or comment is blocked - abort
		$hasAt = $this->hasAtSign($data['comment_comment']);

		if ( ! $hasAt || $data['comment_blocked'] ) {
			return false;
		}

		// get mentions
		$mentions = $this->getAllMentions($data['comment_comment']);

		if ($mentions) {
			$this->mentions = $mentions;
			$this->mentioner = $data['comment_author_name'];
			$this->itemTag = LAN_MENTIONS_TAG_COMMENT;

			// set date
			$this->mentionDate =
				$this->getMentionDate($data['comment_datestamp']);

			// comment type detection
			$this->commentType = $this->getCommentType($data['comment_type']);

			// comment title
			$this->itemTitle = $data['comment_subject'];

			// notify
				$this->notifyAll();

			// todo: unset some vars
		}
	}

	/**
	 * Does Forum mentions notifications
	 *
	 * @param array $data
	 *   Forum _POST data
	 * @return bool
	 *  Returns false if no mention in 'post_entry'.
	 */
	public function forum($data)
	{
		// Debug
		// $this->log($data, 'forum-event-data');

		// if no mentions abort
		if ( ! $this->hasAtSign($data['post_entry'])) {
			return false;
		}

		// get mentions
		$mentions = $this->getAllMentions($data['post_entry']);

		if ($mentions) {

			$this->mentions = $mentions;

			$this->mentioner = USERNAME;

			$this->itemTag = LAN_MENTIONS_TAG_FORUM;

			// date/time
			$this->mentionDate = $this->getMentionDate($data['post_datestamp']);

			// get more forum data
			$forumInfo = $this->getForumPostExtendedData($data['post_thread']);

			$this->itemTitle = $forumInfo['thread_name'];

			$this->notifyAll();
			// todo: unset some vars
		}

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
	 * Gets all mentions in the message
	 *
	 * @param string $message
	 *  String to match for mentions
	 * @return array|null
	 */
	private function getAllMentions($message)
	{
		$pattern = '/(?<=\W|^)@([a-z0-9_.]*)/mis';

		if (preg_match_all($pattern, $message, $matches) !== false) {
			return $matches[0] ?: null;
		}

		return null;
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
	private function getMentionDate($date, $format = 'long')
	{
		return $this->parse->toDate($date, $format);
	}


	/**
	 * Notify each mentionees in a post after making sure that the
	 *  mentioner is not the mentionee
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
			$this->log($this->mentioneeData, 'mentionee-data');

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
	 * @return bool
	 *  true if success false if failure.
	 */
	private function dispatchEmail()
	{
		$mail = $this->mail;

		$email = [
			'email_subject' =>  $this->emailSubject(),
			'send_html' => true,
			'email_body' =>  $this->emailBody(),
			'template' => 'default',
			'e107_header' => $this->mentioneeData['user_id'],
			'extra_header' => 'X-e107-Plugin : Mentions-Plugin-v'
		];

		$sendEmail = $mail->sendEmail($this->mentioneeData['user_email'],
			$this->mentioneeData['user_name'], $email);

		$this->log($email, 'email-body-log');

		if ($sendEmail) {

			$email = null;
			$mail = null;
			unset($email, $mail);

			return $sendEmail;
		}

		// Debug
		$this->log($sendEmail, 'send-email-error-log');

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
			'MENTION_TEXT' => $this->emailText($this->itemTag),
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
		// $emailTemplate = e107::getTemplate('mentions', 'mentions', 'notify');
		$emailTemplate = '';

		if (empty($emailTemplate)) {

			$emailTemplate = '<div>
				<p>' . LAN_MENTIONS_EMAIL_HELLO . ' {MENTIONEE},</p>
				<p>{MENTION_TEXT}</p>
			</div>';

		}

		return $emailTemplate;
	}


	/**
	 * Returns mention 'email notification citation' based on content tag
	 *
	 * @param string $type
	 *  Content type for which the passage/citation is requested.
	 * @return string
	 *  Notification email passage/citation.
	 */
	private function emailText($type)
	{
		switch ($type) {
			case LAN_MENTIONS_TAG_CHATBOX:
				$vars = [
					'user' => $this->mentioner,
					'tag'  => $this->itemTag,
					'date' => $this->mentionDate,
				];

				return $this->parse->lanVars(LAN_MENTIONS_EMAIL_VERSE_CHATBOX,
					$vars);
				break;

			case LAN_MENTIONS_TAG_COMMENT:
				$vars = [
					'user'  => $this->mentioner,
					'tag'   => $this->itemTag,
					'date'  => $this->mentionDate,
					'type'  => $this->commentType,
					'title' => $this->itemTitle,
					//'title' => htmlentities($this->itemTitle),
				];

				return $this->parse->lanVars(LAN_MENTIONS_EMAIL_VERSE_COMMENT,
					$vars);
				break;

			case LAN_MENTIONS_TAG_FORUM:
				$vars = [
					'user'  => $this->mentioner,
					'tag'   => $this->itemTag,
					'date'  => $this->mentionDate,
					'title' => $this->itemTitle,
				];

				return $this->parse->lanVars(LAN_MENTIONS_EMAIL_VERSE_FORUM,
					$vars);
				break;

			default:
				return $this->parse->lanVars(LAN_MENTIONS_EMAIL_VERSE_UNRESOLVED,
					$this->mentioner);
				break;
		}

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
	 * Decide comment's inheritor's 'name' from 'comment_type' event data
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
	 * Returns 'comment_type' name string based on existing -
	 * e107 'comment_types' spec.
	 *
	 * @param $input
	 *
	 * @return string
	 */
	private function commentType($input)
	{
		$input = (int)$input;

		switch ($input) {
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
	 * Get forum thread 'title' and other data from 'forum' & 'forum_thread' tables
	 *
	 * @param integer $thread_id
	 *  Id of forum thread.
	 * @return array|bool
	 */
	private function getForumPostExtendedData($thread_id)
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
	 * Experimental: Link compiler method
	 * @return string
	 *  html markup for the content link
	 */
	private function compileContentLink($linkData)
	{
		$tag = $this->itemTag;
		$opt = [
			'mode' => 'full',
			'legacy' => false
		];

		switch ($tag) {
			case 'chatbox':
				$url = SITEURLBASE . e_PLUGIN_ABS . 'chatbox_menu/chat.php';
				return '<a href="' . $url . '">this link</a>';
				break;
			case 'comment': // news, downloads, polls, ? webomics and other third party plugins
				return '--COMMENT-LINK--';
				break;
			case 'forum':
				return e107::url('forum', 'topic', $this->modifyForumData($linkData), $opt);
				break;
			default:
				return '[unresolved]';
				break;
		}
	}


	/**
	 * 
	 * @param $linkData
	 *
	 * @return array
	 */
	private function modifyForumData($linkData)
	{

		$linkData = [
			'forum_sef' => $linkData['forum_sef'],
			'thread_id' => $linkData['thread_id'],
			'thread_sef' => $this->linkSlugFrom($linkData['thread_name'])
		];

		return $linkData;
	}


	/**
	 * Get comment item links
	 * @return string
	 */
	private function getCommentItemLink()
	{
		$type = $this->commentType;

		switch ($type) {
			case 'news':
				break;
			case 'downloads':
				return e107::url('download', 'item'); // 'sef' => '{alias}/{download_id}/{download_sef}',
				break;
			case 'poll':
				return '';
				break;
			default:
				break;
		}

	}


	private function linkSlugFrom($title, $type = 'dashl')
	{
		return eHelper::title2sef($title, $type);
	}

}
