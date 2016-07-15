<? if(!$this->getParam('ajax')){?>
<html>
<head>
    <title>Logs</title>
    <meta http-equiv="Page-Enter" content="blendTrans(Duration=1.0)">
    <meta http-equiv="Page-Exit" content="blendTrans(Duration=1.0)">
    <style type="text/css">
        body {
            background-color: #000;
            font-size: 13px;
            color:#fff;

            font-family: "Open Sans", "Helvetica Neue", helvetica, arial, verdana, sans-serif;
        }
        h1 {
            font-size: 18px;
            color: #f00;
            font-weight:bold;
            margin-bottom:0px;
        }
        .reload {
            position: fixed;
            bottom: 0;
            right: 0px;
            font-family: "Open Sans", "Helvetica Neue", helvetica, arial, verdana, sans-serif;
            background-color: #ECECEC;
            padding: 5px;
            margin-bottom: 0px;
            -webkit-border-top-left-radius: 5px;
            -moz-border-radius-topleft: 5px;
            border-top-left-radius: 5px;
        }
        .reload label{
            position: relative;
            top: -2px;

            color: #404040;
        }
        #content {
            padding-bottom:20px;
        }

        .reload label:hover {
            cursor: pointer;
        }
    </style>
    <script type="text/javascript" src="/pimcore/static6/js/lib/jquery-2.1.4.js"></script>

</head>
<body>

<div id="content">
    <?}
    if(!$this->monitoringItem->getPid()){?>
        <h1>Process Finished</h1>
    <?}?>
    <?=p_r($this->data)?>
<? if(!$this->getParam('ajax')){?>
</div>


<form method="" class="reload">
    <input type="checkbox" value="refresh" name="refresh" id="autorefresh" checked="checked "/> <label for="autorefresh"> Auto refresh </label>
</form>
<script type="text/javascript">
    var timer = null;



    $(function() {
        startRefresh();

        $('#autorefresh').change(function(){
           if(!this.checked){
               clearTimeout(timer);
           }else{
               startRefresh();
           }
        });
    });


    function startRefresh(){
        timer = setTimeout(startRefresh,1000);
        $.post(location.href + '&ajax=1', function(data) {
            $('#content').html(data);

            $(window).scrollTop($(document).height());
        });
    }
</script>
</body>
</html>
<?}?>