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
* Configuration is now done with .yml files (instead of the plugin-process-manager.php) 
 Please change the settings manually
* Executor classes / actions / loggers are now defined as services


## Migration from Pimcore 4 to Pimcore 5

* Create a backup of the following tables: 
  - plugin_process_manager_callback_setting
  - plugin_process_manager_configuration
  - plugin_process_manager_monitoring_item
* Update to Pimcore 5 first
* Install the bundle
* The location of the plugin configuration file has changed.
If you can't find it at var/config/plugin-process-manager.php then copy your existing version to that directory or place the config file in /app/config/pimcore/plugin-process-manager.php

* The location of file log files has changed. If you want to rescue them copy them from
/website/var/log/process-manager to /var/logs/process-manager

* The tmp directory has changed. It is now located at /var/tmp
Please note that you may have to adapt your configurations.
 
* The last step is to migrate the process mananager tables stored in the database.
Execute the "process-manager:migrate" console command. If you need additional mappings just adapt the Migrator class to your needs.

Watch for messages like this one:

pimcore-5@pimcore:~/www$ php bin/console process-manager:migrate
do not have mapping for \ProcessManager\Executor\Action\Download

If there is a mapping missing, add it to the Migrator class.

