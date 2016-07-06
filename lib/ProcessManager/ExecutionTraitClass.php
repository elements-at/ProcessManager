<?php
namespace ProcessManager;

trait ExecutionTraitClass {
    use ExecutionTrait;


    /**
     * @return \Monolog\Logger
     *
     * @throws \Zend_Exception
     */
    public function getLogger(){
        if(\Zend_Registry::isRegistered('process_manager_logger')){
            return \Zend_Registry::get('process_manager_logger');
        }
    }

    

}