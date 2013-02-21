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
 * Backend Controller
 */
class Tx_MfcResponsivePreview_Controller_BackendController extends t3lib_SCbase {
	/**
	 * @var template
	 */
	public $doc;

	/**
	 * @var t3lib_db
	 */
	protected $database;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var t3lib_beUserAuth
	 */
	protected $user;

	/**
	 * @var array
	 */
	protected $pageRow;

	/**
	 * @var array
	 */
	protected $markers = array();

	/**
	 * @var array
	 */
	protected $docHeaderMarkers = array();


	/**
	 * @return void
	 */
	public function init() {
		$this->database = & $GLOBALS['TYPO3_DB'];
		$this->language = & $GLOBALS['LANG'];
		$this->user = & $GLOBALS['BE_USER'];

		parent::init();

		$this->pageRow = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
	}

	/**
	 * Main dispatching method
	 *
	 * @return void
	 */
	public function main() {
		$action = $this->getAction();
		switch ($action) {
			case 'frameset':
			case 'iframe':
			case 'select':
			case 'subframe':
				$this->initializeAction();
				break;
			default:
				$this->actionRedirectToPreview();
				break;
		}

		$actionMethodName = 'action' . ucfirst(strtolower($action));
		if (method_exists($this, $actionMethodName)) {
			call_user_func(array($this, $actionMethodName));
		}

		$this->postProcessAction();
	}

	/**
	 * @return void
	 */
	public function printContent() {
		if ($this->content) {
			echo $this->content;
		}
	}

	/**
	 * Initialize menu array
	 * Needs to exists in select and index to store values on select
	 *
	 * @return	void
	 */
	public function menuConfig() {
			// page/be_user TSconfig settings and blinding of menu-items
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id, 'mod.' . $this->MCONF['name']);

			// MENU-ITEMS:
		$this->MOD_MENU = array(
			'preview_size' => $this->modTSconfig['properties']['size.'],
		);

		$this->MOD_MENU['preview_size'] = t3lib_BEfunc::unsetMenuItems(
			$this->modTSconfig['preview_size'],
			$this->MOD_MENU['preview_size'],
			'menu.preview_size'
		);

			// Clean up settings
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name']);
	}


	/**
	 * @return string
	 */
	protected function getAction() {
		if (t3lib_div::int_from_ver(TYPO3_version) < 6000000) {
			$action = 'frameset';

			if (t3lib_div::_GP('SET')) {
				$action = 'subframe';
			}
		} else {
			$action = 'iframe';
		}

		if (t3lib_div::_GP('action')) {
			$action = (string) t3lib_div::_GP('action');
		}

		return $action;
	}

	/**
	 * @return void
	 */
	protected function initializeAction() {
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->doc->docType = 'xhtml_trans';
	}

	/**
	 * @return void
	 */
	protected function postProcessAction() {
		$this->content = $this->doc->startPage($this->language->getLL('title'));
		$this->content .= $this->doc->moduleBody($this->pageRow, $this->docHeaderMarkers, $this->markers);
		$this->content .= $this->doc->endPage();
		$this->content = $this->doc->insertStylesAndJS($this->content);
	}


	/**
	 * @return void
	 */
	protected function actionFrameset() {
		$this->doc->docType = 'xhtml_frames';
		$this->doc->setModuleTemplate('EXT:mfc_responsive_preview/Resources/Private/Templates/Preview/Frameset.html');

		$urlParts = parse_url(t3lib_div::getIndpEnv('REQUEST_URI'));

		$this->markers = array(
			'PAGE_ID' => $this->id,
			'QUERY' => $urlParts['query'],
		);
	}

	/**
	 * @return void
	 */
	protected function actionIframe() {
		$this->doc->setModuleTemplate('EXT:mfc_responsive_preview/Resources/Private/Templates/Preview/Iframe.html');

		$this->doc->JScode .= $this->doc->wrapScriptTags('
			if (top.fsMod) {
				top.fsMod.recentIds["web"] = ' . intval($this->id) . ';
			}
			if (top.fsMod) {
				top.fsMod.navFrameHighlightedID["web"] = "pages' . intval($this->id) . '_"+top.fsMod.currentBank; ' . intval($this->id) . ';
			}

			function jumpToUrl(URL, formEl) {
				window.document.location.href = URL;
			}
		');

		$this->addDocHeaders();
		$this->markers = $this->addFrameSizeMarkers($this->markers);
	}

	/**
	 * @return void
	 */
	protected function actionSelect() {
		$this->doc->setModuleTemplate('EXT:mfc_responsive_preview/Resources/Private/Templates/Preview/Select.html');

		$this->doc->inDocStylesArray['mfc_responsive_preview'] = '
			#typo3-docheader-row1 select { width: 250px; }
		';

		$this->doc->JScode.= $this->doc->wrapScriptTags('
			if (top.fsMod) {
				top.fsMod.recentIds["web"] = ' . intval($this->id) . ';
			}
			if (top.fsMod) {
				top.fsMod.navFrameHighlightedID["web"] = "pages' . intval($this->id) . '_"+top.fsMod.currentBank; ' . intval($this->id) . ';
			}

			function jumpToUrl(URL, formEl) {
				window.parent.document.getElementById("view_frame").contentWindow.location.href = URL;
			}
		');

		$this->addDocHeaders();
	}

	/**
	 * @return void
	 */
	protected function actionSubframe() {
		$this->doc->setModuleTemplate('EXT:mfc_responsive_preview/Resources/Private/Templates/Preview/Subframe.html');

		$urlParts = parse_url(
			preg_replace('@action=[^&]+@i', '',
				preg_replace('@&SET\[preview_size\]=.*@i', '',
					str_replace('show=1&', '', t3lib_div::getIndpEnv('REQUEST_URI'))
				)
			)
		);

		$this->markers = array(
			'ID' => $this->id,
			'QUERY' => $urlParts['query'] . '&action=redirect',
		);

		$this->markers = $this->addFrameSizeMarkers($this->markers);
	}

	/**
	 * @return void
	 */
	protected function actionRedirectToPreview() {
		t3lib_utility_Http::redirect($this->getTargetUrl());
	}


	/**
	 * @param array $markers
	 * @return array
	 */
	protected function addFrameSizeMarkers(array $markers) {
		$selectedSize = $this->MOD_SETTINGS['preview_size'];
		if (array_key_exists($selectedSize, $this->modTSconfig['properties']['size.']) && $selectedSize != 'full') {
			$widthAndHeight = t3lib_div::intExplode('_', $selectedSize, TRUE, 2);

			$markers['WIDTH'] = $widthAndHeight[0] . 'px';
			$markers['HEIGHT'] = $widthAndHeight[1] . 'px';
		} else {
			$markers['WIDTH'] = '100%';
			$markers['HEIGHT'] = '100%';
		}

		return $markers;
	}

	/**
	 * @return void
	 */
	protected function addDocHeaders() {
		$urlParts = parse_url(t3lib_div::getIndpEnv('REQUEST_URI'));

		$sizeMenu = t3lib_BEfunc::getFuncMenu(
			$this->id,
			'SET[preview_size]',
			$this->MOD_SETTINGS['preview_size'],
			$this->MOD_MENU['preview_size'],
			'index.php',
			''
		);

		$this->markers = array(
			'ID' => $this->id,
			'QUERY' => $urlParts['query'],
			'LABEL_SIZE_SELECT' => $this->language->getLL('label_size_select'),
			'SIZE_SELECT' => $sizeMenu,
		);

		$this->docHeaderMarkers['ICON_REFRESH'] = t3lib_iconWorks::getSpriteIcon('actions-system-refresh');
		$this->docHeaderMarkers['ICON_SHORTCUT'] = $this->doc->makeShortcutIcon('', '', $this->MCONF['name']);
		$this->docHeaderMarkers['ICON_SHOW'] = t3lib_iconWorks::getSpriteIcon('actions-document-view');
		$this->docHeaderMarkers['LINK_SHOW'] = $this->getTargetUrl();
	}


	/**
	 * @return string
	 */
	protected function getTargetUrl() {
		if (t3lib_div::int_from_ver(TYPO3_version) < 6000000) {
			$url = t3lib_div::makeInstance('Tx_MfcResponsivePreview_Utility_Until60', $this->pageRow)->getTargetUrl();
		} else {
			$url = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_MfcResponsivePreview_Utility_Since60', $this->pageRow)->getTargetUrl();
		}

		return $url;
	}
}

if (defined('TYPO3_MODE') &&
		$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mfc_responsive_preview/Classes/Controller/BackendController.php']) {
	include_once(
		$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mfc_responsive_preview/Classes/Controller/BackendController.php']
	);
}

?>