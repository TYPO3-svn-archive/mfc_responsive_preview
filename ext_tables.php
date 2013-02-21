<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

t3lib_extMgm::addPageTSConfig('
mod.web_txmfcresponsivepreviewPreview {
	size {
		full = Full windowed
		768_1024 = 768 x 1024
		640_1136 = 640 x 1136 (Apple iPhone 5)
		640_960 = 640 x 960 (Apple iPhone 4)
		600_800 = 600 x 800 (Amazon Kindle 2 & 3)
		480_800 = 480 x 800 (Samsung Galaxy S1 & S2 Nokia Lumia 800 & 900)
		360_640 = 360 x 640 (Nokia N8)
		360_480 = 360 x 480 (BlackBerry Storm)
		320_480 = 320 x 480 (Apple iPhone 3G, Blackberry Bold 9000)
		240_320 = 240 x 320 (BlackBerry Curve, Nokia E71)
		176_220 = 176 x 220 (Siemens E81)
	}
}
');

if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addModule(
		'web',
		'txmfcresponsivepreviewPreview',
		'after:layout',
		t3lib_extMgm::extPath($_EXTKEY) . 'Classes/ModulePreview/'
	);
}

?>