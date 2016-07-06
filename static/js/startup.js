pimcore.registerNS("pimcore.plugin.processmanager");

pimcore.plugin.processmanager = Class.create(pimcore.plugin.admin, {
    config : {},

    getClassName: function() {
        return "pimcore.plugin.processmanager";
    },

    initialize: function() {
        //only register plugin if user has a dataLogger permission
        if(pimcore.currentuser.permissions.indexOf("plugin_pm_permission_view") >= 0 ||
           pimcore.currentuser.permissions.indexOf("plugin_pm_permission_configure") >= 0
        ){
            pimcore.plugin.broker.registerPlugin(this);
        }
    },
 
    pimcoreReady: function (params,broker){
        var extrasMenu = pimcore.globalmanager.get("layout_toolbar").extrasMenu;
        if(extrasMenu){
            extrasMenu.insert(extrasMenu.items.length+1, {
                text: t("plugin_pm"),
                iconCls: "plugin_pmicon",
                cls: "pimcore_main_menu",
                handler: function () {
                    if (pimcore.globalmanager.get("plugin_pm_cnf")) {
                        Ext.getCmp("pimcore_panel_tabs").setActiveItem("pimcore_plugin_pm_panel");
                    } else {
                        pimcore.globalmanager.add("plugin_pm_cnf", new pimcore.plugin.processmanager.panel.general());
                    }
                }
            });
        }
        if(extrasMenu){
            extrasMenu.updateLayout();
        }

        this.getConfig();
    },

    getConfig : function(){
        Ext.Ajax.request({
            url: '/plugin/ProcessManager/index/get-plugin-config',
            success: function(response, opts) {
                this.config = Ext.decode(response.responseText);
            }.bind(this)
        });
    },

    monitoringItemRestart : function(id){
        Ext.Ajax.request({
            url: '/plugin/ProcessManager/monitoring-item/restart?id=' + id,
            success: function(response, opts) {
                var data = Ext.decode(response.responseText);
                if(data.success){
                    pimcore.helpers.showNotification(t("success"), t("plugin_pm_config_execution_success"), "success");
                    Ext.getCmp("plugin_pmmonitoring_item_list_panel").store.reload();
                }else{
                    pimcore.helpers.showNotification(t("error"), t("plugin_pm_config_execution_error"), "error",data.message);
                }
            }.bind(this)
        });
    },

    download : function(id,accessKey){

        var url = '/plugin/ProcessManager/index/download/?id='+ id +'&accessKey=' + accessKey;
        pimcore.helpers.download(url);
    }
});

var processmanagerPlugin = new pimcore.plugin.processmanager();

