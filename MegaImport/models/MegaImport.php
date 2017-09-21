<?php
/**
 * The Mega Import page record class.
 * @package MegaImport
 */
class MegaImport extends Omeka_Record_AbstractRecord implements Zend_Acl_Resource_Interface {

    public function getResourceId() {
        return 'MegaImport Plugin';
    }

    public function getRecordTable() {
        
    }

    /**/
}
