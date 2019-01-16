<?php
require_once dirname(dirname(__FILE__)) . '/index.class.php';
/**
 * Loads the home page.
 *
 * @package multilangsetup
 * @subpackage controllers
 */
class MultiLangSetupHomeManagerController extends MultiLangSetupBaseManagerController {
    public function process(array $scriptProperties = array()) {

    }
    public function getPageTitle() { return $this->modx->lexicon('multilangsetup'); }
    public function loadCustomCssJs() {
    
    }

}