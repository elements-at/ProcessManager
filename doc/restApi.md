## Rest-API

The Process Manager also provides a rest service to start jobs...
If you want to use the Rest Service you have to define the Pimcore users which should be allowed to execute/list... jobs in the "plugin-process-manager.php" config file.
Therefore add an array "restApiUsers" to the config as shown in the example below
```php
return [
    "general" => [
        "archive_treshold_logs" => 7,
        "executeWithMaintenance" => false,
        "processTimeoutMinutes" => 30
    ],
    "restApiUsers" => [
        [
            "username" => "ckogler",
            'apiKey' => "secret"
        ]
    ],

...
]

```
 You have to pass the "username" and "apiKey" parameter on each request. 

**URL: http://YOUR-DOMAIN/webservice/elementsprocessmanager/rest/execute?username=ckogler&apiKey=secret**
 
Executes a job by an ID or by the name. 

| Parameter | Type | Description |
| ----- | ------| ----------- |
| "id" or "name" | mandatory | ID or name of the configuration to execute |
| "callbackSettings" | optional | A Json or Xml string which is stored as the callbackSettings in the monitoring item |

It returns the monitoring item ID to check the process state...
**Example:**
```json
{
  "success": true,
  "monitoringItemId": 123
}
```

**URL: http://YOUR-DOMAIN/webservice/elementsprocessmanager/monitoring-item-state?username=ckogler&apiKey=secret**
 
Returns the state of a process by the monitoring item id

| Parameter | Type | Description |
| ----- | ------| ----------- |
| "id"| mandatory | ID of the monitoring item |

It returns the monitoring item ...
