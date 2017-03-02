pimcore.registerNS("pimcore.plugin.processmanager.executor.class.command");
pimcore.plugin.processmanager.executor.class.command = Class.create(pimcore.plugin.processmanager.executor.class.abstractExecutor,{

    getFormItems : function(){
        var items = this.getDefautlItems();
        items.push(this.getTextField('command'));
        items.push(this.getCheckbox('uniqueExecution'));
        items.push(this.getCronjobField());
        items.push(this.getCronjobDescription());
        items.push(this.getNumberField("keepVersions"));
        return items;
    }

});