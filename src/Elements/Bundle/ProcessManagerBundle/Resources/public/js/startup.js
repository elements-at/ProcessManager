String.prototype.ucFirst = function() {
    return this.charAt(0).toUpperCase() + this.slice(1).toLowerCase();
}

if(typeof defaultValue != 'function'){
    window.defaultValue = function(val,defaultValue){
        return typeof val != 'undefined' ? val : defaultValue;
    }
}

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
                handler: this.showProcessManager.bind(this)
            });

            //ignore process manager process log request exceptions as otherwise annoying errors can pop up in the pimcore backend
            Ext.Ajax.on({requestexception: function (conn, response, options) {
                if(response.request.url.startsWith('/admin/elementsprocessmanager/monitoring-item/list') && options.action === "read") {
                    options.ignoreErrors = true;
                }
            }, priority: 1000});
        }
        if(extrasMenu){
            extrasMenu.updateLayout();
        }

        this.getConfig();
    },

    showProcessManager : function (config){
        config = defaultValue(config,{});
        if (pimcore.globalmanager.get("plugin_pm_cnf")) {
            return Ext.getCmp("pimcore_panel_tabs").setActiveItem("pimcore_plugin_pm_panel");
        } else {
            return pimcore.globalmanager.add("plugin_pm_cnf", new pimcore.plugin.processmanager.panel.general(config));
        }
    },

    getConfig : function(){
        Ext.Ajax.request({
            url: '/admin/elementsprocessmanager/index/get-plugin-config',
            success: function(response, opts) {
                this.config = Ext.decode(response.responseText);
                this.addShortcutMenu();
            }.bind(this)
        });
    },

    getMenuItem : function (data) {
        return {
            text: data.name,
            iconCls: "pm_icon_cli",
            handler : this.executeJob.bind(this,data.id)
        }
    },
    addShortcutMenu : function () {
        if(pimcore.globalmanager.get("user").isAllowed("plugin_pm_permission_execute")){

            if(this.config.shortCutMenu){
                var menuItems = [];
                for(var key in this.config.shortCutMenu){
                    if(key != 'default'){
                        var group = {
                            text: key,
                            iconCls: "pimcore_icon_folder",
                            hideOnClick: false,
                            menu: {
                                cls: "pimcore_navigation_flyout",
                                shadow: false,
                                items: []
                            }
                        }
                        var childs = this.config.shortCutMenu[key];
                        for(var i = 0; i < childs.length; i++ ){
                            group.menu.items.push(this.getMenuItem(childs[i]))
                        }
                        menuItems.push(group);
                    }
                }

                if(this.config.shortCutMenu['default']){
                    var childs = this.config.shortCutMenu['default'];
                    for(var i = 0; i < childs.length; i++ ){
                        menuItems.push(this.getMenuItem(childs[i]));
                    }
                }

                var menu = new Ext.menu.Menu({
                    items: menuItems,
                    shadow: false,
                    cls: "pimcore_navigation_flyout"
                });


                var insertPoint = Ext.get("pimcore_menu_settings");
                if(!insertPoint) {
                    var dom = Ext.dom.Query.select('#pimcore_navigation ul li:last');
                    insertPoint = Ext.get(dom[0]);
                }
                var toolbar = pimcore.globalmanager.get("layout_toolbar");

                this.navEl = Ext.get(
                    insertPoint.insertHtml(
                        "afterEnd",
                        '<li id="plugin_pm_shortcut_menu" class="pimcore_menu_item" data-menu-tooltip="' + t('plugin_pm')+'">' + t('plugin_pm') + '</li>'
                    )
                );

                this.navEl.on("mousedown", toolbar.showSubMenu.bind(menu));
                pimcore.helpers.initMenuTooltips();
            }
        }
    },

    monitoringItemRestart : function(id){
        Ext.Ajax.request({
            url: '/admin/elementsprocessmanager/monitoring-item/restart?id=' + id,
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
    monitoringItemCancel : function (id) {
        Ext.Ajax.request({
            url: '/admin/elementsprocessmanager/monitoring-item/cancel?id=' + id,
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
        var url = '/admin/elementsprocessmanager/index/download?id='+ id +'&accessKey=' + accessKey;
        pimcore.helpers.download(url);
    },

    executeJob: function (id) {
        Ext.Ajax.request({
            url: '/admin/elementsprocessmanager/config/get-by-id?id=' + id,
            success: function (response) {
                var data = Ext.decode(response.responseText);
                if (data.success) {
                    var configData = data.data;
                    var callbackClass = configData.executorSettings.values.callback;
                    if (callbackClass) {
                        callbackClass = eval('new ' + callbackClass + '()');
                    } else {
                        callbackClass = new pimcore.plugin.processmanager.executor.callback.default();
                    }
                    callbackClass.reset();
                    callbackClass.setConfig(configData);
                    callbackClass.execute();
                } else {
                    pimcore.helpers.showNotification(t("error"), t("plugin_pm_config_execution_error"), "error", data.message);
                }
            }.bind(this)
        });
    }
});

var processmanagerPlugin = new pimcore.plugin.processmanager();

