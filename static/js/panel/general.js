
pimcore.registerNS("pimcore.plugin.processmanager.panel.general");
pimcore.plugin.processmanager.panel.general = Class.create({

    initialize: function (config) {
        config = defaultValue(config,{});

        if (!this.panel) {
            this.configPanel = new pimcore.plugin.processmanager.panel.config();
            this.monitoringItems = new pimcore.plugin.processmanager.panel.monitoringItem();

            var items = [];
            items.push(this.configPanel.getPanel());
            items.push(this.monitoringItems.getPanel());

            if(processmanagerPlugin.config.executorCallbackClasses){
                this.callbackSettings = new pimcore.plugin.processmanager.panel.callbackSetting();
                items.push(this.callbackSettings.getPanel());
            }
            this.panel = new Ext.TabPanel({
                title: t("plugin_pm"),
                closable: true,
                deferredRender: false,
                forceLayout: true,
                activeTab: 0,
                id: "pimcore_plugin_pm_panel",
                iconCls: "plugin_pmicon_header",
                items: items
            });

            var tabPanel = Ext.getCmp("pimcore_panel_tabs");
            tabPanel.add(this.panel);
            tabPanel.setActiveItem("pimcore_plugin_pm_panel");

            this.panel.on("destroy", function () {
                Ext.TaskManager.stop(this.monitoringItems.autoRefreshTask);
                pimcore.globalmanager.remove("plugin_pm_cnf");
            }.bind(this));

            if(config.activeTab){
                this.panel.setActiveTab(config.activeTab);
            }

            pimcore.layout.refresh();

        }
        return this.panel;
    }
});

