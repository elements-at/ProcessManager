pimcore.registerNS("pimcore.plugin.processmanager.executor.action.download");
pimcore.plugin.processmanager.executor.action.download = new Class.create(pimcore.plugin.processmanager.executor.action.abstractAction,{

    getButton : function(){
        this.button = {
            iconCls: "pimcore_icon_add",
            exporterClass: "DataLogger_Exporter_Default",
            text: t("plugin_pm_download"),
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
                fieldLabel: t("plugin_pm_accessKey") + ' <span style="color:#f00;">*</span>',
                name: "accessKey",
                width: "100%",
                readOnly: false,
                value: this.getFieldValue('accessKey')
            },
            {
                xtype: "textfield",
                fieldLabel: t("plugin_pm_label"),
                name: "label",
                width: "100%",
                readOnly: false,
                value: this.getFieldValue('label')
            },
            {
                xtype: "textfield",
                fieldLabel: t("plugin_pm_download_filepath") + ' <span style="color:#f00;">*</span>',
                name: "filepath",
                width: "100%",
                readOnly: false,
                value: this.getFieldValue('filepath')
            },
            {
                xtype: "checkbox",
                fieldLabel: t("plugin_pm_action_download_delete_with_monitoring_item") + ' <span style="color:#f00;">*</span>',
                name: "deleteWithMonitoringItem",
                width: "100%",
                readOnly: false,
                checked: this.getFieldValue('deleteWithMonitoringItem')
            },
            {
                xtype: "hidden",
                name: "class",
                readOnly: true,
                value: '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Action\\Download',
            }
            ]
        });

        return this.form;
    },

    executeActionForActiveProcessList : function(actionButtonPanel,actionData,monitoringItem,obj,index){

        let buttonId = 'processmanager_action_download_button_' + monitoringItem.id + '_' + actionData.accessKey;
        let button = Ext.getCmp(buttonId);
        if(monitoringItem.status == 'finished'){

            let text = actionData.label ? actionData.label : t('download');
            let disabled = false;

            if(actionData.dynamicData.fileExists == false){
                text += ' (' + t('plugin_pm_download_file_doesnt_exist') + ')';
                disabled = true;
            }
            button = Ext.create('Ext.Button', {
                text: text,
                //  id : buttonId,
                icon : "/bundles/pimcoreadmin/img/flat-color-icons/download-cloud.svg",
                // id: buttonId,
                style: (index > 0 ? 'margin-left:5px;' : ''),
                disabled : disabled,
                scale: 'small',
                handler: function() {
                    processmanagerPlugin.download(monitoringItem.id,actionData.accessKey);
                }
            });
            actionButtonPanel.items.add(button);
        }
    }

});