<?php
require_once dirname(__FILE__) . '/model/multilangsetup/multilangsetup.class.php';
/**
 * @package multilangsetup
 */

abstract class MultiLangSetupBaseManagerController extends modExtraManagerController {
    /** @var MultiLangSetup $multilangsetup */
    public $multilangsetup;
    public function initialize() {
        $this->multilangsetup = new MultiLangSetup($this->modx);

        $this->addCss($this->multilangsetup->getOption('cssUrl').'mgr.css');
        $this->addJavascript($this->multilangsetup->getOption('jsUrl').'mgr/multilangsetup.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            MultiLangSetup.config = '.$this->modx->toJSON($this->multilangsetup->options).';
            MultiLangSetup.config.connector_url = "'.$this->multilangsetup->getOption('connectorUrl').'";
        });
        </script>');
        
        parent::initialize();
    }
    public function getLanguageTopics() {
        return array('multilangsetup:default');
    }
    public function checkPermissions() { return true;}
}