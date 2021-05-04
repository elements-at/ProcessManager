### Getting started (basics)

**In short:** 
The [ProcessManagerSampleCommandSimple](sample/src/App/Command/ProcessManagerSampleCommandSimple.php) is a very simple example on
how you should use the bundle. If you are new to this Bundle just copy this file to your project command space  
and set up the command in the ProcessManager.
The [ProcessManagerSampleCommandAdvanced](sample/src/App/Command/ProcessManagerSampleCommandAdvanced.php) is a more advanced example.

Details: When a script is executed via the Pimcore admin interface a monitoring item is created and the id of the monitoring item is passed to the cli script (Param: monitoring-item-id).
You will have to retrieve this id in your script and call the initProcessManager() function of the \Elements\Bundle\ProcessManagerBundle\ExecutionTrait. 

In your script you update the status of the monitoring item. The information of the monitoring item is used to display the status...
in the pimcore admin.
