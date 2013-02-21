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
 * Utility to get target url until TYPO3 6.0
 */
class Tx_MfcResponsivePreview_Utility_Until60 {
	/**
	 * @var array
	 */
	protected $pageRow;

	/**
	 * @param $pageRow
	 * @return Tx_MfcResponsivePreview_Utility_Until60
	 */
	public function __construct($pageRow) {
		$this->pageRow = & $pageRow;
	}

	/**
	 * Determine the url to view
	 *
	 * @return string
	 */
	public function getTargetUrl() {
		$pageIdToShow = intval(t3lib_div::_GP('id'));
		$adminCommand = $this->getAdminCommand();
		$domainName = $this->getDomainName($pageIdToShow);

		// Mount point overlay: Set new target page id and mp parameter
		/** @var $sysPage t3lib_pageSelect */
		$sysPage = t3lib_div::makeInstance('t3lib_pageSelect');
		$sysPage->init(FALSE);
		$mountPointMpParameter = '';
		$finalPageIdToShow = $pageIdToShow;
		$mountPointInformation = $sysPage->getMountPointInfo($pageIdToShow);
		if ($mountPointInformation && $mountPointInformation['overlay']) {
			// New page id
			$finalPageIdToShow = $mountPointInformation['mount_pid'];
			$mountPointMpParameter = '&MP=' . $mountPointInformation['MPvar'];
		}

		// Modify relative path to protocol with host if domain record is given
		$protocolAndHost = $GLOBALS['BACK_PATH'] . '..';
		if ($domainName) {
			$protocol = 'http';
			$page = (array) $sysPage->getPage($finalPageIdToShow);
			if ($page['url_scheme'] == 2 || $page['url_scheme'] == 0 && t3lib_div::getIndpEnv('TYPO3_SSL')) {
				$protocol = 'https';
			}
			$protocolAndHost = $protocol . '://' . $domainName;
		}

		$url = $protocolAndHost . '/index.php?id=' . $finalPageIdToShow . $this->getTypeParameterIfSet($finalPageIdToShow) .
			$mountPointMpParameter . $adminCommand;
		return $url;
	}

	/**
	 * Get admin command
	 *
	 * @return string
	 */
	protected function getAdminCommand() {
		// The page will show only if there is a valid page
		// and if this page may be viewed by the user
		$addCommand = '';
		if (is_array($this->pageRow)) {
			$addCommand = '&ADMCMD_view=1&ADMCMD_editIcons=1' . t3lib_BEfunc::ADMCMD_previewCmds($this->pageRow);
		}
		return $addCommand;
	}

	/**
	 * With page TS config it is possible to force a specific type id
	 * via mod.web_view.type for a page id or a page tree. The method checks
	 * if a type is set for the given id and returns the additional GET string.
	 *
	 * @param integer $pageId
	 * @return string
	 */
	protected function getTypeParameterIfSet($pageId) {
		$typeParameter = '';
		$modTsConfig = t3lib_BEfunc::getModTSconfig($pageId, 'mod.web_view');
		$typeId = intval($modTsConfig['properties']['type']);
		if ($typeId > 0) {
			$typeParameter = '&type=' . $typeId;
		}
		return $typeParameter;
	}

	/**
	 * Get domain name for requested page id
	 *
	 * @param integer $pageId
	 * @return boolean|string Domain name if there is one, FALSE if not
	 */
	protected function getDomainName($pageId) {
		$domain = t3lib_BEfunc::firstDomainRecord(t3lib_BEfunc::BEgetRootLine($pageId));
		return $domain;
	}
}

?>