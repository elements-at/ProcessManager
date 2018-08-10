# ProcessManager

The ProcessManager allows you to manage (define,execute...) arbitrary processes/commands in the Pimcore backend. You can display the execution progress of the script in the Admin interface and the user can view the detailed log information. In addition you can define "actions" - e.g. a download of a file after the process has finished. Furthermore callback actions are available and the processes are monitored (you get an email if a process dies)

*Key features*
- Execute custom script in background
- Report the current execution state to the customer in the Pimcore admin
- View detailed debug log information in the Pimcore admin
- Scripts are monitored and you will receive an email if a job fails
- Provide custom actions after a job has finished (e.g download a file)
- Define custom Callback-Windows to allow the user to define runtime execution options
- Store/Manage CallbackSettings and reuse them at execution time

[For detailed information take a look at the documentation pages](./doc/01_ProcessManager.md)

## Important

Further development of this plugin is done only for the Pimcore 5 (Symfony version).
Please take a look at the master branch to get all updates / new features...

## Update notes

To update the plugin please use the following command
```
composer update pimcore-plugins/ProcessManager; php pimcore/cli/console.php process-manager:update
```

