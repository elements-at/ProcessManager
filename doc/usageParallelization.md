### How to use - Parallelization / Multiprocessing

The Processmanager allows you to execute multiple child processes. Before you use the multiprocessing option please make sure
that you have read the [Basic usage guide](/doc/gettingStarted.md).

To get started please take a look at the [Sample Command](sample/src/App/Command/MultiprocessingSampleCommand.php) which shows how to use the feature.

When a child process is executed the parameter "--monitoring-item-parent-id" is passed so you have to support this parameter in your command.
Depending on the paremeter you can execute different methods... it's up to you. 

To execute the Child processes you can use the method of the trait (or implement your own logic...)
````php
$this->executeChildProcesses($monitoringItem,$data,2,4,$callback); 
````
 Parameter        | Description           |
| ------------- |-------------|
| $monitoringItem | The monitoring item of the main process | 
| $data | is the workload to process - most of the time it is just a array with entries. It is saved to the "metadata" field of the child process so you can access it later on |
| $numberOfChildProcesses | defines how much child processes can be run in parallel |
| $batchSize | defines how much entries should be processed in each child process |
| $callback | A function that can be used to modify the monitoringItem settings of the child process


To execute multiple processes from an arbitrary place you could call:

```php
$monitoringItem = \Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle::getMonitoringItem();
\Elements\Bundle\ProcessManagerBundle\ExecutionTrait::executeChildProcesses($monitoringItem,$data,5,50,function ( MonitoringItem $monitoringItem, \Elements\Bundle\ProcessManagerBundle\Executor\PimcoreCommand $executor){
                    $values = $executor->getValues();
                    $values['command'] = 'website:import:articles'; //execute another command
                    $monitoringItem->setName('Article import - child');
                    $executor->setValues($values);
                });
```
