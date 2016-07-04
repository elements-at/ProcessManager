pimcore.registerNS("pimcore.plugin.processmanager.executor.exportToolkit");
pimcore.plugin.processmanager.executor.exportToolkit = Class.create(pimcore.plugin.processmanager.executor.abstractExecutor,{


    getConfigList: function () {

        var store = [];

        for (var key in processmanagerPlugin.config.executorClass.exportToolkit.config.jobs) {
            if (processmanagerPlugin.config.executorClass.exportToolkit.config.jobs.hasOwnProperty(key)) {
                store.push([key,key]);
            }
        }

        this.configName = {
            fieldLabel: t("configuration"),
            xtype: "combo",
            editable: false,
            name: "configName",
            value: this.getFieldValue('configName'),
            store: store,
            mode: "local",
            width : "100%",
            triggerAction: "all"
        }
        return this.configName;
    },

    getFormItems : function(){
        var items = [];
        items.push(this.getTextFieldName());
        items.push(this.getTextField('group'));
        items.push(this.getTextArea('description'));
        if(processmanagerPlugin.config.executorCallbackClasses){
            items.push(this.getCallbackSelect());
        }
        items.push(this.getConfigList());
        items.push(this.getCheckbox('uniqueExecution'));
        items.push(this.getCronjobField());
        items.push(this.getCronjobDescription());
        items.push(this.getNumberField("keepVersions"));
        return items;
    }

});