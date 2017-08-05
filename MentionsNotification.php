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
			if ($mentions) {
				$this->contentMessage = $_POST['cmessage'];
				$this->mentions = $mentions;
				$this->mentioner = USERNAME;
				$this->notifyAllMentioned();
				// todo: unset mentions and message
			}
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

		//$mentions = array_unique($this->mentions, SORT_STRING);

		foreach (array_unique($mentions, SORT_STRING) as $mention) {
			$this->mentioneeData = $this->getUserData($mention);

			// Debug
			$this->log(json_encode($this->mentioneeData), 'mentionee-data');

			// Email
			if ($this->email($this->mentioneeData)) {
				unset($this->mentioneeData);
			}

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
		$date = e107::getParser()->toDate(time(), 'long');
		$url = $this->getMentionContentLink();

		$body = [
			'USERNAME'     => $mentionee_name,
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
		unset($body, $text, $eml);

		if ($send) {
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
		$MENTIONS_NOTIFY = e107::getTemplate('mentions', 'mentions', 'notify');

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

}