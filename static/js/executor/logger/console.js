pimcore.registerNS("pimcore.plugin.processmanager.executor.logger.console");
pimcore.plugin.processmanager.executor.logger.console = Class.create(pimcore.plugin.processmanager.executor.logger.abstractLogger,{
    type : 'console',

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
                this.getCheckbox('simpleLogFormat'),
                //this.getTextField('accessKey'),
                {
                    xtype: "hidden",
                    name: "class",
                    readOnly: true,
                    value: '\\ProcessManager\\Executor\\Logger\\Console'
                }
            ],
            bodyStyle: "padding: 10px 30px 10px 30px; min-height:40px;",
            tbar: this.getTopBar(this.button.text,myId)
        });
        return this.form;
    },

    showLogs : function (monitoringItemId,loggerIndexPositions) {
        alert('Asdfasdfa');
        console.log(monitoringItemId);
        console.log(loggerIndexPositions);
    }

});