<h1>FatalShutdown Plugin - Configuration</h1>

<?php if($this->saved){?>
    <strong style="color: #23a920;">Config Data has been saved.</strong>
<?php } ?>


<form action="" method="post">
    <h2>Email</h2>
    <table>
        <tr>
            <td><b>Alternative Subject</b></td>
            <td><input style="width: 500px;" type="text" name="subject" value="<?=$this->pluginConfig['email']['subject']?>" /></td>
        </tr>
        <tr>
            <td><b>Recipients (comma separated)</b></td>
            <td><input style="width: 500px;" type="text" name="recipients" value="<?=$this->pluginConfig['email']['recipients']?>" /></td>
        </tr>
    </table>

    <hr/>
    <input type="submit" name="submit" value="save" />

</form>

