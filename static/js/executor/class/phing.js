pimcore.registerNS("pimcore.plugin.processmanager.executor.phing");
pimcore.plugin.processmanager.executor.phing = Class.create(pimcore.plugin.processmanager.executor.class.abstractExecutor,{

    getFormItems : function(){
        var items = [];
        items.push(this.getTextFieldName());
        items.push(this.getTextField('group'));
        items.push(this.getTextArea('description'));

        if(processmanagerPlugin.config.executorCallbackClasses){
            items.push(this.getCallbackSelect());
        }

        items.push(this.getNumberField("keepVersions"));
        return items;
    }

});