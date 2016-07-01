pimcore.registerNS("pimcore.plugin.processmanager.executor.abstractExecutor");
pimcore.plugin.processmanager.executor.abstractExecutor = Class.create(pimcore.plugin.processmanager.helper.form,{
    id : null,
    mode : 'create',
    rec : null,
    saveUrl : "/plugin/ProcessManager/index/configuration-save",
    executorConfig : [],

    settings  : {
        windowHeight:500,
        windowWidth:800,
        editorlayout : {
            width: 650,
            height: 220
        }
    },


    setRecord : function(record){
        this.rec = record;
        this.executorConfig = record.get('settings').executorConfig;
        this.actions = record.get('settings').actions;
        this.id = record.get('id');
        this.mode = 'edit';
    },

    setExecutorConfig : function(config){
        this.executorConfig = config;
    },

    show:function () {

        if(this.mode == 'edit'){
            var title = t('plugin_process_manager_button_edit_' + this.executorConfig.name) + " (ID: " + this.rec.get('id') + ') '
        }else{
            var title = t('plugin_process_manager_button_add_' + this.executorConfig.name);
        }

        this.panelConfiguration = new Ext.Panel({
            title: t("plugin_processmanager_executor_config"),
            border: false,
            iconCls: "pimcore_icon_system",
            layout: "fit",
            region: "center",
            items : [this.getFormPanel()]
        });

        this.panelActions = new Ext.Panel({
            title: t("plugin_processmanager_executor_actions"),
            border: false,
            iconCls: "pimcore_icon_clear_cache",
            layout: "fit",
            region: "center",
            items : [this.getActionsPanel()]
        });


        this.windowButtons = [
            this.getFieldSaveButton(),
            {
                text: t("cancel"),
                scale: "medium",
                iconCls: "pimcore_icon_cancel",
                handler: this.closeWindow.bind(this)
            }

        ]

        this.tabPanel = new Ext.TabPanel({
            deferredRender: false,
            header: false,
            layout : "fit",
            forceLayout: true,
            activeTab: 0,
            id: "pimcore_plugin_process_manager_panel_config_tabs",
            iconCls: "plugin_process_manager_icon_header",
            items: [this.panelConfiguration,this.panelActions],
            buttons : this.windowButtons
        });


        this.window = new Ext.Window({
            id:'editWindow',
            minHeight: this.settings.windowHeight,
            layout : "fit",
            title: title,
            modal:true,
            width:this.settings.windowWidth,
            close : this.closeWindow.bind(this),
            items:[this.tabPanel]
        });

        var items = [];
        for (var key in this.actions) {
            if (this.actions.hasOwnProperty(key)) {
                var actionObj = eval('new ' + this.actions[key].extJsClass + '()');
                actionObj.setValues(this.actions[key]);
                actionObj.getForm();
                actionObj.addForm();
            }
        }

        this.window.show();
    },

    closeWindow : function(){
        this.window.destroy();
    },

   

    getCallbackSelect : function () {
        var store = [['','']];

        for (var key in processmanagerPlugin.config.executorCallbackClasses) {
            if (processmanagerPlugin.config.executorCallbackClasses.hasOwnProperty(key)) {
                store.push([processmanagerPlugin.config.executorCallbackClasses[key].extJsClass,t(processmanagerPlugin.config.executorCallbackClasses[key].name)]);
            }
        }

        this.callback = {
            fieldLabel: t("callback"),
            xtype: "combo",
            editable: false,
            name: "callback",
            value: this.getFieldValue('callback'),
            store: store,
            mode: "local",
            width : "100%",
            triggerAction: "all"
        }
        return this.callback;
    },

    getCronjobField : function(){
        var field = this.getTextField('cronjob');
        this.cronjob.width = 200;
        this.cronjob.boxLabel = 'test';
        this.cronjob.style = 'float:left;margin-right:20px;';
        return field;

    },

    getCronjobDescription : function(){
        return {
            xtype: "displayfield",
                hideLabel: true,
            width: 300,
            value: '<a href="http://cron.nmonitoring.com/cron-generator.html" target="_blank">' + t("plugin_process_manager_cronjob_expression_generator") +' </a>',
            cls: "process-manager-link"
        };
    },

    getTextField : function(fieldName){
        if(typeof this[fieldName] == 'undefined'){
            this[fieldName] = new Ext.form.TextField({
                fieldLabel: t(fieldName),
                width : '100%',
                name: fieldName,
                readOnly: false,
                value: this.getFieldValue(fieldName)
            });
        }
        return this[fieldName];
    },

    getFieldSaveButton : function(){
        if(!this.saveButton){
            this.saveButton = {
                xtype: 'button',
                scale: "medium",
                //autoWidth : true,
                text: t("plugin_process_manager_save_config_button"),
                iconCls: "plugin-process-manager-save-button",
                hideLabel: true,
                style: {
                    marginLeft: (this.settings.windowWidth-215) + 'px'
                },
                handler: this.updateConfig.bind(this)
            };
        }
        return this.saveButton;
    },

    getStorageValues : function () {
        return this.formPanel.getForm().getValues();
    },

    updateConfig : function(){
        var values = this.getStorageValues();

        var actions = this.getActionsValues();
        if(!values.name){
            alert('Please provide a name');
            return;
        }

        var data = Ext.encode({values : values,executorConfig : this.executorConfig,actions : actions});

        Ext.Ajax.request({
            url: this.saveUrl,
            method: "post",
            params: {data : data, id : this.id},
            success: function (response) {
                try{
                    var rdata = Ext.decode(response.responseText);
                    if (rdata && rdata.success) {
                        pimcore.helpers.showNotification(t("success"), t("plugin_process_manager_config_saved_success"), "success");
                        this.closeWindow();
                        Ext.getCmp('plugin_process_manager_config_list_panel').store.reload();
                    }
                    else {
                        pimcore.helpers.showNotification(t("error"), t("plugin_process_manager_config_saved_error"), "error",t(rdata.message));
                    }
                } catch(e){
                    pimcore.helpers.showNotification(t("error"), t("plugin_process_manager_config_saved_error"), "error");
                }
            }.bind(this)
        });
    },

    getActionsPanel : function(){

        var actionButtons = [];
        for (var key in processmanagerPlugin.config.executorActionClasses) {
            if (processmanagerPlugin.config.executorActionClasses.hasOwnProperty(key)) {
                var actionObj = eval('new ' + processmanagerPlugin.config.executorActionClasses[key].extJsClass + '()');
                actionButtons.push(actionObj.getButton());
            }
        }



        this.actionPanel = new Ext.Panel({
            bodyStyle:'background-color: #fff;',
            id : 'plugin_process_manager_action_panel',
            autoScroll: true,
            padding: 0,
            border: false,
            //layout: "fit",
            forceLayout: true,
            defaults: {
                forceLayout: true
            },
            items: items,
            listeners: {
                afterrender: function () {
                    pimcore.layout.refresh();
                }
            },
            tbar: [{
                iconCls: "pimcore_icon_add",
                menu: actionButtons
            }]
        });




        return this.actionPanel;
    },

    getActionsValues : function(){
        var data = [];
        var actions = this.actionPanel.items.getRange();

        for (var i=0; i < actions.length; i++) {
            if(actions[i].type == 'formPanel'){
                data.push(actions[i].getValues());
            }
        }
        return data;
    },


    getFormPanel : function () {
        var items = [];

        items.push(this.getTextField('name'));
        items.push(this.getTextArea('description'));
        items.push(this.getTextField('command'));

        this.formPanel = new Ext.FormPanel({
            id:'plugin_process_manager_executor_config_form',
            //title : '',
            border: false,
            bodyPadding: 15,
            items: this.getFormItems()
        });

        return this.formPanel;
    },

    /**
     * overwrite in extended classes
     *
     * @returns {Array}
     */
    getFormItems : function(){
        var items = [];
        items.push(this.getTextField('name'));
        return items;
    }
});