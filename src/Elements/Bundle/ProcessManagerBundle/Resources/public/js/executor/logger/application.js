pimcore.registerNS("pimcore.plugin.processmanager.executor.logger.application");
pimcore.plugin.processmanager.executor.logger.application = Class.create(pimcore.plugin.processmanager.executor.logger.abstractLogger, {
    type: 'application',

    getForm: function () {
        if (!this.button) {
            this.getButton();
        }
        var myId = Ext.id();
        this.form = new Ext.form.FormPanel({
            forceLayout: true,
            id: myId,
            type: 'formPanel',
            style: "margin: 10px",
            items: [
                this.getLogLevelField(),
                {
                    xtype: "hidden",
                    name: "class",
                    readOnly: true,
                    value: '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Logger\\Application'
                }
            ],
            bodyStyle: "padding: 10px 30px 10px 30px; min-height:40px;",
            tbar: this.getTopBar(this.button.text, myId)
        });
        return this.form;
    },


    showLogs: function (monitoringItemId, loggerIndexPositions) {
        this.stopRefresh();
        var url = '/admin/elementsprocessmanager/monitoring-item/log-application-logger?id=' + monitoringItemId + '&loggerIndex=' + loggerIndexPositions;
        Ext.Ajax.request({
            url: url,
            success: function (response, opts) {
                var result = Ext.decode(response.responseText);
                if (result.success) {
                    if (result.success) {
                        try {
                            pimcore.globalmanager.get("pimcore_applicationlog_admin").activate();
                        }
                        catch (e) {
                            pimcore.globalmanager.add("pimcore_applicationlog_admin", new pimcore.log.admin());
                        }
                    }

                    var formateTime = function (date) {
                        return Ext.Date.format(date, 'g:i A');
                    };
                    var applicationLogger = pimcore.globalmanager.get("pimcore_applicationlog_admin");
                    applicationLogger.clearValues();

                    var fromDate = new Date(result.data.creationDate * 1000);
                    applicationLogger.fromDate.setValue(fromDate);

                    applicationLogger.fromTime.setValue(fromDate);

                    if (!result.data.pid) {
                        var tillDate = new Date(result.data.modificationDate * 1000);
                        applicationLogger.toDate.setValue(tillDate);
                        applicationLogger.toTime.setValue(formateTime(tillDate));
                    }

                    applicationLogger.searchpanel.getForm().setValues({
                        'component': result.data.name,
                        'priority': result.data.logLevel
                    });

                    applicationLogger.find();

                    if (applicationLogger.autoRefresh && result.data.pid) {
                        applicationLogger.autoRefresh.setValue(true);
                    }
                } else {
                    pimcore.helpers.showNotification(t("error"), t("plugin_pm_config_execution_error"), "error", result.message);
                }
            }.bind(this)
        });
    }
});
var processManagerApplicationLogger = new pimcore.plugin.processmanager.executor.logger.application();
