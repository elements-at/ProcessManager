pimcore.registerNS("pimcore.plugin.processmanager.executor.class.exportToolkit");
pimcore.plugin.processmanager.executor.class.exportToolkit = Class.create(pimcore.plugin.processmanager.executor.class.abstractExecutor,{


    getConfigList: function () {

        var store = [];

        if(typeof processmanagerPlugin.config.executorClass.exportToolkit != 'undefined'){
            for (var key in processmanagerPlugin.config.executorClass.exportToolkit.config.jobs) {
                if (processmanagerPlugin.config.executorClass.exportToolkit.config.jobs.hasOwnProperty(key)) {
                    store.push([key,key]);
                }
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
        var items = this.getDefaultItems();
        items.push(this.getConfigList());
        items.push(this.getCheckbox('uniqueExecution'));
        items.push(this.getCronjobField());
        items.push(this.getCronjobDescription());
        items.push(this.getNumberField("keepVersions"));
        return items;
    }

});