pimcore.registerNS("pimcore.plugin.processmanager.executor.action.abstractAction");
pimcore.plugin.processmanager.executor.action.abstractAction = Class.create({
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
        Ext.getCmp('plugin_pm_action_panel').remove(Ext.getCmp(id));
    },

    getFieldValue: function (fieldName) {
        if(this.values){
            return this.values[fieldName];
        }
    },

    addForm : function(){
        Ext.getCmp('plugin_pm_action_panel').add(this.getForm());
        this.form.updateLayout();
        Ext.getCmp('plugin_pm_action_panel').updateLayout();
        return this.form;
    },

    /**
     * Implement in extended class if needed
     */
    executeActionForActiveProcessList : function () {

    }
});