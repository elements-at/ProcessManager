pimcore.registerNS("pimcore.plugin.processmanager.executor.classMethod");
pimcore.plugin.processmanager.executor.classMethod = Class.create(pimcore.plugin.processmanager.executor.abstractExecutor,{

    getFormItems : function(){
        var items = [];
        items.push(this.getTextFieldName());
        items.push(this.getTextField('group'));
        items.push(this.getTextArea('description'));
        if(processmanagerPlugin.config.executorCallbackClasses){
            items.push(this.getCallbackSelect());
        }
        items.push(this.getTextField('executorClass'));
        items.push(this.getTextField('executorMethod'));
        items.push(this.getCheckbox('uniqueExecution'));
        items.push(this.getCronjobField());
        items.push(this.getCronjobDescription());
        items.push(this.getNumberField("keepVersions"));
        return items;
    }
});