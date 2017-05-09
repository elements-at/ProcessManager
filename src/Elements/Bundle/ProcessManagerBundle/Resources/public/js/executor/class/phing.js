pimcore.registerNS("pimcore.plugin.processmanager.executor.phing");
pimcore.plugin.processmanager.executor.phing = Class.create(pimcore.plugin.processmanager.executor.class.abstractExecutor, {

    getFormItems: function () {
        var items = this.getDefaultItems();

        items.push(this.getNumberField("keepVersions"));
        return items;
    }

});