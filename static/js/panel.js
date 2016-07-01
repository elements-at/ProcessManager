
pimcore.registerNS("pimcore.plugin.processmanager.panel");
pimcore.plugin.processmanager.panel = Class.create({

    initialize: function () {

        if (!this.panel) {
            this.configPanel = new pimcore.plugin.processmanager.configListPanel();
            this.monitoringItems = new pimcore.plugin.processmanager.monitoringItemsPanel();

            this.panel = new Ext.TabPanel({
                title: t("plugin_processmanager"),
                closable: true,
                deferredRender: false,
                forceLayout: true,
                activeTab: 0,
                id: "pimcore_plugin_process_manager_panel",
                iconCls: "plugin_process_manager_icon_header",
                items: [this.configPanel.getPanel(), this.monitoringItems.getPanel()]
            });

            var tabPanel = Ext.getCmp("pimcore_panel_tabs");
            tabPanel.add(this.panel);
            tabPanel.setActiveItem("pimcore_plugin_process_manager_panel");

            this.panel.on("destroy", function () {
                Ext.TaskManager.stop(this.monitoringItems.autoRefreshTask);
                pimcore.globalmanager.remove("plugin_process_manager_cnf");
            }.bind(this));

            pimcore.layout.refresh();

        }
        return this.panel;
    }
});

