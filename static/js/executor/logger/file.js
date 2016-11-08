pimcore.registerNS("pimcore.plugin.processmanager.executor.logger.file");
pimcore.plugin.processmanager.executor.logger.file = Class.create(pimcore.plugin.processmanager.executor.logger.abstractLogger,{
    type : 'file',

    getForm : function(){
        if(!this.button){
            this.getButton();
        }
        var myId = Ext.id();
        this.form =  new Ext.form.FormPanel({
            forceLayout: true,
            id: myId,
            type : 'formPanel',
            style: "margin: 10px",
            items: [
                this.getLogLevelField(),
                //this.getTextField('accessKey'),
                {
                    xtype: "textfield",
                    fieldLabel: t("plugin_pm_download_filepath"),
                    name: "filepath",
                    width: "100%",
                    readOnly: false,
                    value: this.getFieldValue('filepath')
                },
                this.getCheckbox('simpleLogFormat'),
                {
                    xtype: "hidden",
                    name: "class",
                    readOnly: true,
                    value: '\\ProcessManager\\Executor\\Logger\\File'
                }
            ],
            bodyStyle: "padding: 10px 30px 10px 30px; min-height:40px;",
            tbar: this.getTopBar(this.button.text,myId)
        });
        return this.form;
    },

    showLogs : function (monitoringItemId,loggerIndexPositions) {
        //we need to stop the panel reloading to prevent errors when a user clicks and a reload is done
        this.stopRefresh();
        var url = '/plugin/ProcessManager/monitoring-item/log-file-logger?id=' + monitoringItemId + '&loggerIndex='+loggerIndexPositions;
        var modal = new Ext.Window({
            id:'processManagerLoggerFileWindow',
            title: t('plugin_pm_log_info'),
            modal:true,
            height: '80%',
            width: '90%',
            maximizable: true,
            items : [{
                xtype : "box",
                autoEl: {tag: 'iframe', src: url, width : '100%', height : '100%'}
            }]
        });
        modal.show();
        this.startRefresh();


    }
});
var processManagerFileLogger = new pimcore.plugin.processmanager.executor.logger.file();
