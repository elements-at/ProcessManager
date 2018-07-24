<?php

$data = $this->data;

if (!$this->getParam('ajax')) {

    ?>
    <html>
    <head>
        <title>Logs</title>
        <meta http-equiv="Page-Enter" content="blendTrans(Duration=1.0)">
        <meta http-equiv="Page-Exit" content="blendTrans(Duration=1.0)">
        <style type="text/css">
            body {
                background-color: #000;
                margin-top: 40px;
                font-size: 13px;
                color: #fff;

                font-family: "Open Sans", "Helvetica Neue", helvetica, arial, verdana, sans-serif;
            }

            h1 {
                font-size: 18px;
                color: #f00;
                font-weight: bold;
                margin-bottom: 0px;
            }

            .reload {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                font-family: "Open Sans", "Helvetica Neue", helvetica, arial, verdana, sans-serif;
                background-color: #ECECEC;
                padding: 5px;
                padding-left: 10px;
                margin-bottom: 0px;
            }

            .reload {
                color: #404040;
            }

            .reload label {
                position: relative;
                top: -2px;
            }

            .reloadWrapper {
                float: left;
                padding-right: 5px;
            }

            #content {
                padding-bottom: 20px;
                position: relative;
            }

            .reload label:hover {
                cursor: pointer;
            }
        </style>
        <script type="text/javascript" src="/pimcore/static6/js/lib/jquery-2.1.4.js"></script>

    </head>
    <body>

    <div id="content">
<?php }
if (!$this->monitoringItem->getPid()) {
    ?>
    <h1>Process Finished</h1>
<?php } ?>

<?php if ($this->getParam('ajax') && !$this->monitoringItem->getPid()) { ?>
    <script type="text/javascript">

        $('#autorefresh').attr('checked', false);
    </script>


<?php }

echo "<pre>";
print_r($data);
echo "</pre>";
?>

<?php
if (!$this->getParam('ajax')) { ?>
    </div>


    <form method="" class="reload">
        <?php if ($this->monitoringItem->getPid()) { ?>
            <div class="reloadWrapper">
                <input type="checkbox" value="refresh" name="refresh" id="autorefresh"
                       <?php if ($this->monitoringItem->getPid()){ ?>checked="checked"<?php } ?>/> <label
                        for="autorefresh"> Auto refresh |</label>
            </div>
        <?php } ?>
        <div class="loggerData">
            <b>LogLevel:</b> <?= $this->logLevel ?> | <b>Log file:</b> <?= $this->logFile ?>
        </div>
    </form>

<?php if ($this->monitoringItem->getPid()){ ?>
    <script type="text/javascript">
        var timer = null;


        $(function () {
            if ($('#autorefresh').is(':checked')) {
                startRefresh();
            }

            $('#autorefresh').change(function () {
                if (!this.checked) {
                    clearTimeout(timer);
                } else {
                    startRefresh();
                }
            });
        });


        function startRefresh() {
            timer = setTimeout(startRefresh, 1000);
            $.get(location.href + '&ajax=1', function (data) {
                $('#content').html(data);
                $(window).scrollTop($(document).height());
            });
        }
    </script>
<?php } ?>

    </body>
    </html>
<?php } ?>
