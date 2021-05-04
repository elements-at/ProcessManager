# Installation

Add the bundle to your composer.json and enable/install it. 
```command 
composer require elements/process-manager-bundle
./bin/console pimcore:bundle:enable ElementsProcessManagerBundle
./bin/console pimcore:bundle:install ElementsProcessManagerBundle
```

After the installation you have to configure the bundle. Execute
```command 
bin/console config:dump-reference ElementsProcessManagerBundle
```
to dump the reference configuration.

A sample configuration could look like this
```yaml
elements_process_manager:
    archiveThresholdLogs: 14
    processTimeoutMinutes : 60
    disableShortcutMenu : false
    additionalScriptExecutionUsers : ["www-data","stagingUser"]
    reportingEmailAddresses : ["christian.kogler@elements.at"]
    restApiUsers:
        - {username: "tester" , apiKey: "1234"}
        - {username: "tester2" , apiKey: "344"}

services:
    example:
        class : Elements\Bundle\ProcessManagerBundle\Executor\Callback\General
        arguments :
            $name: "example"
            $extJsClass: "pimcore.plugin.processmanager.executor.callback.example"
            $jsFile: "/bundles/elementsprocessmanager/js/executor/callback/example.js"
        tags:
            - { name: "elements.processManager.executorCallbackClasses" }
```

Please set up the Cronjob which checks/executes the processes.

```
* * * * * php ~/www/bin/console process-manager:maintenance > /dev/null 2>&1
```

# Update
To update the bundle please use the following command:

```
composer update elements/process-manager-bundle
./bin/console doctrine:migrations:migrate --prefix=Elements\\Bundle\\ProcessManagerBundle 
```

If you want, that the migrations of the ProcessManagerBundle be executed automatically, please add the following
line to your **project composer.json**
```
  "scripts": {
    "post-update-cmd": [
       //...,
      "./bin/console doctrine:migrations:migrate --prefix=Elements\\Bundle\\ProcessManagerBundle --no-interaction"
    ],
```
