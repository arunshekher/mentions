<?php
require_once __DIR__ . '/MentionsContentEmails.php';
require_once __DIR__ . '/MentionsContentLinks.php';


class MentionsNotificationTest extends Mentions
{
	private $notificationSender = USERNAME;

	// todo: new - instead of self::$mentioner
	private $notificationSenderId = USERID;

	private $mentions;
	private $mentionedUsers;

	// todo: new trial - instead of self::$mentionsUserNames;
	private $usersData;

	private $notificationSubject;
	private $notificationMessage;
	private $notificationRecipient;

	private $eventType;
	private $eventData;
	private $eventUserMessage; // todo: post message


	public function chatbox($data)
	{
		$this->setEventType('chatbox')->setEventData($data)
			->parseAllMentions($data['cmessage'])->fetchEachUserDetails()
			->filterDuplicates()->copyMentionsDetails()
			->traverseAllMentionsAndNotify();
	}


	private function traverseAllMentionsAndNotify()
	{
		for ($i =
			     0; $i < $this->prefs['max_emails']; $i++) { // todo: rename this pref to 'max_emails_per_post'

			// todo: utilize e107::user($userId);  to fetch user details perhaps
			//  the fetching of user data can be done in parse-mentions OR filtering methods.

			// skipping e-mailing mentioner
			if ($this->isSenderRecipient($this->mentionedUsers[$i]['user_id'])) {
				continue;
			}

			// check & send email
			if ($this->hasEmailableData($this->mentionedUsers[$i])) {


				$this->setNotificationRecipient($this->mentionedUsers[$i]['user_name'])
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
		$this->log($mail, 'z-content-email-object');

		return $mail->generate();
	}


	private function copyMentionsDetails()
	{
		$this->usersData = $this->mentionedUsers;

		return $this;
	}


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


	public function comment($data)
	{
		$this->setEventType('comment')->setEventData($data)
			->parseAllMentions($data['comment_comment'])->fetchEachUserDetails()
			->filterDuplicates()->copyMentionsDetails()
			->traverseAllMentionsAndNotify();

	}


	public function forum($data)
	{
		$this->setEventType('forum')->setEventData($data)
			->parseAllMentions($data['post_entry'])->fetchEachUserDetails()
			->filterDuplicates()->copyMentionsDetails()
			->traverseAllMentionsAndNotify();

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
			'email_subject' => $this->emailSubject(),
			// todo: this depends only on event data so can be called earlier
			//  in each event trigger methods.
			'send_html'     => true,
			'email_body'    => $this->emailBody(),
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
	 * Fetches the subject line for email based on plugin preference
	 *
	 * @return string
	 *  Email subject line.
	 */
	public function emailSubject()
	{
		$subjectLine = trim($this->prefs['email_subject_line']);

		if (null !== $subjectLine && $subjectLine !== '') {
			return str_replace('{MENTIONER}', $this->notificationSender,
				$subjectLine);
		}

		return LAN_MENTIONS_EMAIL_SUBJECTLINE . $this->notificationSender;
	}


	/**
	 * Parses and returns email body
	 *
	 * @return string
	 */
	private function emailBody()
	{
		$bodyVars = [
			'MENTIONEE'    => $this->notificationRecipient,
			'MENTIONER'    => $this->notificationSender,
			'MENTION_TEXT' => $this->acquireEmailMessage(),
			// todo: this depends only on event data so can be called earlier
			//  in each event trigger methods to be stored in a private property.
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