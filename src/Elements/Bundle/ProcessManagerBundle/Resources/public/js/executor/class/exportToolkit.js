pimcore.registerNS("pimcore.plugin.processmanager.executor.class.exportToolkit");
pimcore.plugin.processmanager.executor.class.exportToolkit = Class.create(pimcore.plugin.processmanager.executor.class.abstractExecutor, {


    getConfigList: function () {

        var store = [];

        this.configName = {
            fieldLabel: t("configuration"),
            xtype: "combo",
            editable: false,
            name: "configName",
            value: this.getFieldValue('configName'),
            store: processmanagerPlugin.config.executorClasses.exportToolkit.config.jobs,
            mode: "local",
            width: "100%",
            triggerAction: "all"
        }
        return this.configName;
    },

    getFormItems: function () {
        var items = this.getDefaultItems();
        items.push(this.getConfigList());
        items.push(this.getCheckbox('uniqueExecution'));
        items.push(this.getCronjobField());
        items.push(this.getCronjobDescription());
        items.push(this.getNumberField("keepVersions"));
        return items;
    }

});