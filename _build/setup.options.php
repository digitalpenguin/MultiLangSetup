<?php
/**
 * MultiLangSetup setup options
 *
 * @package TutorSeekCore
 * @subpackage build
 */
$package = 'MultiLangSetup';
$output = [];
$output[] = '<style type="text/css">
                #modx-setupoptions-panel { display: none; }
                form.x-panel-body.inline-form.x-panel-body-noheader.x-form {padding-top:0 !important; margin-bottom:30px !important;}
            </style>';

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
        $output[] = '<h2>Multiple Language Setup</h2>';
        $output[] = '<p>Please enter comma-separated language keys. These will be used to generate language contexts.<br>
                    Example: <em>en,zh,ja</em></p>';
        $output[] = '<p>The first one will be considered by Babel and LangRouter to be the default and will be assigned to the <em>web</em> context.</p>';
        $output[] = '<label for="lang-keys">Language keys</label>
                    <input type="text" placeholder="Example: en,zh,ja" name="lang_keys" id="lang-keys"><br>';

        /*$output[] = '<label for="site-name">Site Name</label>
                    <input type="text" placeholder="Example: My Great New Website" name="site_name" id="site-name">';
        $output[] = '<p>The site name you enter will be created on all contexts ready to be changed into the appropriate language.</p>';
        */
        $output[] = '<label for="name-space">Namespace</label>
                    <input type="text" placeholder="Example: mygreatnewwebsite" name="name_space" id="name-space">';
        $output[] = '<p>The namespace will be used for your lexicon directory. Put something unique, all in lowercase, with no spaces.</p>';

        /*$output[] = '<label for="overwrite">Add/overwrite Home resources and BaseTemplate.</label>
                    <input type="checkbox" value="1" name="overwrite" id="overwrite" checked>';
        $output[] = '<p>Warning: Leaving this checked will overwrite resource ID:1 and template ID:1 and add relative resources into each context.</p>';
*/
        break;
    case xPDOTransport::ACTION_UPGRADE:
    case xPDOTransport::ACTION_UNINSTALL:
        break;
}

return implode('<br />', $output);