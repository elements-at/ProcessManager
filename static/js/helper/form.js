pimcore.registerNS("pimcore.plugin.processmanager.helper.form");
pimcore.plugin.processmanager.helper.form = Class.create({

    getFieldValue : function(fieldName){
        var value = '';
        if(this.rec){
            value = this.rec.get('settings').values[fieldName];
        }
        return value;
    },

    getCheckbox: function (fieldName) {
        if (typeof this[fieldName] == 'undefined') {
            this[fieldName] = new Ext.form.Checkbox({
                fieldLabel: t(fieldName),
                xtype: "checkbox",
                name: fieldName,
                checked: this.getFieldValue(fieldName)
            });
        }
        return this[fieldName];
    },

    getNumberField: function (fieldName) {
        if (typeof this[fieldName] == 'undefined') {
            this[fieldName] = new Ext.form.NumberField({
                fieldLabel: t(fieldName),
                name: fieldName,
                minValue: 0,
                value : this.getFieldValue(fieldName)
            });
        }
        return this[fieldName];
    },

    getTextArea : function(fieldName){
        if(typeof this[fieldName] == 'undefined'){

            this[fieldName] = new Ext.form.TextArea({
                fieldLabel: t(fieldName),
                width : '100%',
                name: fieldName,
                readOnly: false,
                value: this.getFieldValue(fieldName)
            });
        }

        return this[fieldName];
    },

    getTextFieldName: function(){
        return new Ext.form.TextField({
            fieldLabel: t('name')  + ' <span style="color:#f00;">*</span>',
            width : "100%",
            name: 'name',
            readOnly: false,
            value: this.getFieldValue('name')
        });
    },
});