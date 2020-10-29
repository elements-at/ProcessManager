# Actions

Actions are some kind of tasks which should be performed after a process has been finished... 
E.g. let the user download a file.

Therefore you can define certain actions in the "Actions" Tab.
![callbackWindow](img/actions.png)

These actions are then shown in the "active processes panel" and in the Logs
![callbackWindow](img/actionsLog.png)

Sometimes you want to set these actions programmatically. This can be done with:

```php
use \Elements\Bundle\ProcessManagerBundle\Executor\Action;
#...


$downloadAction = new Action\Download();
$downloadAction
    ->setAccessKey('myIcon')
    ->setLabel('Download Icon')
    ->setFilePath('/web/bundles/elementsprocessmanager/img/sprite-open-item-action.png')
    ->setDeleteWithMonitoringItem(false);

$openItemAction = new Action\OpenItem();
$openItemAction
    ->setLabel('Open document')
    ->setItemId(1)
    ->setType('document');

$monitoringItem->setActions([
    $downloadAction,
    $openItemAction
]);

$monitoringItem->save();
```