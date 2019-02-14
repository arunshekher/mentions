<?php
require_once __DIR__ . '/MentionsContentEmails.php';
require_once __DIR__ . '/MentionsContentLinks.php';


class MentionsNotificationTest extends Mentions
{
	private $notificationSender = USERNAME;
	private $notificationSenderId = USERID;

	private $mentions;
	private $mentionedUsers;

	// todo: may be rename to $getEmailSubject, $emailMessage and so forth.
	private $notificationSubject;
	private $notificationMessage;
	private $notificationRecipient;

	private $eventType;
	private $eventData;
	private $eventUserText;


	/**
	 * Listen to, initializes and perform chatbox event notification.
	 *
	 * @param $data
	 */
	public function chatbox($data)
	{
		$this->init('chatbox', $data)->iterateAndNotify();
	}


	/**
	 * Iterates through mentions and send email
	 *
	 * @return $this
	 */
	private function iterateAndNotify()
	{
		for ($i =
			     0; $i < $this->prefs['max_emails']; $i++) { // todo: rename this pref to 'max_emails_per_post'

			// skipping e-mailing mentioner
			if ($this->isSenderRecipient($this->mentionedUsers[$i]['user_id'])) {
				continue;
			}

			// check & send email
			if ($this->hasEmailableData($this->mentionedUsers[$i])) {


				$this->setNotificationRecipient($this->mentionedUsers[$i]['user_name'])
					->setNotificationSubject($this->getEmailSubject())
					->setNotificationMessage($this->acquireEmailMessage());

				// send email
				//$this->sendMentionsEmail($this->mentionedUsers[$i]);

				// debug
				$this->log($this->notificationMessage,
					'mentions-notification-iterate-message');

				continue;
			}

		}

		return $this;
	}


	/**
	 * Returns if sender is recipient
	 *
	 * @param $recipientId
	 *
	 * @return bool
	 */
	private function isSenderRecipient($recipientId)
	{
		return ($this->notificationSenderId === (int)$recipientId);
	}


	/**
	 * Returns if the mentioned user has e-mailable data
	 *
	 * @param $mentionedUser
	 *
	 * @return bool
	 */
	private function hasEmailableData($mentionedUser)
	{
		return (null !== $mentionedUser['user_email'] && null !== $mentionedUser['user_name']);
	}


	/**
	 * @param mixed $notificationMessage
	 *
	 * @return MentionsNotificationTest
	 */
	public function setNotificationMessage($notificationMessage)
	{
		$this->notificationMessage = $notificationMessage;

		return $this;
	}


	/**
	 * @param mixed $notificationSubject
	 *
	 * @return MentionsNotificationTest
	 */
	public function setNotificationSubject($notificationSubject)
	{
		$this->notificationSubject = $notificationSubject;

		return $this;
	}


	/**
	 * @param mixed $notificationRecipient
	 *
	 * @return MentionsNotificationTest
	 */
	public function setNotificationRecipient($notificationRecipient)
	{
		$this->notificationRecipient = $notificationRecipient;

		return $this;
	}


	/**
	 * Fetches the subject line for email based on plugin preference
	 *
	 * @return string
	 *  Email subject line.
	 */
	public function getEmailSubject()
	{
		$subjectLine = trim($this->prefs['email_subject_line']);

		if (null !== $subjectLine && $subjectLine !== '') {
			return str_replace('{MENTIONER}', $this->notificationSender,
				$subjectLine);
		}

		return LAN_MENTIONS_EMAIL_SUBJECTLINE . $this->notificationSender;
	}


	/**
	 * Returns mention 'email notification citation' based on content tag
	 *
	 * @return string
	 *  Notification email passage/citation.
	 * @internal param string $tag
	 *  Tag name of the 'content type' for which the email text is requested.
	 */
	private function acquireEmailMessage()
	{

		$mail = new ContentEmailsFactory($this->eventType, $this->eventData);

		return $mail->generate();
	}


	/**
	 * Initializes MentionsNotificationTest class
	 *
	 * @param $eventType
	 * @param $eventData
	 *
	 * @return \MentionsNotificationTest
	 */
	private function init($eventType, $eventData)
	{
		return $this->setEventType($eventType)->setEventData($eventData)
			->setEventUserText()->parseAllMentions($this->eventUserText)
			->fetchEachUserDetails()->filterDuplicates();
	}


	/**
	 * Filters out duplicate entries in self::mentionedUsers
	 *
	 * @return $this
	 */
	private function filterDuplicates()
	{
		if (count($this->mentionedUsers) > 1) {
			$this->mentionedUsers =
				array_unique($this->mentionedUsers, SORT_REGULAR);
		}

		return $this;
	}


	/**
	 * Fetches each user's user_name user_id and user_email from `#user` table
	 *      and stores as multi-dimensional indexed array
	 *
	 * @return $this
	 */
	private function fetchEachUserDetails()
	{
		// todo: check if self::mentionsUserNames is an array

		foreach ($this->mentionedUsers as $key => $value) {
			$this->mentionedUsers[$key] = $this->getUserData($value);
		}

		return $this;
	}


	/**
	 * Parses all mentions in user posted message and stores as a numeric array
	 *
	 * @param $message
	 *
	 * @return $this
	 */
	private function parseAllMentions($message)
	{
		$pattern = $this->obtainMatchRegEx();

		if (preg_match_all($pattern, $message, $matches) !== false) {

			$this->setMentions($matches[0]);
			$this->setMentionedUsers($matches[1]);

			return $this;
		} // todo: ?  do a pattern fallback match

		return $this;
	}


	/**
	 * Sets self::$mentions
	 *
	 * @param mixed $mentions
	 *
	 * @return MentionsNotificationTest
	 */
	public function setMentions($mentions)
	{
		$this->mentions = $mentions;

		return $this;
	}


	/**
	 * Sets self::$mentionsUserNames
	 *
	 * @param mixed $mentionedUsers
	 *
	 * @return MentionsNotificationTest
	 */
	public function setMentionedUsers($mentionedUsers)
	{
		$this->mentionedUsers = $mentionedUsers;

		return $this;
	}


	/**
	 * Sets self::$eventUserText
	 *
	 * @return $this
	 */
	private function setEventUserText()
	{
		if ($this->eventType === 'chatbox') {
			$this->eventUserText = $this->eventData['cmessage'];
		}

		if ($this->eventType === 'comment') {
			$this->eventUserText = $this->eventData['comment_comment'];
		}

		if ($this->eventType === 'forum') {
			$this->eventUserText = $this->eventData['post_entry'];
		}

		return $this;
	}


	/**
	 * @param mixed $eventData
	 *
	 * @return MentionsNotificationTest
	 */
	public function setEventData($eventData)
	{
		$this->eventData = $eventData;

		return $this;
	}


	/**
	 * @param mixed $eventType
	 *
	 * @return MentionsNotificationTest
	 */
	public function setEventType($eventType)
	{
		$this->eventType = $eventType;

		return $this;
	}


	/**
	 * Listen to, initializes and perform comment event notification.
	 *
	 * @param $data
	 */
	public function comment($data)
	{
		$this->init('comment', $data)->iterateAndNotify();
	}


	/**
	 * Listen to, initializes and perform forum event notification.
	 *
	 * @param $data
	 */
	public function forum($data)
	{
		$this->init('forum', $data)->iterateAndNotify();

		// debug
		$this->log($this, 'z-event-data');
	}


	/**
	 * Dispatches email to the mentioned user
	 *
	 * @param array $userData
	 *
	 * @return bool
	 *  true if success false if failure.
	 * @todo: make method fluent.
	 */
	private function sendMentionsEmail(array $userData)
	{
		$mail = e107::getEmail();

		$emailContent = [
			'email_subject' => $this->notificationSubject,
			'send_html'     => true,
			'email_body'    => $this->getEmailBody(),
			'template'      => 'default',
			'e107_header'   => $userData['user_id'],
			'extra_header'  => 'X-e107-Plugin : Mentions-Plugin-v',
		];

		// user email details
		$userEmail = $userData['user_email'];
		$userName = $userData['user_name'];

		// send email
		$emailSent = $mail->sendEmail($userEmail, $userName, $emailContent);

		if (true === $emailSent) {
			return $emailSent;
		}

		// Debug
		e107::getLog()
			->add('Mentions Email Sent Failure', $emailSent, E_LOG_WARNING,
				'MENTIONS_01', LOG_TO_ADMIN, ['user_name' => $userName]);

		return false;
	}


	/**
	 * Parses and returns email body
	 *
	 * @return string
	 */
	private function getEmailBody()
	{
		$bodyVars = [
			'MENTIONEE'    => $this->notificationRecipient,
			'MENTIONER'    => $this->notificationSender,
			'MENTION_TEXT' => $this->notificationMessage,
		];

		return e107::getParser()
			->simpleParse($this->emailTemplate(), $bodyVars);
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


}