<?php


class MentionsNotification extends Mentions
{
	protected $mentions;
	protected $mentioner;
	protected $mentioneeData;

	protected $contentType;
	protected $contentMessage;
	protected $contentId;
	protected $contentUrl;


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
			$this->chatboxMentionsNotify();
			e107::getEvent()->register('user_comment_posted', array('MentionsNotification', 'commentsMentionsNotify'));
			e107::getEvent()->register('user_forum_topic_created', array('MentionsNotification', 'forumsMentionsNotify'));
			e107::getEvent()->register('user_forum_post_created', array('MentionsNotification', 'forumsMentionsNotify'));
		}
	}


	/**
	 *
	 */
	private function chatboxMentionsNotify()
	{
		if ($_POST['chat_submit'] && $_POST['cmessage'] !== '') {
			// Debug
			// $this->log(json_encode([USERNAME, $_POST['cmessage']]));
			$mentions = $this->getAllMentions($_POST['cmessage']);
			if ($mentions) { // todo: logic to check if array
				$this->mentions = $mentions;
				$this->mentioner = USERNAME;
				//$this->contentMessage = $_POST['cmessage'];
				$this->notifyAllMentioned();
				// todo: unset mentions and message
			}
		}
	}


	/**
	 * Comments mentions notify
	 * @param $data
	 */
	public function commentsMentionsNotify($data)
	{
		//$this->log(json_encode($data), 'comments-trigger-data');
		$mentions = $this->getAllMentions($data['comment_comment']);
		if ($mentions) {
			$this->mentions = $mentions;
			$this->mentioner = $data['comment_author_name'];
			$this->notifyAllMentioned();
		}
	}


	/**
	 * Forum mentions notify
	 * @param $data
	 */
	public function forumsMentionsNotify($data)
	{
		//$this->log(json_encode($data), 'forums-trigger-data');
		$mentions = $this->getAllMentions($data['post_entry']);
		if ($mentions) {
			$this->mentions = $mentions;
			$this->mentioner = USERNAME;
			$this->notifyAllMentioned();
		}
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
	 * Notify all mentionees in a post event
	 */
	private function notifyAllMentioned()
	{
		$mentions = $this->mentions;

		if (null === $mentions || $mentions !== (array)$mentions) {
			return;
		}

		foreach (array_unique($mentions, SORT_STRING) as $mention) {

			if (USERNAME === $this->stripAtFrom($mention)) {
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

		$body = [
			'USERNAME'     => $mentionee_name,// todo: channge to MENTIONEE
			'DATE'         => $date,
			'SITENAME'     => SITENAME,
			'MENTIONER'    => $mentioner,
			'CONTENT_TYPE' => $content,
			'URL'          => $url,
		];

		$text = e107::getParser()->simpleParse($MENTIONS_NOTIFY, $body);

		$eml = [];
		$eml['email_subject'] = 'You were mentioned by ' . $mentioner;
		$eml['send_html'] = true;
		$eml['email_body'] = $text;
		$eml['template'] = 'default';
		$eml['e107_header'] = $userData['user_id'];

		$send =
			$mail->sendEmail($userData['user_email'], $userData['user_name'],
				$eml);

		if ($send) {
			unset($body, $text, $eml);

			return true;
		} else {
			return false;
		}

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
			<p>Hello {USERNAME}</p>
			<p>{MENTIONER} mentioned you in a {CONTENT_TYPE} at {SITENAME} on {DATE}.</p>
			<p>Please follow the {URL} to view.</p>
			</div>';

		}

		return $MENTIONS_NOTIFY;
	}


	/**
	 * todo: develop this stub
	 *
	 * @return string
	 */
	private function getContentType()
	{
		return '--CONTENT_TYPE---';
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
