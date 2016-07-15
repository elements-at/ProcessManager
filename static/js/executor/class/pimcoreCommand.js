pimcore.registerNS("pimcore.plugin.processmanager.executor.class.pimcoreCommand");
pimcore.plugin.processmanager.executor.class.pimcoreCommand = Class.create(pimcore.plugin.processmanager.executor.class.abstractExecutor,{

    initialize : function () {

        //this.settings.windowHeight = 800;
    },

    getCommandList: function () {

        var store = [];

        var commandWhiteList = processmanagerPlugin.config.executorClass.pimcoreCommand.config.commandWhiteList;

        for (var key in processmanagerPlugin.config.pimcoreCommands) {
            if (processmanagerPlugin.config.pimcoreCommands.hasOwnProperty(key)) {
                if(commandWhiteList && commandWhiteList.indexOf(key) == -1){
                        continue;
                }
                store.push([key,key]);
            }
        }

        this.command = {
            fieldLabel: t("plugin_pm_command"),
            xtype: "combo",
            editable: false,
            name: "command",
            value: this.getFieldValue('command'),
            store: store,
            mode: "local",
            width : "100%",
            triggerAction: "all"
        }
        return this.command;
    },

    getFormItems : function(){
        var items = [];
        items.push(this.getTextFieldName());
        items.push(this.getTextField('group'));
        items.push(this.getTextArea('description'));
        if(processmanagerPlugin.config.executorCallbackClasses){
            items.push(this.getCallbackSelect());
        }
        items.push(this.getCommandList());
        items.push(this.getTextField('commandOptions'));
        items.push(this.getCheckbox('uniqueExecution'));
        items.push(this.getCronjobField());
        items.push(this.getCronjobDescription());
        items.push(this.getNumberField("keepVersions"));
        return items;
    }

});