<?php

$extensionClassesPath = t3lib_extMgm::extPath('mfc_responsive_preview') . 'Classes/';

return array(
	'tx_mfcresponsivepreview_controller_backendcontroller' => $extensionClassesPath . 'Controller/BackendController.php',
	'tx_mfcresponsivepreview_utility_since60' => $extensionClassesPath . 'Utility/Since60.php',
	'tx_mfcresponsivepreview_utility_until60' => $extensionClassesPath . 'Utility/Until60.php',
);

?>