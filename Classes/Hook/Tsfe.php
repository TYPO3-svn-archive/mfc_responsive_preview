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

/**
 * Tsfe hook
 */
class Tx_MfcResponsivePreview_Hook_Tsfe {
	/**
	 * @var tslib_fe
	 */
	protected $parentObject;

	/**
	 * @param array $parameter
	 * @param tslib_fe $parentObject
	 * @return void
	 */
	public function configArrayPostProc($parameter, $parentObject) {
		$this->parentObject = & $parentObject;

		$request = t3lib_div::_GP('TSFE_ADMIN_PANEL');
		if (isset($GLOBALS['BE_USER']) && $GLOBALS['BE_USER']->uc['TSFE_adminConfig']['responsive_preview_size'] != 'full') {
			if ($request['action'] != 'responsive_preview') {
				$this->parentObject->set_no_cache();

				$template = $this->getHtmlTemplate('EXT:mfc_responsive_preview/Resources/Private/Templates/Hook/Tsfe.html');

				$urlParts = parse_url(t3lib_div::getIndpEnv('REQUEST_URI'));

				$markers = array(
					'TITLE_PREVIEW' => $this->parentObject->sL(
						'LLL:EXT:mfc_responsive_preview/Resources/Private/Language/locallang.xml:tsfe_hook_title'
					),
					'PAGE_ID' => $GLOBALS['TSFE']->id,
					'QUERY' => $urlParts['query'],
				);

				$markers = $this->addFrameSizeMarkers($markers);
				$markers = $this->addAdminPanelMarker($markers);

				echo t3lib_parsehtml::substituteMarkerArray($template, $markers, '###|###');

				exit;
			} else {
				$GLOBALS['TSFE']->config['config']['admPanel'] = 0;
			}
		}
	}

	/**
	 * @param array $markers
	 * @return array
	 */
	protected function addAdminPanelMarker(array $markers) {
		$markers['ADMINPANEL_HEADER'] = '';
		$markers['ADMINPANEL'] = '';

		/** @var $user t3lib_tsfeBeUserAuth */
		$user = $GLOBALS['BE_USER'];
		if (is_object($user) && $user->isAdminPanelVisible() && $this->parentObject->beUserLogin) {
			$markers['ADMINPANEL_HEADER'] = $user->adminPanel->getAdminPanelHeaderData();
			$markers['ADMINPANEL'] = $user->displayAdminPanel();
		}
		return $markers;
	}

	/**
	 * @param array $markers
	 * @return array
	 */
	protected function addFrameSizeMarkers(array $markers) {
		$selectedSize = $GLOBALS['BE_USER']->uc['TSFE_adminConfig']['responsive_preview_size'];
		if ($selectedSize != 'full') {
			$widthAndHeight = t3lib_div::intExplode('_', $selectedSize, TRUE, 2);

			$markers['WIDTH'] = $widthAndHeight[0] . 'px';
			$markers['HEIGHT'] = $widthAndHeight[1] . 'px';
		} else {
			$markers['WIDTH'] = '100%';
			$markers['HEIGHT'] = '100%';
		}

		$markers['TITLE_PREVIEW'] = sprintf($this->parentObject->sL(
			'LLL:EXT:mfc_responsive_preview/Resources/Private/Language/locallang.xml:tsfe_hook_title'
		), str_replace('_', ' x ', $selectedSize));

		return $markers;
	}

	/**
	 * Function to load a HTML template file with markers. When calling from
	 * own extension, use syntax getHtmlTemplate('EXT:extkey/template.html')
	 *
	 * @param string $filename name, usually in the typo3/template/ directory
	 * @return string HTML of template
	 */
	protected function getHtmlTemplate($filename) {
		if ($GLOBALS['TBE_STYLES']['htmlTemplates'][$filename]) {
			$filename = $GLOBALS['TBE_STYLES']['htmlTemplates'][$filename];
		}

		if (t3lib_div::isFirstPartOfStr($filename, 'EXT:')) {
			$filename = t3lib_div::getFileAbsFileName($filename, TRUE, TRUE);
		} elseif (!t3lib_div::isAbsPath($filename)) {
			$filename = t3lib_div::resolveBackPath('./typo3/' . $filename);
		} elseif (!t3lib_div::isAllowedAbsPath($filename)) {
			$filename = '';
		}

		$htmlTemplate = '';
		if ($filename !== '') {
			$htmlTemplate = t3lib_div::getURL($filename);
		}

		return $htmlTemplate;
	}
}

?>