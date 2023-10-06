pimcore.registerNS("pimcore.plugin.processmanager.executor.action.download");
pimcore.plugin.processmanager.executor.action.download = new Class.create(pimcore.plugin.processmanager.executor.action.abstractAction,{

    getButton : function(){
        this.button = {
            icon: '/bundles/pimcoreadmin/img/flat-color-icons/download-cloud.svg',
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

        let items = [];
        items.push(this.getTextField('accessKey',{mandatory : true,tooltip : t('plugin_pm_tooltip_accessKey')}));
        items.push(this.getTextField('label'));
        items.push(this.getTextField('filepath',{mandatory: true}));
        items.push(this.getCheckbox('deleteWithMonitoringItem',{
            fieldLabel : t('plugin_pm_action_download_delete_with_monitoring_item'),
            inputValue : true
        }));
        items.push(this.getTextField('class',{hidden: true,value : '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Action\\Download'}));
        this.form =  new Ext.form.FormPanel({
            forceLayout: true,
            id: myId,
            type : 'formPanel',
            style: "margin: 10px",
            bodyStyle: "padding: 10px 30px 10px 30px; min-height:40px;",
            tbar: this.getTopBar(this.button.text,myId),
            items: items
        });

        return this.form;
    },

    executeActionForActiveProcessList : function(actionButtonPanel,actionData,monitoringItem,obj,index){

        if(actionData.executeAtStates.includes(monitoringItem.status)){

            let text = actionData.label ? actionData.label : t('download');
            let disabled = false;

            if(actionData.dynamicData.fileExists == false){
                text += ' (' + t('plugin_pm_download_file_doesnt_exist') + ')';
                disabled = true;
            }
            let button = Ext.create('Ext.Button', {
                text: text,
                icon : "/bundles/pimcoreadmin/img/flat-color-icons/download-cloud.svg",
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

document.addEventListener('processManager.monitoringItemGrid', (e) => {
    e.preventDefault();
    let currentTarget = e.detail.sourceEvent.currentTarget;
    if(e.detail.trigger === 'download'){
        let accessKey = e.detail.sourceEvent.currentTarget.getAttribute('data-process-manager-access-key');
        processmanagerPlugin.download(e.detail.monitoringId,accessKey);
    }
});