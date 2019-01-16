<?php
if (!defined('e107_INIT')) { exit; }

if (deftrue('BOOTSTRAP') && deftrue('FONTAWESOME')) {
	define('MENTIONS_GITHUB_ICON',
		e107::getParser()->toGlyph('fa-github-alt', ['size' => '2x']));
	define('MENTIONS_HEART_ICON', e107::getParser()->toGlyph('fa-heart'));
	define('MENTIONS_SMILE_ICON', e107::getParser()->toGlyph('fa-smile-o', ['size' => '2x']));
} else {
	define('MENTIONS_GITHUB_ICON', '&nbsp;');
	define('MENTIONS_HEART_ICON', '&nbsp;');
	define('MENTIONS_SMILE_ICON', ':)');
}

$PROJECT_MENU_TEMPLATE = '
<div style="text-align: center">
<img src="https://www.e107.space/projects/mentions/svg" alt="Mentions" width="128" height="128">
</div>
<ul class="list-unstyled">
	<li>
		<h5>' . MENTIONS_GITHUB_ICON . '&nbsp;' . LAN_MENTIONS_INFO_MENU_SUBTITLE . '</h5>
	</li>
	<li>
		<kbd style="word-wrap: break-word;font-size: x-small">
			<a href="http://github.com/arunshekher/mentions" target="_blank">http://github.com/arunshekher/mentions</a>
		</kbd>
	</li>
	<li>&nbsp;</li>
	<li class="text-center">
		<a class="github-button" href="https://github.com/arunshekher/mentions/subscription" data-icon="octicon-eye" aria-label="Watch arunshekher/mentions on GitHub">Watch</a>
		<a class="github-button" href="https://github.com/arunshekher/mentions" data-icon="octicon-star"  data-show-count="true" aria-label="Star arunshekher/mentions on GitHub">Star</a>
	</li>
	<li>
		<h5>' . LAN_MENTIONS_INFO_MENU_SUBTITLE_ISSUES . '</h5>
	</li>
	<li class="text-center">
		<a class="github-button" href="https://github.com/arunshekher/mentions/issues" data-icon="octicon-issue-opened" data-size="large" data-show-count="true" aria-label="Issue arunshekher/mentions on GitHub">Issue</a>
	</li>
	<li style="border-bottom: solid 1px dimgrey" class="divider">&nbsp;</li>
	<li>
		<h5>' . MENTIONS_HEART_ICON . '&nbsp;' . LAN_MENTIONS_INFO_MENU_SUBTITLE_DEV . '</h5>
	</li>
	<li>
		<p>
			<small>'.LAN_MENTIONS_INFO_MENU_SUPPORT_DEV_TEXT.'</small>
		</p>
	</li>
	<li class="text-center">
		<script type="text/javascript" src="https://ko-fi.com/widgets/widget_2.js"></script>
		<script type="text/javascript">kofiwidget2.init("Buy Me a Coffee", "#46b798", "E1E4B43T");kofiwidget2.draw();</script>  
	</li>
	<li>&nbsp;</li>
	<li class="text-center" style="height: 50px">&nbsp;
		<script src="https://www.e107.space/bcwidget/coin.js"></script>
	<script>
	CoinWidget.go({
		wallet_address: "1FgXdXePYLGSsyDztWvZw99Ki3i2eVWbDe", 
		currency: "bitcoin", 
		counter: "hide", 
		alignment: "bc", 
		qrcode: true, 
		auto_show: false, 
		lbl_button: "Donate Bitcoins", 
		lbl_address: "Donate bitcoin to this address:"
	});
	</script>
	</li>
	<li class="text-center">
		<p style="padding-top:10px;">
			<small>'.LAN_MENTIONS_INFO_MENU_SUPPORT_DEV_TEXT_SIGN.'</small>
		</p>
		<p>' . MENTIONS_SMILE_ICON .'</p>
	</li>
</ul>
<script async defer src="https://buttons.github.io/buttons.js"></script>';