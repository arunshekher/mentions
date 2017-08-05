<?php
require_once __DIR__ . '/MentionsNotification.php';

// chatbox notification
MentionsNotification::execute();

// For the rest event trigger works
//e107::getEvent()->register('user_comment_posted', array('MentionsNotification', 'commentsMentionsNotify'));
//e107::getEvent()->register('user_forum_topic_created', array('MentionsNotification', 'forumsMentionsNotify'));
//e107::getEvent()->register('user_forum_post_created', array('MentionsNotification', 'forumsMentionsNotify'));