pimcore.registerNS("pimcore.plugin.processmanager.executor.class.abstractExecutor");
pimcore.plugin.processmanager.executor.class.abstractExecutor = Class.create(pimcore.plugin.processmanager.helper.form, {
    id: null,
    mode: 'create',
    rec: null,
    saveUrl: "/admin/elementsprocessmanager/config/save",
    executorConfig: [],

    settings: {
        windowHeight: 500,
        windowWidth: 800,
        editorlayout: {
            width: 650,
            height: 220
        }
    },


    setRecord: function (record) {
        this.rec = record;

        this.extJsSettings = record.get('extJsSettings');

        this.executorConfig = record.get('extJsSettings').executorConfig;
        this.actions = record.get('extJsSettings').actions;
        this.loggers = record.get('extJsSettings').loggers;

        this.id = record.get('id');
        this.mode = 'edit';
    },

    setExecutorConfig: function (config) {
        this.executorConfig = config;
    },

    show: function () {

        if (this.mode == 'edit') {
            var title = t('plugin_pm_button_edit_' + this.executorConfig.name) + " (ID: " + this.rec.get('id') + ') '
        } else {
            var title = t('plugin_pm_button_add_' + this.executorConfig.name);
        }

        this.panelConfiguration = new Ext.Panel({
            title: t("plugin_pm_executor_config"),
            border: false,
            icon: '/bundles/pimcoreadmin/img/flat-color-icons/settings.svg',
            layout: "fit",
            region: "center",
            items: [this.getFormPanel()]
        });

        this.panelActions = new Ext.Panel({
            title: t("plugin_pm_executor_actions"),
            border: false,
            iconCls: "pimcore_icon_clear_cache",
            layout: "fit",
            region: "center",
            items: [this.getActionsPanel()]
        });

        this.panelLoggers = new Ext.Panel({
            title: t("plugin_pm_executor_loggers"),
            border: false,
            iconCls: "pimcore_icon_log_admin",
            layout: "fit",
            region: "center",
            items: [this.getLoggersPanel()]
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
            layout: "fit",
            forceLayout: true,
            activeTab: 0,
            id: "pimcore_plugin_pm_panel_config_tabs",
            iconCls: "plugin_pmicon_header",
            items: [this.panelConfiguration, this.panelActions, this.panelLoggers],
            buttons: this.windowButtons
        });


        this.window = new Ext.Window({
            id: 'editWindow',
            height: '80%',
            layout: "fit",
            title: title,
            modal: true,
            width: this.settings.windowWidth,
            close: this.closeWindow.bind(this),
            items: [this.tabPanel]
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

        for (var key in this.loggers) {
            if (this.loggers.hasOwnProperty(key)) {
                var obj = eval('new ' + this.loggers[key].extJsClass + '()');
                obj.setValues(this.loggers[key]);
                obj.getForm();
                obj.addForm();
            }
        }
        this.window.show();
    },

    closeWindow: function () {
        this.window.destroy();
    },


    getCallbackSelect: function () {
        var store = [['', '']];

        for (var key in processmanagerPlugin.config.executorCallbackClasses) {
            if (processmanagerPlugin.config.executorCallbackClasses.hasOwnProperty(key)) {
                store.push([processmanagerPlugin.config.executorCallbackClasses[key].extJsClass, t('plugin_pm_' + processmanagerPlugin.config.executorCallbackClasses[key].name)]);
            }
        }

        this.callback = {
            fieldLabel: t("plugin_pm_callback"),
            xtype: "combo",
            editable: false,
            name: "callback",
            labelWidth: this.labelWidth,
            value: this.getFieldValue('callback'),
            store: store,
            mode: "local",
            width: "100%",
            triggerAction: "all",
            onChange: function (newVal, oldVal) {
                var parts = newVal.split("\.");
                this.predefinedCallbackStore.load({
                    params: {
                        type: parts[parts.length - 1]
                    }
                });
                this.formPanel.getForm().findField("defaultPreDefinedConfig").setValue(null);
                this.formPanel.getForm().findField("defaultPreDefinedConfig").setHidden(!newVal);
            }.bind(this)
        }
        return this.callback;
    },

    getCallbackPredefinedConfig: function () {

        var callbackType = '';

        if (this.getFieldValue('callback')) {
            var val = this.getFieldValue('callback');
            var parts = val.split("\.");
            callbackType = parts[parts.length - 1];
        }

        /* mandant */
        this.predefinedCallbackStore = new Ext.data.Store({
            autoLoad: true,
            proxy: {
                url: '/admin/elementsprocessmanager/callback-settings/list',
                type: 'ajax',
                reader: {
                    type: 'json',
                    rootProperty: "data"
                },
                extraParams: {
                    type: callbackType
                }
            }
        });

        this.defaultPreDefinedConfig = this.getSelectField("defaultPreDefinedConfig", {
            store: this.predefinedCallbackStore,
            hidden: !callbackType,
            displayField: "name",
            valueField: "id"
        });

        return this.defaultPreDefinedConfig;
    },

    getCronjobField: function () {
        var field = this.getTextField('cronjob');
        field.width = 300;
        field.boxLabel = 'test';
        field.style = 'float:left;margin-right:20px;';
        return field;

    },

    getCronjobDescription: function () {
        return {
            xtype: "displayfield",
            hideLabel: true,
            width: 300,
            value: '<a href="https://crontab.guru" target="_blank">' + t("plugin_pm_cronjob_expression_generator") + ' </a>',
            cls: "process-manager-link"
        };
    },


    getFieldSaveButton: function () {
        if (!this.saveButton) {
            this.saveButton = {
                xtype: 'button',
                scale: "medium",
                //autoWidth : true,
                text: t("plugin_pm_save_config_button"),
                iconCls: "plugin-process-manager-save-button",
                hideLabel: true,
                style: {
                    marginLeft: (this.settings.windowWidth - 215) + 'px'
                },
                handler: this.updateConfig.bind(this)
            };
        }
        return this.saveButton;
    },

    getStorageValues: function () {
        var values = this.formPanel.getForm().getValues();
        return values;
    },

    updateConfig: function () {
        var values = this.getStorageValues();

        var actions = this.getActionsValues();
        var loggers = this.getLoggersValues();

        if (!values.id) {
            alert('Please provide an id');
            return;
        }
        if (!values.name) {
            alert('Please provide a name');
            return;
        }

        var data = Ext.encode({
            values: values,
            executorConfig: this.executorConfig,
            actions: actions,
            loggers: loggers
        });

        Ext.Ajax.request({
            url: this.saveUrl,
            method: "post",
            params: {data: data, id: this.id},
            success: function (response) {
                try {
                    var rdata = Ext.decode(response.responseText);
                    if (rdata && rdata.success) {
                        pimcore.helpers.showNotification(t("success"), t("plugin_pm_config_saved_success"), "success");
                        this.closeWindow();
                        Ext.getCmp('plugin_pm_config_list_panel').store.reload();
                    } else {
                        pimcore.helpers.showNotification(t("error"), t("plugin_pm_config_saved_error"), "error", t(rdata.message));
                    }
                } catch (e) {
                    pimcore.helpers.showNotification(t("error"), t("plugin_pm_config_saved_error"), "error");
                }
            }.bind(this)
        });
    },

    getLoggersPanel: function () {
        var actionButtons = [];
        var loggerButtons = [];
        for (var key in processmanagerPlugin.config.executorLoggerClasses) {
            if (processmanagerPlugin.config.executorLoggerClasses.hasOwnProperty(key)) {
                var loggerObj = eval('new ' + processmanagerPlugin.config.executorLoggerClasses[key].extJsClass + '()');
                actionButtons.push(loggerObj.getButton());
            }
        }

        this.loggerPanel = new Ext.Panel({
            bodyStyle: 'background-color: #fff;',
            id: 'plugin_pm_logger_panel',
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
            }
            ]
        });
        return this.loggerPanel;
    },

    getActionsPanel: function () {

        var actionButtons = [];
        for (var key in processmanagerPlugin.config.executorActionClasses) {
            if (processmanagerPlugin.config.executorActionClasses.hasOwnProperty(key)) {
                var actionObj = eval('new ' + processmanagerPlugin.config.executorActionClasses[key].extJsClass + '()');
                actionButtons.push(actionObj.getButton());
            }
        }


        this.actionPanel = new Ext.Panel({
            bodyStyle: 'background-color: #fff;',
            id: 'plugin_pm_action_panel',
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

    getLoggersValues: function () {
        var data = [];
        var loggers = this.loggerPanel.items.getRange();

        for (var i = 0; i < loggers.length; i++) {
            if (loggers[i].type == 'formPanel') {
                data.push(loggers[i].getValues());
            }
        }
        return data;
    },

    getActionsValues: function () {
        var data = [];
        var actions = this.actionPanel.items.getRange();

        for (var i = 0; i < actions.length; i++) {
            if (actions[i].type == 'formPanel') {
                data.push(actions[i].getValues());
            }
        }
        return data;
    },


    getFormPanel: function () {
        var items = [];

        items.push(this.getTextField('name'));
        items.push(this.getTextArea('description'));
        items.push(this.getTextField('command'));

        this.formPanel = new Ext.FormPanel({
            id: 'plugin_pm_executor_config_form',
            //title : '',
            autoScroll: true,
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
    getFormItems: function () {
        var items = [];
        items.push(this.getTextField('name'));
        return items;
    },

    getDefaultItems: function () {
        var items = [];
        items.push(this.getTextField('id', {mandatory: true}));
        items.push(this.getTextField('name', {mandatory: true}));
        items.push(this.getTextField('group'));
        items.push(this.getTextArea('description'));
        items.push(this.getRoleSelection('restrictToRoles'));

        if (processmanagerPlugin.config.executorCallbackClasses) {
            items.push(this.getCallbackSelect());
            items.push(this.getCallbackPredefinedConfig());
        }
        return items;
    }
});
