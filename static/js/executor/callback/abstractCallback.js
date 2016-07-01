pimcore.registerNS("pimcore.plugin.processmanager.executor.callback.abstractCallback");
pimcore.plugin.processmanager.executor.callback.abstractCallback = Class.create(pimcore.plugin.processmanager.helper.form,{
    executionUrl : "/plugin/ProcessManager/index/configuration-execute",

    grid : null,
    rowIndex : null,
    record : null,

    settings : {
        windowWidth: 800,
        windowHeight: 400,
        windowTitle : t('plugin_process_manager_callback_title')
    },

    initialize : function (grid,rowIndex) {
        this.grid = grid;
        this.rowIndex = rowIndex;
        this.record = this.grid.getStore().getAt(this.rowIndex);
    },


    getWindowButtons : function(){


        var buttons = [
            {
                xtype: 'button',
                scale: "medium",
                //autoWidth : true,
                text: t("plugin_process_manager_execute_button"),
                icon : '/pimcore/static6/img/flat-color-icons/go.svg',
                hideLabel: true,
                style: {
                    marginLeft: (this.settings.windowWidth-215) + 'px'
                },
                handler: this.doExecute.bind(this)
            },
            {
                text: t("cancel"),
                scale: "medium",
                iconCls: "pimcore_icon_cancel",
                handler: this.closeWindow.bind(this)
            }
        ]
        return buttons;
    },

    execute : function () {
        this.doExecute();
    },

    getStorageValues : function () {
        var data = [];
        if(this.formPanel){
            data = this.formPanel.getForm().getValues();
        }
        return data;
    },

    doExecute : function () {
        var params = {id: this.record.get('id'),
                      callbackSettings: Ext.encode(this.getStorageValues())};

        if(this.window){
            this.closeWindow();
        }

        Ext.Ajax.request({
            url: this.executionUrl,
            method : 'post',
            params: params,
            failure: function (response) {
                alert('Error at execution');
            }.bind(this),
            success: function (response) {
                var data = Ext.decode(response.responseText);
                if (data.success) {
                    pimcore.helpers.showNotification(t("success"), t("plugin_process_manager_config_execution_success"), "success");
                    Ext.getCmp("pimcore_plugin_process_manager_panel").setActiveTab(1);
                } else {
                    pimcore.helpers.showNotification(t("error"), t("plugin_process_manager_config_execution_error"), "error", data.message);
                }
                this.store.reload();
            }.bind(this.grid)
        });
    },

    getFormPanel : function () {
        this.formPanel = new Ext.FormPanel({
            id:'plugin_process_manager_executor_callback_form',
            //title : '',
            border: false,
            bodyPadding: 15,
            items: this.getFormItems()
        });

        return this.formPanel;
    },

    openConfigWindow : function () {

        this.window = new Ext.Window({
            id:'editWindow',
            height: this.settings.windowHeight,
            layout : "fit",
            title: this.settings.windowTitle,
            iconCls: "pimcore_icon_system",
            modal:true,
            width:this.settings.windowWidth,
            close : this.closeWindow.bind(this),
            items:[this.getFormPanel()],
            buttons : this.getWindowButtons()
        });

        this.window.show();
    },

    closeWindow : function(){
        this.window.destroy();
    },


});