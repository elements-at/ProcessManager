<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rest-Test</title>
    <style type="text/css">
        .callbackTable {
            width: 100%;
            border-collapse: collapse;

        }
        .callbackTable td {
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>

<? if($result = $this->result){?>

    <div class="result">
        <h2>Result:</h2>
        <? p_r($result) ?>
    </div>
<?}?>
<form action="" method="post">
    <table class="callbackTable">
        <tr>
            <td width="150">ID:</td>
            <td>
                <select name="id">
                    <option value="">-- please choose --</option>
                    <?
                        foreach ($this->options as $key => $value){?>
                           <option value="<?=$key?>" <? if($this->getParam('id') == $key){?> selected<?}?>><?=$value?></option>
                        <?}
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Config name:</td>
            <td>
                <input type="text" name="name" value="<?=$this->getParam('name')?>"  style="width: 100%;"/>
            </td>
        </tr>
        <tr>
            <td>API-Key:</td>
            <td>
                <input type="text" name="apikey" value="<?=$this->getParam('apikey')?>"  style="width: 100%;"/>
            </td>
        </tr>
        <tr>
            <td>
                Callback:
            </td>
            <td>
                <textarea name="callbackSettings" style="width: 100%;height: 400px;"><?=$this->getParam('callbackSettings')?></textarea><br/>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="submit" name="post" />
            </td>
        </tr>
    </table>
</form>
</body>
</html>