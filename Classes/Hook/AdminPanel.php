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

if (t3lib_div::int_from_ver(TYPO3_version) < t3lib_div::int_from_ver('4.6.0') && !interface_exists('tslib_adminPanelHook')) {
	require_once(t3lib_extMgm::extPath('mfc_responsive_preview') . 'Classes/Interface/interface.tslib_adminPanelHook.php');
}

/**
 * Admin panel hook
 */
class Tx_MfcResponsivePreview_Hook_AdminPanel implements tslib_adminPanelHook {
	/**
	 * @var ux_tslib_AdminPanel
	 */
	protected $adminPanel = NULL;

	/**
	 * @param string $moduleContent
	 * @param tslib_AdminPanel $adminPanel
	 * @return string
	 */
	public function extendAdminPanel($moduleContent, tslib_AdminPanel $adminPanel) {
		$this->adminPanel = & $adminPanel;

		$moduleContent = '';
		if ($GLOBALS['BE_USER']->uc['TSFE_adminConfig']['display_top'] &&
				$this->adminPanel->isAdminModuleEnabled('responsive_preview')) {
			$moduleContent = $this->getResponsivePreviewModule();
		}

		return $moduleContent;
	}

	/**
	 * Creates the content for the "Responsive Preview" section ("module")
	 *
	 * @return string HTML content for the section.
	 */
	protected function getResponsivePreviewModule() {
		$out = $this->adminPanel->extGetHead('responsive_preview');

		if ($GLOBALS['BE_USER']->uc['TSFE_adminConfig']['display_responsive_preview']) {
			$this->adminPanel->extNeedUpdate = TRUE;

			$modTsConfig = t3lib_BEfunc::getModTSconfig($GLOBALS['TSFE']->id, 'mod.web_txmfcresponsivepreviewPreview');

			$sizeMenu = $this->getSelectBox(
				'TSFE_ADMIN_PANEL[responsive_preview_size]',
				$GLOBALS['BE_USER']->uc['TSFE_adminConfig']['responsive_preview_size'],
				$modTsConfig['properties']['size.']
			);

			$out .= $this->adminPanel->extGetItem(
				'responsive_preview_size',
				'',
				$sizeMenu
			);
		}

		return $out;
	}

	/**
	 * Returns a selector box "size menu" for a module
	 *
	 * @param string $elementName it the form elements name
	 * @param string $currentValue is the value to be selected currently.
	 * @param array $menuItems is an array with the menu items for the selector box
	 * @return string HTML code for selector box
	 */
	public function getSelectBox($elementName, $currentValue, $menuItems) {
		$result = '';

		if (is_array($menuItems)) {
			$options = array();
			foreach ($menuItems as $value => $label) {
				$options[] = '<option value="' . htmlspecialchars($value) . '"' .
						(!strcmp($currentValue, $value) ? ' selected="selected"' : '') . '>' .
						t3lib_div::deHSCentities(htmlspecialchars($label)) .
					'</option>';
			}

			if (count($options)) {
				$result = '
					<!-- Size Menu of module -->
					<select name="' . $elementName . '">' . implode('', $options) . '</select>';
			}
		}

		return $result;
	}
}

?>