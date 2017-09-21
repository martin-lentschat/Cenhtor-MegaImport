<?php
/**
 * @package Omeka\Plugins\MegaImport
 */

/** Path to plugins directory */
defined('MEGA_IMPORT_PLUGIN_DIRECTORY') or define('MEGA_IMPORT_PLUGIN_DIRECTORY', dirname(__FILE__));
define('ROOT',$_SERVER['DOCUMENT_ROOT']);
/**
*
*/
class MegaImportPlugin extends Omeka_Plugin_AbstractPlugin {
    /**
     * @var array This plugin's hooks.
     */
    protected $_hooks = array('install', 'uninstall', 'initialize', 'define_acl');
    /**
     * @var array This plugin's filters.
     */
    protected $_filters = array('admin_navigation_main');
    /**
     * Install this plugin.
     */
    public function hookInstall(){
        $db = get_db();
        $sql = "CREATE TABLE IF NOT EXISTS `{$db->MegaImport}` (
            `id` int unsigned NOT NULL auto_increment,
            `store` text NULL,
            `info` text NULL,
            `value` text NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $db->query($sql);
    }
    /**
     * Uninstall this plugin.
     */
    public function hookUninstall(){
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `{$db->MegaImport}`;";
        $db->query($sql);
    }
    /**
     * Initialize this plugin.
     */
    public function hookInitialize(){
        // Register the select filter controller plugin.
    }
    /**
     * Define this plugin's ACL.
     */
    public function hookDefineAcl($args){
        $acl = $args['acl']; // get the Zend_Acl
        //$args['acl']->addResource('MegaImport_Index');

        $indexResource = new Zend_Acl_Resource('MegaImport_Index');
        $pageResource = new Zend_Acl_Resource('MegaImport_Page');
        $acl->add($indexResource);
        $acl->add($pageResource);

        $acl->allow(array('super', 'admin', 'contributor'), array('MegaImport_Index', 'MegaImport_Page'));
        $acl->allow(null, 'MegaImport_Page', 'show');
        $acl->deny(null, 'MegaImport_Page', 'show-unpublished');
    }
    /**
     * Add the navigation link.
     */
    public function filterAdminNavigationMain($nav){
        if(is_allowed('MegaImport_Index', 'index')) {
            $nav[] = array('label' => __('Import'), 'uri' => url('mega-import'));
        }
        return $nav;
    }
}
