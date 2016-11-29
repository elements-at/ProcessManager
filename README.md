# ProcessManager

## Update notes

To update the plugin please use the following command
```
composer update pimcore-plugins/ProcessManager; php pimcore/cli/console.php process-manager:update
```

## Release notes
### 1.0.25
Return a dummy Monitoring item if it doesn't exist and added a CLI Logger if "autocreate" was passed and a Monitoring Item has to be created. 

### 1.0.24
Added process-manager:update Command. Plugin updates can now be done with php pimcore/cli/console.php process-manager:update

### 1.0.23 

Added general Callback class (\ProcessManager\Executor\Callback\General) + automatically check executing user. 

Callbacks can now be defined in the config as:

```
'\ProcessManager\Executor\Callback\General' => [
            'extJsClass' => 'pimcore.plugin.tyrolitpim.processmanager.executor.callback.exportEasyCatalog',
            'name' => 'exportEasyCatalog'
    ]
```


## Introduction

> The    ProcessManager allows you to manage (define,execute...) arbitrary processes/commands in the Pimcore backend. 
You can display the execution progress of the script in the Admin interface and the user can view the detailed log information. 
In addition you can define "actions" - e.g.  a download of a file after the process has finished. Furthermore callback actions are available and the processes are monitored (you get an email if a process dies)

[For detailed information take a look at the Wiki](https://gitlab.elements.at/pimcore-plugins/ProcessManager/wikis/home)