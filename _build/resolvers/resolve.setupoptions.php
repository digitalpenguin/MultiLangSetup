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

            if (!isset($options['site_name']) || !isset($options['name_space'])) {
                $modx->log(MODX_LOG_LEVEL_ERROR, 'Site name or namespace is missing. Aborting...');
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
            if (isset($options['site_name'])) {
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
            }

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
            if(isset($options['overwrite'])) {
                $baseTemplate = $modx->getObject('modTemplate',[
                    'id'    =>  1
                ]);
                if(isset($baseTemplate)) {
                    $baseTemplate->set('content',$template);
                    $baseTemplate->save();
                }
            }

            // *** 8. Create extra contexts for language keys ***
            if(isset($lang_keys)) {
                $i = 0;
                $resource_ids = []; // keep track of created resources
                $linkString = ''; // build Babel string

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

                    if(isset($options['overwrite'])) {

                        // Grab the site_start resource from the default context
                        if ($i === 0) {
                            $resource = $modx->getObject('modResource', [
                                'id' => 1
                            ]);
                            if (!isset($resource)) {
                                $resource = $modx->getObject('modResource',[
                                    'id'    =>  $modx->getOption('site_start')
                                ]);
                            }
                            if (isset($resource)) {
                                $resource->set('longtitle', '');
                            }
                        // Create alternative site-start resources for other contexts
                        } else {
                            $resource = $modx->newObject('modResource');
                            if(isset($resource)) {
                                $resource->set('context_key',$lang_key);

                            }
                        }
                        if (isset($resource)) {
                            $resource->set('template', 1);
                            $resource->set('pagetitle', 'Home');
                            $resource->set('content', '');
                            $resource->set('alias', 'index');
                            $resource->set('published', 1);
                            $resource->save();

                            // set up the correct format for Babel e.g. web:1,en:2
                            if(strlen($linkString) > 0) {
                                $linkString .= ';'.$lang_key . ':' . $resource->get('id');
                            } else {
                                $linkString .= 'web:' . $resource->get('id');
                            }

                            // store resource id
                            $resource_ids[] = $resource->get('id');
                        }

                        // Create context settings
                        createContextSetting($modx,$context->get('key'),'base_url','gateway','/');
                        createContextSetting($modx,$context->get('key'),'site_url','gateway','{url_scheme}{http_host}{base_url}{cultureKey}/');
                        createContextSetting($modx,$context->get('key'),'cultureKey','language',$lang_key);
                        createContextSetting($modx,$context->get('key'),'site_start','site',$resource->get('id'));
                    }
                    $i++;
                }

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
                        $link->set('value',$linkString);
                        $link->save();
                    }
                }

            }


            break;
        case xPDOTransport::ACTION_UPGRADE:
        case xPDOTransport::ACTION_UNINSTALL:
            $success = true;
            break;
    }
}
return $success;


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
    return $cs->save();
}