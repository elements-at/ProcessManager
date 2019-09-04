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
    </tr>
    <?php
    /**
     * @var $monitoringItem \Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem
     */
    foreach($this->reportItems as $i  =>  $monitoringItem){?>
        <tr>
            <td><?=$monitoringItem->getId()?></td>
            <td><?=$monitoringItem->getPid()?></td>
            <td><?=$monitoringItem->getName()?></td>
            <td><?php
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
        </tr>
    <?php
        if($i > 100){
            break;
        }
    } ?>
    <?php
    if(count($this->reportItems) > 100) { ?>
        <tr>
            <td colspan="8" class="note-important">Further <?=(count($this->reportItems)-100)?> items are considered as failed.</td>
        </tr>
    <?php } ?>
</table>

</body>
</html>
