<?php
/**
 * @package Omeka\Plugins\MegaImport
 */

class MegaImport_IndexController extends Omeka_Controller_AbstractActionController{
    /**
     * Initialize this controller.
     */
    public function init(){
        // Restrict actions to AJAX requests.
        $this->_helper->getHelper('AjaxContext')
                      ->addActionContexts(array('element-texts' => 'html', 
                                                'element-terms' => 'html'))
                      ->initContext();
    }

    public function indexAction(){
        $this->view->terms = $this->_helper->db->getTable('MegaImport')->findAll();
    }

    public function megaImportServeurAction(){
    }

    public function nakalaDisplayServeurAction(){
    }
}
