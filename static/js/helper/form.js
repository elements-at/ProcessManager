pimcore.registerNS("pimcore.plugin.processmanager.helper.form");
pimcore.plugin.processmanager.helper.form = Class.create({

    getFieldValue : function(fieldName){
        var value = '';
        if(this.rec){
            value = this.rec.get('extJsSettings').values[fieldName];
        }
        return value;
    },

    getCheckbox: function (fieldName) {
        if (typeof this[fieldName] == 'undefined') {
            this[fieldName] = new Ext.form.Checkbox({
                fieldLabel: t('plugin_pm_' + fieldName),
                xtype: "checkbox",
                name: fieldName,
                checked: this.getFieldValue(fieldName)
            });
        }
        return this[fieldName];
    },

    getDateField : function(fieldName) {
        return {
            xtype: 'datefield',
            fieldLabel: t("plugin_pm_" + fieldName),
            name: fieldName,
            submitFormat: 'U',
            value: this.getFieldValue(fieldName)
        }
    },

    getLocaleSelection : function () {
        var localestore = [];
        var websiteLanguages = pimcore.settings.websiteLanguages;
        var selectContent = "";
        for (var i = 0; i < websiteLanguages.length; i++) {
            selectContent = pimcore.available_languages[websiteLanguages[i]] + " [" + websiteLanguages[i] + "]";
            localestore.push([websiteLanguages[i], selectContent]);
        }

        return {
            xtype: "combo",
            name: "locale",
            store: localestore,
            editable: false,
            width: '100%',
            triggerAction: 'all',
            mode: "local",
            fieldLabel: t('plugin_pm_locale')
        };
    },

    getSelectField : function(fieldName,store,options){

        var fieldLabel = t('plugin_pm_' + fieldName);
        if(options){
            if(options.mandatory){
                fieldLabel += ' <span style="color:#f00;">*</span>';
            }
        }

        return {
            xtype: "combo",
            name: fieldName,
            store: store,
            editable: false,
            width: '100%',
            triggerAction: 'all',
            mode: "local",
            value : this.getFieldValue(fieldName),
            fieldLabel: fieldLabel
        };
    },

    getLogLevelField : function(){
        return this.getSelectField('logLevel',[
            ['DEBUG','DEBUG'],
            ['INFO','INFO'],
            ['NOTICE','NOTICE'],
            ['WARNING','WARNING'],
            ['ERROR','ERROR'],
            ['CRITICAL','CRITICAL'],
            ['ALERT','ALERT'],
            ['EMERGENCY','EMERGENCY']
        ],{mandatory : true});
    },

    getTextField : function(fieldName){
       return new Ext.form.TextField({
                fieldLabel: t("plugin_pm_" + fieldName),
                width : '100%',
                name: fieldName,
                readOnly: false,
                value: this.getFieldValue(fieldName)
            });
    },

    getNumberField: function (fieldName) {
        if (typeof this[fieldName] == 'undefined') {
            this[fieldName] = new Ext.form.NumberField({
                fieldLabel: t("plugin_pm_" + fieldName),
                name: fieldName,
                minValue: 0,
                value : this.getFieldValue(fieldName)
            });
        }
        return this[fieldName];
    },

    getTextArea : function(fieldName){
        return new Ext.form.TextArea({
            fieldLabel: t("plugin_pm_" + fieldName),
            width : '100%',
            name: fieldName,
            readOnly: false,
            value: this.getFieldValue(fieldName)
        });
    },

    getTextFieldName: function(){
        return new Ext.form.TextField({
            fieldLabel: t('plugin_pm_name')  + ' <span style="color:#f00;">*</span>',
            width : "100%",
            name: 'name',
            readOnly: false,
            value: this.getFieldValue('name')
        });
    },
});