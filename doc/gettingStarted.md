### Getting started (basics)

**In short:** 
The [ProcessManagerSampleCommandSimple](sample/src/AppBundle/Command/ProcessManagerSampleCommandSimple.php) is a very simple example on
how you should use the bundle. If you are new to this Bundle just copy this file to your project command space  
and set up the command in the ProcessManager.
The [ProcessManagerSampleCommandAdvanced](sample/src/AppBundle/Command/ProcessManagerSampleCommandAdvanced.php) is a more advanced example.

Details: When a script is executed via the Pimcore admin interface a monitoring item is created and the id of the monitoring item is passed to the cli script (Param: monitoring-item-id).
You will have to retrieve this id in your script and call the initProcessManager() function of the \Elements\Bundle\ProcessManagerBundle\ExecutionTrait. 

In your script you update the status of the monitoring item. The information of the monitoring item is used to display the status...
in the pimcore admin.

## Callbacks

Callbacks are configuration windows/forms which are displayed before a process is executed. This allowes the user to configure certain runtime options. 
The selected values are stored in the monitoring item and can be retrieved by calling 

```php
$callbackSettings = $monitoringItem->getCallbackSettings();
```

A "Callback" can be defined on each process  ("Settings" -> "Callback"). To create a new custom callback you have to add an entry to the "executorCallbackClasses" array in the config. 
A entry could look like this:

```php
[
            "name" => "exportProducts",
            "class" => "\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Callback\\General",
            "extJsClass" => "pimcore.plugin.PLUGINNAME.processmanager.executor.callback.exportProducts",
]
```

For most use cases you just have to provide a unique "name" and a extJsClass which is responsible to open the window.
The ExtJs Class sould extend the pimcore.plugin.processmanager.executor.callback.abstractCallback and implement a "getFormItems" method which returns the configuration fields.

The abstract callback Class implements certain helpers to easily add new form elements. Please take a look at [example.js](/src/Elements/Bundle/ProcessManagerBundle/Resources/public/js/executor/callback/example.js). This class demonstrates how to add the provided fields. Of course you can add your own custom fields as well.

The example.js file provides a callback window like this:

![callback](img/callback.png)

