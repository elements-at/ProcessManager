pimcore.registerNS("pimcore.plugin.processmanager.executor.class.classMethod");
pimcore.plugin.processmanager.executor.class.classMethod = Class.create(pimcore.plugin.processmanager.executor.class.abstractExecutor,{

    getFormItems : function(){
        var items = this.getDefautlItems();
        items.push(this.getTextField('executorClass'));
        items.push(this.getTextField('executorMethod'));
        items.push(this.getCheckbox('uniqueExecution'));
        items.push(this.getCronjobField());
        items.push(this.getCronjobDescription());
        items.push(this.getNumberField("keepVersions"));
        return items;
    }
});