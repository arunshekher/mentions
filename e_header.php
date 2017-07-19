<?php
if(!defined('e107_INIT'))
{
	exit;
}
if(USER_AREA && USER) {
	e107::css('mentions', 'js/jquery.atwho.css');
	e107::js('mentions', 'js/jquery.caret.js', 'jquery');
	e107::js('mentions', 'js/jquery.atwho.js', 'jquery');

	$pluginPath = e_PLUGIN_ABS. 'mentions/';
	e107::js('inline', "var mOpt = {'path': '$pluginPath'}");
	e107::js('mentions', 'js/mentions.js', 'jquery');
}