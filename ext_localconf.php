<?php

if (t3lib_div::int_from_ver(TYPO3_version) >= 4005000 and t3lib_div::int_from_ver(TYPO3_version) < 4006000) {
	$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['typo3/sysext/cms/tslib/class.tslib_adminpanel.php'] =
		t3lib_extMgm::extPath('mfc_heatmap') . 'Classes/Xclass/AdminPanel.php';
}

	// Override locallang file of admin panel to get own elements into it
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:lang/locallang_tsfe.php'][$_EXTKEY] =
	'EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_adminpanel.php']['extendAdminPanel'][$_EXTKEY] =
	'EXT:mfc_responsive_preview/Classes/Hook/AdminPanel.php:Tx_MfcResponsivePreview_Hook_AdminPanel';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc'][$_EXTKEY] =
	'EXT:mfc_responsive_preview/Classes/Hook/Tsfe.php:Tx_MfcResponsivePreview_Hook_Tsfe->configArrayPostProc';

?>