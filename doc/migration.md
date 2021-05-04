## Migration from Pimcore 6 to Pimcore X

Before you update to the 4.x ProcessManager / Pimcore X make sure you are at the latest 3.x Version of the ProcessManager.

Your composer.json should contain an constraint like 

```console
"elements/process-manager-bundle": "v3.*"
```

Then execute:

```console
COMPOSER_MEMORY_LIMIT=-1 composer update elements/process-manager-bundle
bin/console pimcore:bundle:update ElementsProcessManagerBundle
```

#### After upgrade to Pimcore X

Rename DataBase Tables 

plugin_process_manager_configuration to bundle_process_manager_configuration
plugin_process_manager_monitoring_item to bundle_process_manager_monitoring_item
plugin_process_manager_callback_setting to bundle_process_manager_callback_setting

Change the Version of the ProcessManger to v4.x
```console
"elements/process-manager-bundle": "v4.*"
```

And run the update commands
```console
composer update elements/process-manager-bundle
bin/console pimcore:bundle:update ElementsProcessManagerBundle
```
#### Breaking changes

* The "executeWithMaintenance" config option has been removed - please set up a separate cronjob if you have not done it allready
```console
* * * * * php ~/www/bin/console process-manager:maintenance > /dev/null 2>&1
```


* [Commands Validator](../src/Service/CommandsValidator.php) for executable commands is implemented. Only commands which implement the ExecutionTrait will show up in the admin by default.  
* Phing executor has been removed
* CliCommand executor has been removed
* ExecuteShellCmdCommand executor has been removed
* ExportToolkit executor has been removed
* **Configuration is now done with .yml files (instead of the plugin-process-manager.php) 
 Please change the settings manually**
* Executor classes / actions / loggers are now defined as services
