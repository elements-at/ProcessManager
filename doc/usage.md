### Usage

**In short:** 
The [SampleCommand](/src/Elements/Bundle/ProcessManagerBundle/examples/ProcessManager/Command/SampleCommand.php) is a example 
how you should use the plugin. If you are new to this Bundle just copy this file to your projekt command space (alter namespaces) 
and set up the command in the processmanager.

Details: When a script is executed via the Pimcore admin interface a monitoring item is created and the id of the monitoring item is passed to the cli script (Param: monitoring-item-id).
You will have to retrieve this id in your script and call the initProcessManager() function of the \Elements\Bundle\ProcessManagerBundle\ExecutionTrait. 

In your script you update the status of the monitoring item. The information of the monitoring item is used to display the status...
in the pimcore admin.
