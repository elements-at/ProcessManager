# ProcessManager

## Update notes

To update the plugin please use the following command
```
composer update elements/process-manager-bundle; php pimcore/cli/console.php process-manager:update
```

## Release notes
Take a look at the tags :-)
## Introduction

> The ProcessManager allows you to manage (define,execute...) arbitrary processes/commands in the Pimcore backend. 
You can display the execution progress of the script in the Admin interface and the user can view the detailed log information. 
In addition you can define "actions" - e.g.  a download of a file after the process has finished. Furthermore callback actions are available and the processes are monitored (you get an email if a process dies)

[For detailed information take a look at the Wiki](https://gitlab.elements.at/pimcore-plugins/ProcessManager/wikis/home)

## Development Instance
> https://objecttools.elements.zone/admin/

## Migration from Pimcore 4 to Pimcore 5

* Create a backup of the following tables
** plugin_process_manager_callback_setting
** plugin_process_manager_configuration
** plugin_process_manager_monitoring_item
* Update to Pimcore 5 first
* Install the bundle
* The location of the plugin configuration file has changed.
If you can't find it at var/config/plugin-process-manager.php then copy your existing version to that directory.

* The location of file log files has changed. If you want to rescue them copy them from
/website/var/log/process-manager to /var/logs/process-manager

* The tmp directory has changed. It is now located at /var/tmp
Please note that you may have to adapt your configurations.
 
* The last step is to migrate the process mananager tables stored in the database.
Execute the elementsprocessmanager:migrate console command. If you need additional mappings just adapt the Migrator class to your needs.

Watch for messages like this one:

pimcore-5@pimcore:~/www$ php bin/console process-manager:migrate
do not have mapping for \ProcessManager\Executor\Action\Download

If there is a mapping missing, add it to the Migrator class.

