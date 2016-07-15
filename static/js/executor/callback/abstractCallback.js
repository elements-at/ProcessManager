pimcore.registerNS("pimcore.plugin.processmanager.executor.callback.abstractCallback");
pimcore.plugin.processmanager.executor.callback.abstractCallback = Class.create(pimcore.plugin.processmanager.helper.form,{
    executionUrl : "/plugin/ProcessManager/config/execute",

    grid : null,
    rowIndex : null,
    record : null,

    settings : {
        windowWidth: 800,
        windowHeight: 400,
        windowTitle : t('plugin_pm_callback_title')
    },

    initialize : function (grid,rowIndex) {

        this.grid = grid;
        this.rowIndex = rowIndex;
        if(this.grid){
            this.record = this.grid.getStore().getAt(this.rowIndex);
        }
    },

    setRecord : function(record){
        this.record = record;
    },

    applyCallbackSettings : function(data){

        if(data){
            this.formPanel.getForm().setValues(data);
        }else if(this.record){
            this.callbackSettingsForm.getForm().findField('name').setValue(this.record.get('name'));
            this.callbackSettingsForm.getForm().findField('description').setValue(this.record.get('description'));
            var settings = this.record.get('settings');
            this.formPanel.getForm().setValues(settings);
        }
    },
    openSaveSettingsWindow : function(){

        var items = [];

        var fieldSetGeneral = new Ext.form.FieldSet({
            title: t("plugin_pm_field_set_general"),
            combineErrors:false,
            items:[this.getTextField('name'),
                this.getTextArea('description')]
        });
        items.push(fieldSetGeneral);


        var fieldSetConfiguration = new Ext.form.FieldSet({
            title: t("plugin_pm_field_set_configuration"),
            combineErrors:false,
            items:[this.getFormPanel()]
        });
        items.push(fieldSetConfiguration);

        this.callbackSettingsForm = new Ext.FormPanel({
            id:'plugin_pm_executor_callback_settings_save',
            border: false,
            autoScroll: true,
            bodyPadding: 15,
            items: items
        });

        var buttons = [
            {
                xtype: 'button',
                scale: "medium",
                //autoWidth : true,
                text: t("plugin_pm_callback_settings_save"),
                icon : '/pimcore/static6/img/flat-color-icons/ok.svg',
                hideLabel: true,
                handler: function(){
                    var settings = this.getStorageValues();
                    var values = this.callbackSettingsForm.getValues();
                    var params = {values : Ext.encode(values),
                                  settings: Ext.encode(settings),
                                  type : this.name
                    };
                    if(this.record){
                        params.id = this.record.get('id');
                    }
                    Ext.Ajax.request({
                        url: '/plugin/ProcessManager/callback-settings/save',
                        method : 'post',
                        params: params,
                        failure: function (response) {
                            alert('Error at execution');
                        }.bind(this),
                        success: function (response) {
                            var data = Ext.decode(response.responseText);
                            if (data.success) {
                                pimcore.helpers.showNotification(t("success"), t("plugin_pm_config_callback_save_success"), "success");
                                Ext.getCmp('plugin_pm_callback_settings_panel').store.reload();
                                this.saveSettingsWindow.close();
                            } else {
                                pimcore.helpers.showNotification(t("error"), t("plugin_pm_config_callback_save_error"), "error", data.message);
                            }
                        }.bind(this)
                    });

                }.bind(this)
            },
            {
                text: t("cancel"),
                scale: "medium",
                iconCls: "pimcore_icon_cancel",
                handler: function(){
                    this.saveSettingsWindow.close();
                }.bind(this)
            }
        ];

        this.applyCallbackSettings();


        this.saveSettingsWindow = new Ext.Window({
            id:'callbackSettingsWindow',
            autoScroll: true,
            height: this.settings.windowHeight+250,
            layout : "fit",
            title: t("plugin_pm_callback_settings_save"),
            icon : '/pimcore/static6/img/flat-color-icons/settings.svg',
            modal:true,
            width: 700,
            items:[this.callbackSettingsForm],
            buttons : buttons
        });

        this.saveSettingsWindow.show();

    },
    getWindowButtons : function(){
        /*{
         xtype: 'button',
         scale: "medium",
         //autoWidth : true,
         text: t("plugin_pm_callback_settings_save"),
         icon : '/pimcore/static6/img/flat-color-icons/ok.svg',
         hideLabel: true,
         handler: this.openSaveSettingsWindow.bind(this)
         },*/
        var buttons = [

            {
                xtype: 'button',
                scale: "medium",
                //autoWidth : true,
                text: t("plugin_pm_permission_execute_button"),
                icon : '/pimcore/static6/img/flat-color-icons/go.svg',
                hideLabel: true,
                style: {
                   // marginLeft: (this.settings.windowWidth-215) + 'px'
                },
                handler: this.doExecute.bind(this)
            },
            {
                text: t("cancel"),
                scale: "medium",
                iconCls: "pimcore_icon_cancel",
                handler: this.closeWindow.bind(this)
            }
        ];
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
                    pimcore.helpers.showNotification(t("success"), t("plugin_pm_config_execution_success"), "success");
                    Ext.getCmp("pimcore_plugin_pm_panel").setActiveTab(1);
                } else {
                    pimcore.helpers.showNotification(t("error"), t("plugin_pm_config_execution_error"), "error", data.message);
                }
                if(this.store){
                    this.store.reload();
                }
            }.bind(this.grid)
        });
    },

    getFormPanel : function () {
        this.formPanel = new Ext.FormPanel({
            id:'plugin_pm_executor_callback_form',
            //title : '',
            border: false,
            autoScroll: true,

            bodyPadding: 15,
            items: this.getFormItems()
        });

        return this.formPanel;
    },

    openConfigWindow : function () {

        var tbar = [];
        var configStore = new Ext.data.Store({
            proxy: {
                url: '/plugin/ProcessManager/callback-settings/list?type=' + this.name,
                type: 'ajax',
                reader: {
                    type: 'json',
                    rootProperty: "data"
                }
            },
            fields: ["id","name","description","settings","type"]
        });

        this.selectConfig = {
            fieldLabel: t('plugin_pm_predefined_callback_settings'),
            name: 'docType',
            labelWidth: 120,
            xtype: "combo",
            displayField:'name',
            valueField: "id",
            store: configStore,
            editable: false,
            width : 400,
            triggerAction: 'all',
            value: '',
            listeners: {
                "select": function(a,record){
                    var data = record.getData();
                    //console.log(data.settings);
                    Ext.getCmp("plugin_pm_executor_callback_form").getForm().reset();
                    this.applyCallbackSettings(data.settings);
                }.bind(this)
            }
        };
        tbar.push(this.selectConfig);


        this.window = new Ext.Window({
            id:'editWindow',
            autoScroll: true,
            height: this.settings.windowHeight,
            layout : "fit",
            title: this.settings.windowTitle,
            iconCls: "pimcore_icon_system",
            modal:true,
            width:this.settings.windowWidth,
            close : this.closeWindow.bind(this),
            items:[this.getFormPanel()],
            buttons : this.getWindowButtons(),
            tbar:  tbar
        });

        this.window.show();
    },

    closeWindow : function(){
        this.window.destroy();
    }


});