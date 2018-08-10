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

