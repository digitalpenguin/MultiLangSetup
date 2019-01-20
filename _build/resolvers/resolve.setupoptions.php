<?php
/**
 * MultiLangSetup setup options resolver
 *
 * @package MultiLangSetup
 * @subpackage build
 */
$package = 'MultiLangSetup';
$success = true;
if ($object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            $modx =& $object->xpdo;

            if (isset($options['lang_keys'])) {
                $modx->log(MODX_LOG_LEVEL_INFO, $options['lang_keys'] . ' language keys have been input.');
                // Extract language keys and trim whitespace
                $lang_keys = array_map('trim', explode(',', $options['lang_keys']));

                // convert lang_keys to context keys
                $context_keys = $lang_keys;
                $context_keys[0] = 'web';

                // set Babel and LangRouter system settings.
                $ss = $modx->getObject('modSystemSetting',[
                    'key'   =>  'babel.contextKeys'
                ]);
                if(isset($ss)) {
                    $ss->set('value',implode(',',$context_keys));
                    $ss->save();
                }
                $ss = $modx->getObject('modSystemSetting',[
                    'key'   =>  'langrouter.contextKeys'
                ]);
                if(isset($ss)) {
                    $ss->set('value',implode(',',$context_keys));
                    $ss->save();
                }
                $ss = $modx->getObject('modSystemSetting',[
                    'key'   =>  'langrouter.contextDefault'
                ]);
                if(isset($ss)) {
                    $ss->set('value','web');
                    $ss->save();
                }

            } else {
                $modx->log(MODX_LOG_LEVEL_ERROR, 'Language keys are missing. Aborting...');
                return false;
            }

            if (!isset($options['name_space'])) {
                $modx->log(MODX_LOG_LEVEL_ERROR, 'Namespace is missing. Aborting...');
                return false;
            }

            // Set template to use for updated BaseTemplate
            $template = '<!doctype html>
<html lang="[[++cultureKey]]">
<head>
    <title>[[*pagetitle]] - [[++site_name]]</title>
    <base href="[[!++site_url]]" />
    <meta charset="[[++modx_charset]]" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
</head>
<body>
    <header>
        <p>[[%'.$options['name_space'].'.home? &topic=`default` &namespace=`'.$options['name_space'].'` &language=`[[++cultureKey]]`]]</p>
        <ul>
          [[BabelLinks? &showCurrent=`1`]]
        </ul>
    </header>
    <main>
        [[*content]]
    </main>
    <footer>

    </footer>
</body>
</html>';



            // *** 1. Change log_deprecated system setting to false. ***
            $sett = $modx->getObject('modSystemSetting', [
                'key' => 'log_deprecated'
            ]);
            if(isset($sett)) {
                $sett->set('value', 0);
                $modx->log(MODX_LOG_LEVEL_INFO, 'Setting log_deprecated system setting to false.');
                $success = $sett->save();
            }
            unset($sett);
            if(!$success) {
                $modx->log(MODX_LOG_LEVEL_ERROR, 'There was a problem setting log_deprecated system setting to false.');
                return false;
            }

            // *** 2. Set friendly_urls to true. ***
            $sett = $modx->getObject('modSystemSetting', [
                'key' => 'friendly_urls'
            ]);
            $sett->set('value',1);
            $modx->log(MODX_LOG_LEVEL_INFO,'Setting friendly_urls system setting to true.');
            $success = $sett->save();
            unset($sett);
            if(!$success) {
                $modx->log(MODX_LOG_LEVEL_ERROR, 'There was a problem setting friendly_urls system setting to true.');
                return false;
            }

            // *** 3. Set use_alias_path to true. ***
            $sett = $modx->getObject('modSystemSetting', [
                'key' => 'use_alias_path'
            ]);
            $sett->set('value',1);
            $modx->log(MODX_LOG_LEVEL_INFO,'Setting use_alias_path system setting to true.');
            $success = $sett->save();
            unset($sett);
            if(!$success) {
                $modx->log(MODX_LOG_LEVEL_ERROR, 'There was a problem setting use_alias_path system setting to true.');
                return false;
            }

            // *** 4. Set site_name. ***
            /*if (isset($options['site_name'])) {
                $sett = $modx->getObject('modSystemSetting', [
                    'key' => 'site_name'
                ]);
                $sett->set('value', $options['site_name']);
                $modx->log(MODX_LOG_LEVEL_INFO, 'Setting site_name system setting to '.$options['site_name']);
                $success = $sett->save();
                unset($sett);
                if (!$success) {
                    $modx->log(MODX_LOG_LEVEL_ERROR, 'There was a problem setting site_name system setting to '.$options['site_name']);
                    return false;
                }
            }*/

            // *** 5. Create namespace and lexicon directory ***
            if (isset($options['name_space'])) {
                $sett = $modx->getObject('modNamespace', [
                    'name' => $options['name_space']
                ]);
                if(!isset($sett)) {
                    $sett = $modx->newObject('modNamespace');
                    $sett->set('name',$options['name_space']);
                }
                $sett->set('path', '{core_path}components/'.$options['name_space'].'/');
                $sett->set('assets_path', '{assets_path}components/'.$options['name_space'].'/');
                $modx->log(MODX_LOG_LEVEL_INFO, 'Creating '.$options['name_space'].' namespace...');
                $success = $sett->save();
                unset($sett);
                if (!$success) {
                    $modx->log(MODX_LOG_LEVEL_ERROR, 'There was a problem creating '.$options['name_space'].' namespace.');
                    return false;
                }

                // Make sure we've got some language keys
                if(isset($lang_keys)) {
                    foreach($lang_keys as $key) {
                        // Create directory
                        if (!file_exists(MODX_CORE_PATH . 'components/' . $options['name_space'] . '/lexicon/'.$key.'/default.inc.php')) {
                            mkdir(MODX_CORE_PATH . 'components/' . $options['name_space'] . '/lexicon/'.$key, 0775, true);

                            // Add initial data to lexicon file.
                            $file_contents = "<?php
\$_lang['{$options['name_space']}.home'] = 'This is the {$key} home page.';";
                            file_put_contents(MODX_CORE_PATH.'components/'.$options['name_space'].'/lexicon/'.$key.'/default.inc.php',$file_contents);
                            $modx->log(MODX_LOG_LEVEL_INFO, MODX_CORE_PATH . 'components/' . $options['name_space'] . '/lexicon/'.$key.'/default.inc.php lexicon created.');
                        } else {
                            $modx->log(MODX_LOG_LEVEL_INFO, MODX_CORE_PATH . 'components/' . $options['name_space'] . '/lexicon/'.$key.'/default.inc.php already exists. Skipping...');
                        }
                    }
                }
            }

            // *** 6. Rename .htaccess file ***
            if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/.htaccess') && file_exists($_SERVER['DOCUMENT_ROOT'].'/ht.access')) {
                rename($_SERVER['DOCUMENT_ROOT'].'/ht.access', $_SERVER['DOCUMENT_ROOT'].'/.htaccess');
                $modx->log(MODX_LOG_LEVEL_INFO, 'Renamed root ht.access file to .htaccess for friendly URLs if needed...');
            }


            // *** 7. Overwrite BaseTemplate ***
            $baseTemplate = $modx->getObject('modTemplate',[
                'id'    =>  1
            ]);
            if(isset($baseTemplate)) {
                $baseTemplate->set('content',$template);
                $baseTemplate->save();
                $modx->log(MODX_LOG_LEVEL_INFO, 'BaseTemplate (1) default content overwritten...');
            }

            // *** 8. Create extra contexts for language keys ***
            if(isset($lang_keys)) {
                $i = 0;
                $start_ids = []; // keep track of created resources
                $error_ids = [];
                $unauthorized_ids = [];
                $babel_start = ''; // build site_start Babel string
                $babel_error = ''; // build error_page Babel string
                $babel_unauthorized = ''; // build unauthorized_page Babel string

                foreach($lang_keys as $lang_key) {
                    // We're using the first lang_key as the default context so it's applied to the existing web context.
                    if($i === 0) {
                        $context = $modx->getObject('modContext',[
                            'key'   => 'web'
                        ]);
                    } else {
                        // Create new contexts unless they already exist for the rest of the lang_key values
                        $context = $modx->getObject('modContext',[
                            'key'   => $lang_key
                        ]);
                        if(!isset($context)) {
                            $context = $modx->newObject('modContext');
                            $context->set('key',$lang_key);
                        }
                    }

                    $context->set('name',$lang_key);
                    $context->set('description','This is the '.$lang_key.' context.');
                    $context->set('rank',$i);
                    $context->save();
                    $modx->log(MODX_LOG_LEVEL_INFO, $lang_key.' context added...');

                    // Create gateway & language context settings
                    createContextSetting($modx,$context->get('key'),'base_url','gateway','/');
                    createContextSetting($modx,$context->get('key'),'site_url','gateway','{url_scheme}{http_host}{base_url}{cultureKey}/');
                    createContextSetting($modx,$context->get('key'),'cultureKey','language',$lang_key);


                    /*
                     * Create site_start resource and context setting.
                     */
                    $home_resource_values = [
                        'ctx_key'   =>  $context->get('key'),
                        'pagetitle' =>  'Home',
                        'template'  =>  1,
                        'alias'     =>  'index',
                        'published' =>  1,
                        'content'   =>  ''
                    ];
                    // check if web context so we know to overwrite home resource
                    if ($i === 0) {
                        $site_start_resource = overwriteResource($modx,'site_start',$home_resource_values);
                        // Create alternative site-start resources for other contexts
                    } else {
                        $site_start_resource = createResource($modx,$home_resource_values);
                    }
                    if($site_start_resource) {
                        $modx->log(MODX_LOG_LEVEL_INFO, $site_start_resource->get('pagetitle').' resource added to '.$site_start_resource->get('context_key').' context...');
                    }
                    // set up the correct format for Babel e.g. web:1,en:2
                    if(strlen($babel_start) > 0) {
                        $babel_start .= ';'.$lang_key . ':' . $site_start_resource->get('id');
                    } else {
                        $babel_start .= 'web:' . $site_start_resource->get('id');
                    }
                    // store resource id
                    $start_ids[] = $site_start_resource->get('id');
                    createContextSetting($modx,$context->get('key'),'site_start','site',$site_start_resource->get('id'));
                    $modx->log(MODX_LOG_LEVEL_INFO, 'Context setting created for '. $site_start_resource->get('pagetitle').' resource.');


                    /*
                     * Create error_page resource and context setting.
                     */
                    $error_resource_values = [
                        'ctx_key'   =>  $context->get('key'),
                        'pagetitle' =>  'Page Not Found',
                        'template'  =>  1,
                        'alias'     =>  'page-not-found',
                        'published' =>  1,
                        'content'   =>  ''
                    ];
                    $error_page_resource = createResource($modx,$error_resource_values);
                    if($error_page_resource) {
                        $modx->log(MODX_LOG_LEVEL_INFO, $error_page_resource->get('pagetitle').' resource added to '.$error_page_resource->get('context_key').' context...');
                    }
                    // set up the correct format for Babel e.g. web:1,en:2
                    if(strlen($babel_error) > 0) {
                        $babel_error .= ';'.$lang_key . ':' . $error_page_resource->get('id');
                    } else {
                        $babel_error .= 'web:' . $error_page_resource->get('id');
                    }
                    // store resource id
                    $error_ids[] = $error_page_resource->get('id');
                    createContextSetting($modx,$context->get('key'),'error_page','site',$error_page_resource->get('id'));
                    $modx->log(MODX_LOG_LEVEL_INFO, 'Context setting created for '. $error_page_resource->get('pagetitle').' resource.');

                    /*
                     * Create unauthorized_page resource and context setting.
                     */
                    $unauthorized_resource_values = [
                        'ctx_key'   =>  $context->get('key'),
                        'pagetitle' =>  'Unauthorized',
                        'template'  =>  1,
                        'alias'     =>  'unauthorized',
                        'published' =>  1,
                        'content'   =>  ''
                    ];
                    $unauthorized_page_resource = createResource($modx,$unauthorized_resource_values);
                    if($unauthorized_page_resource) {
                        $modx->log(MODX_LOG_LEVEL_INFO, $unauthorized_page_resource->get('pagetitle').' resource added to '.$unauthorized_page_resource->get('context_key').' context...');
                    }
                    // set up the correct format for Babel e.g. web:1,en:2
                    if(strlen($babel_unauthorized) > 0) {
                        $babel_unauthorized .= ';'.$lang_key . ':' . $unauthorized_page_resource->get('id');
                    } else {
                        $babel_unauthorized .= 'web:' . $unauthorized_page_resource->get('id');
                    }
                    // store resource id
                    $unauthorized_ids[] = $unauthorized_page_resource->get('id');
                    createContextSetting($modx,$context->get('key'),'unauthorized_page','site',$unauthorized_page_resource->get('id'));
                    $modx->log(MODX_LOG_LEVEL_INFO, 'Context setting created for '. $unauthorized_page_resource->get('pagetitle').' resource.');


                    $i++;
                }

                // Set Babel links on each resource.
                setBabelLinks($modx,$start_ids,$babel_start);
                setBabelLinks($modx,$error_ids,$babel_error);
                setBabelLinks($modx,$unauthorized_ids,$babel_unauthorized);
                $modx->log(MODX_LOG_LEVEL_INFO, 'Babel links generated for all newly created resources.');
            }


            break;
        case xPDOTransport::ACTION_UPGRADE:
        case xPDOTransport::ACTION_UNINSTALL:
            $success = true;
            break;
    }
}
return $success;


function setBabelLinks(&$modx,$resource_ids,$babel_string) {
    //Get id of Babel TV to use instead of 1
    $tv = $modx->getObject('modTemplateVar',[
        'name'  =>  $modx->getOption('babel.babelTvName')
    ]);
    if(isset($tv)) {
        // Create Babel Links
        foreach($resource_ids as $resource_id) {
            $link = $modx->getObject('modTemplateVarResource',[
                'contentid' =>  $resource_id
            ]);
            if(!isset($link)) {
                $link = $modx->newObject('modTemplateVarResource');
            }
            $link->set('contentid',$resource_id);
            $link->set('tmplvarid',$tv->get('id'));
            $link->set('value',$babel_string);
            $link->save();
        }
    }
}


/**
 * CreateContextSetting
 *
 * Creates/Updates a context setting.
 *
 * @param $modx
 * @param $ctx_key
 * @param $key
 * @param $area
 * @param $value
 * @return mixed
 */
function createContextSetting(&$modx,$ctx_key,$key,$area,$value) {
    $cs = $modx->getObject('modContextSetting',[
        'context_key'   =>  $ctx_key,
        'key'           =>  $key
    ]);
    if(!isset($cs)) {
        $cs = $modx->newObject('modContextSetting');
        $cs->set('context_key',$ctx_key);
        $cs->set('key',$key);
    }
    $cs->set('area',$area);
    $cs->set('value',$value);
    $modx->log(MODX_LOG_LEVEL_INFO, 'Adding '.$key.' context setting to '.$ctx_key.' context...');
    return $cs->save();
}

/**
 * Creates a new resource ready to be added to a context.
 * @param $modx
 * @param $resource_values
 * @return mixed
 */
function createResource(&$modx,$resource_values) {

    $resource = $modx->newObject('modResource');
    if(isset($resource)) {
        $resource->set('context_key',$resource_values['ctx_key']);
        $resource->set('template', $resource_values['template']);
        $resource->set('pagetitle', $resource_values['pagetitle']);
        $resource->set('content', $resource_values['content']);
        $resource->set('alias', $resource_values['alias']);
        $resource->set('published', $resource_values['published']);
        $resource->save();
        return $resource;
    }
    return false;
}

/**
 * Overwrites
 * @param $modx
 * @param $setting_name
 * @param $resource_values
 * @return bool
 */
function overwriteResource(&$modx,$setting_name,$resource_values) {
    // Grab the site_start resource from the default context
    $resource = $modx->getObject('modResource', [
        'id' => 1
    ]);

    if (!isset($resource)) {
        $resource = $modx->getObject('modResource',[
            'id'    =>  $modx->getOption($setting_name)
        ]);
    }
    if (isset($resource)) {
        $resource->set('longtitle', '');
        $resource->set('template', $resource_values['template']);
        $resource->set('pagetitle', $resource_values['pagetitle']);
        $resource->set('content', $resource_values['content']);
        $resource->set('alias', $resource_values['alias']);
        $resource->set('published', $resource_values['published']);
        $resource->save();
        return $resource;
    }
    return false;
}