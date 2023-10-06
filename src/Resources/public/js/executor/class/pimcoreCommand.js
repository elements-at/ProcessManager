pimcore.registerNS("pimcore.plugin.processmanager.executor.class.pimcoreCommand");
pimcore.plugin.processmanager.executor.class.pimcoreCommand = Class.create(pimcore.plugin.processmanager.executor.class.abstractExecutor, {

    initialize: function () {

        //this.settings.windowHeight = 800;
    },

    getCommandList: function () {

        let store = [];

        for (let key in processmanagerPlugin.config.pimcoreCommands) {
            if (processmanagerPlugin.config.pimcoreCommands.hasOwnProperty(key)) {
                store.push([key, key]);
            }
        }

        this.command = {
            fieldLabel: t("plugin_pm_command"),
            xtype: "combo",
            editable: false,
            name: "command",
            labelWidth: this.labelWidth,
            value: this.getFieldValue('command'),
            store: store,
            mode: "local",
            width: "100%",
            triggerAction: "all"
        }
        return this.command;
    },

    getFormItems: function () {
        let items = this.getDefaultItems();
        items.push(this.getCommandList());
        items.push(this.getTextField('commandOptions'));
        items.push(this.getCheckbox('uniqueExecution'));
        items.push(this.getCronjobField());
        items.push(this.getCronjobDescription());
        items.push(this.getNumberField("keepVersions"));
        items.push(this.getNumberField("deleteAfterHours"));
        items.push(this.getCheckbox("hideMonitoringItem"));
        return items;
    }

});