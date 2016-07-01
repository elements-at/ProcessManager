<html>
<head>
    <title>ProcessManager Report</title>
    <meta charset="utf-8">
    <style type="text/css">
        body {
            font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
            font-size: 14px;
        }

        h1 {
            color: #a20008;
        }
        .reportTable {
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .reportTable th {
            text-align: left;
        }
        .error {
            color: #ea0004;
            font-weight: bold;
        }
        .reportTable td, .reportTable th {
            border: 1px solid #000;
            padding: 5px 10px ;
        }

    </style>
</head>
<body>

<h1>ProcessManager report</h1>
<p>The following processes seems to have a problem. Please check them...</p>
<table class="reportTable">
    <tr>
        <th>ID</th>
        <th>PID</th>
        <th>Name</th>
        <th>Status</th>
        <th>Message</th>
        <th>Command</th>
        <th>Last update</th>
        <th>Callback settings</th>
    </tr>
    <?
    /**
     * @var $monitoringItem \ProcessManager\MonitoringItem
     */
    foreach($this->reportItems as $monitoringItem){?>
        <tr>
            <td><?=$monitoringItem->getId()?></td>
            <td><?=$monitoringItem->getPid()?></td>
            <td><?=$monitoringItem->getName()?></td>
            <td><?
                $status = $monitoringItem->getStatus();
                if($status == $monitoringItem::STATUS_FAILED){
                    echo '<span class="error">' . $status.'</span>';
                }else{
                    echo $status;
                }
                ?></td>
            <td><?=$monitoringItem->getMessage()?></td>
            <td><?=$monitoringItem->getCommand()?></td>
            <td><?=date('Y-m-d H:i:s',$monitoringItem->getModificationDate())?></td>
            <td>
                <?
                if($values = $monitoringItem->getCallbackSettings()){
                    echo print_r($values,true);
                }
                ?></td>
        </tr>
    <?}?>
</table>

</body>
</html>