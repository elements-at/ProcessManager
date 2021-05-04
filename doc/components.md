### Main components
The bundle consists of 6 main components.

| Component | Description |
| ----- | ----------- |
| Monitoring Item | In your script you will have a monitoringItem which holds the state/messages/loggers... most of the time you will just interact with the monitoring item. |
| Executor Classes | A executor Class is responsible to create and execute the Command in the background. |
| Executor Callback Classes | A callback Class is responsible to provide a configurtation window in the pimcore admin where the user can define arbitrary configurations  |
| Executor Action Classes | A Action Class is responsible to execute a certain action (e.g. provide a Download button) after the job has finished.  |
|Predefined callback settings  |  Allows you to configure/store predefined configuration sets which can be selected at execution time |
|Loggers  |  Allows you to add loggers (Console,File..) |
### MonitoringItem 

| Method| Description |
| ----- | ----------- |
| setMessage("my message")| Sets a short message which is displayed in the admin. The message is automatically logged to the logs - if you don't want to log the message, pass "false" as second parameter   |
| getLogger()|  Returns the logger.  Currently a File/Stream and Application Logger is returned. May be configurable in future. 
|setTotalSteps(10)| Total steps |
|setCurrentStep(1)| The current processing step|
|setTotalWorkload(100)| The workload which has to be perfomed for the current step|
|setCurrentWorkload(10)| Current work processed|
|setCompleted()|Helper to set the job to "finshed"|


### Executor Classes 

| Class| Description |
| ----- | ----------- |
| \Elements\Bundle\ProcessManagerBundle\Executor\CliCommand | Executes a custom cli command  |
|\Elements\Bundle\ProcessManagerBundle\Executor\PimcoreCommand |  Executes a pimcore command |
|\Elements\Bundle\ProcessManagerBundle\Executor\ClassMethod  | Initializes a Class and calls a method  |

### Action Classes

| Class| Description |
| ----- | ----------- |
| \Elements\Bundle\ProcessManagerBundle\Executor\Action\Download| Provide a download after a job has finished  |


### Callback Classes

| Class| Description |
| ----- | ----------- |
| \Elements\Bundle\ProcessManagerBundle\Executor\Callback\ExecutionNote | Just an easy callback (provide a text note field) to get started. Callback classes are always job specific ;-)  |

### Logger Classes 

| Class| Description |
| ----- | ----------- |
| \Elements\Bundle\ProcessManagerBundle\Executor\Logger\File | Logs the messages to a file. If no file path is specified, the logs are written to  /website/var/log/process-manager/(MonitoringItem-ID).log |
|\Elements\Bundle\ProcessManagerBundle\Executor\Logger\Console |  The messages are logged to the php stdout (for cli execution) |
|\Elements\Bundle\ProcessManagerBundle\Executor\Logger\Application  | The messages are logged to the Application-Logger. The name of the Configuration is used as component name  |

If "Simple log format" is checked, the Context-Information is omitted (cleaner log messages -> useful for File and Console Logger)
