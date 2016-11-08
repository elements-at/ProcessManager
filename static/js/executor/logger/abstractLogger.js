pimcore.registerNS("pimcore.plugin.processmanager.executor.logger.abstractLogger");
pimcore.plugin.processmanager.executor.logger.abstractLogger = Class.create(pimcore.plugin.processmanager.helper.form,{
    values : {},
    getTopBar: function (niceName, id) {
        return [{
            xtype: "tbtext",
            text: "<b>" + niceName + "</b>"
        },
        "->",
        {
            iconCls: "pimcore_icon_delete",
            handler: this.removeForm.bind(this, id)
        }
        ];
    },

    setValues : function(values){
        this.values = values;
    },

    removeForm: function (id) {
        Ext.getCmp('plugin_pm_logger_panel').remove(Ext.getCmp(id));
    },

    getFieldValue: function (fieldName) {
        if(this.values){
            return this.values[fieldName];
        }
    },

    addForm : function(){
        Ext.getCmp('plugin_pm_logger_panel').add(this.getForm());
        this.form.updateLayout();
        Ext.getCmp('plugin_pm_logger_panel').updateLayout();
        return this.form;
    },

    getButton : function(){
        this.button = {
            iconCls: "pimcore_icon_add",
            text: t("plugin_pm_logger_" + this.type),
            "handler" : this.addForm.bind(this)
        }
        return this.button;
    },

    stopRefresh : function () {
        Ext.TaskManager.stop(pimcore.globalmanager.get("plugin_pm_cnf").monitoringItems.autoRefreshTask);
    },

    startRefresh : function () {
        if(pimcore.globalmanager.get("plugin_pm_cnf").monitoringItems.autoRefresh.getValue()){
            Ext.TaskManager.start(pimcore.globalmanager.get("plugin_pm_cnf").monitoringItems.autoRefreshTask);
        }
    }
});