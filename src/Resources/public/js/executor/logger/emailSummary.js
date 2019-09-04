pimcore.registerNS("pimcore.plugin.processmanager.executor.logger.emailSummary");
pimcore.plugin.processmanager.executor.logger.emailSummary = Class.create(pimcore.plugin.processmanager.executor.logger.abstractLogger, {
    type: 'emailSummary',

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
            defaults: {
                labelWidth: 120
            },
            items: [
                this.getLogLevelField(),
                this.getTextField('to',{tooltip : t('plugin_pm_to_tooltip'),'mandatory' : true}),
                this.getTextField('subject'),
                this.getTextArea('text'),
                this.getCheckbox('simpleLogFormat',{checked : true}),
                {
                    xtype: "hidden",
                    name: "class",
                    readOnly: true,
                    value: '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Logger\\EmailSummary'
                }
            ],
            bodyStyle: "padding: 10px 30px 10px 30px; min-height:40px;",
            tbar: this.getTopBar(this.button.text, myId)
        });
        return this.form;
    },

    showLogs: function (monitoringItemId, loggerIndexPositions) {
        return '';
    }
});
var processManagerFileLogger = new pimcore.plugin.processmanager.executor.logger.emailSummary();
