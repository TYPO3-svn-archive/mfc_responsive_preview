<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2012 - 2013 Sebastian Fischer <typo3@marketing-factory.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH . 'init.php');
require_once($BACK_PATH . 'template.php');
$GLOBALS['LANG']->includeLLFile('EXT:mfc_responsive_preview/Resources/Private/Language/locallang_mod.xml');

if (!(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_AJAX)) {
	/** @var $SOBE Tx_MfcResponsivePreview_Controller_BackendController */
	$SOBE = t3lib_div::makeInstance('Tx_MfcResponsivePreview_Controller_BackendController');
	$SOBE->init();

	foreach ($SOBE->include_once as $includeFile) {
		include_once($includeFile);
	}

	$SOBE->main();
	$SOBE->printContent();
}

?>