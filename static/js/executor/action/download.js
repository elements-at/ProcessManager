pimcore.registerNS("pimcore.plugin.processmanager.executor.action.download");
pimcore.plugin.processmanager.executor.action.download = Class.create(pimcore.plugin.processmanager.executor.action.abstractAction,{

    getButton : function(){
        this.button = {
            iconCls: "pimcore_icon_add",
            exporterClass: "DataLogger_Exporter_Default",
            text: t("plugin_process_manager_download"),
            "handler" : this.addForm.bind(this)
        }
        return this.button;
    },

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
            bodyStyle: "padding: 10px 30px 10px 30px; min-height:40px;",
            tbar: this.getTopBar(this.button.text,myId),
            items: [{
                xtype: "textfield",
                fieldLabel: t("plugin_process_manager_accessKey") + ' <span style="color:#f00;">*</span>',
                name: "accessKey",
                width: "100%",
                readOnly: false,
                value: this.getFieldValue('accessKey')
            },
            {
                xtype: "textfield",
                fieldLabel: t("plugin_process_manager_download_filepath") + ' <span style="color:#f00;">*</span>',
                name: "filepath",
                width: "100%",
                readOnly: false,
                value: this.getFieldValue('filepath')
            },
            {
                xtype: "hidden",
                name: "class",
                readOnly: true,
                value: '\\ProcessManager\\Executor\\Action\\Download',
            }
            ]
        });

        return this.form;
    }

});