pimcore.registerNS("pimcore.plugin.processmanager.executor.callback.executionNote");
pimcore.plugin.processmanager.executor.callback.executionNote = Class.create(pimcore.plugin.processmanager.executor.callback.abstractCallback,{

    getFormItems : function () {
        var items = [];
        items.push(this.getTextArea('note'));
        return items;
    },

    execute : function () {
        this.openConfigWindow();
    }
});