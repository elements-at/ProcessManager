pimcore.registerNS("pimcore.plugin.processmanager.helper.form");
pimcore.plugin.processmanager.helper.form = Class.create({

    specialFormFields : [],
    mandatoryFields : [],

    getFieldValue : function(fieldName){
        var value = '';
        if(this.rec){
            value = this.rec.get('extJsSettings').values[fieldName];
        }
        return value;
    },

    getCheckbox: function (fieldName, config) {
        if (typeof this[fieldName] == 'undefined') {
            config = defaultValue(config,{});
            this[fieldName] = new Ext.form.Checkbox({
                fieldLabel: t('plugin_pm_' + fieldName),
                xtype: "checkbox",
                name: fieldName,
                afterLabelTextTpl: this.getTooltip(config.tooltip),
                checked: this.getFieldValue(fieldName)
            });
        }
        return this[fieldName];
    },

    getDateField : function(fieldName, config) {
        config = defaultValue(config,{});
        return {
            xtype: 'datefield',
            fieldLabel: this.getFieldLabel(fieldName , config),
            name: fieldName,
            submitFormat: 'U',
            afterLabelTextTpl: this.getTooltip(config.tooltip),
            value: this.getFieldValue(fieldName)
        }
    },

    getLocaleSelection : function (fieldName,config) {
        config = defaultValue(config,{});
        var localestore = [];
        var websiteLanguages = pimcore.settings.websiteLanguages;
        var selectContent = "";
        for (var i = 0; i < websiteLanguages.length; i++) {
            selectContent = pimcore.available_languages[websiteLanguages[i]] + " [" + websiteLanguages[i] + "]";
            localestore.push([websiteLanguages[i], selectContent]);
        }

        return {
            xtype: "combo",
            name: fieldName,
            store: localestore,
            editable: false,
            width: '100%',
            triggerAction: 'all',
            mode: "local",
            afterLabelTextTpl: this.getTooltip(config.tooltip),
            fieldLabel: this.getFieldLabel(fieldName , config)
        };
    },

    getSelectField : function(fieldName,config){
        config = defaultValue(config,{});
        return {
            xtype: "combo",
            name: fieldName,
            store: config.store,
            editable: false,
            width: '100%',
            triggerAction: 'all',
            mode: "local",
            value : this.getFieldValue(fieldName),
            afterLabelTextTpl: this.getTooltip(config.tooltip),
            fieldLabel: this.getFieldLabel(fieldName , config)
        };
    },

    getLogLevelField : function(){
        return this.getSelectField('logLevel',{
            store : [
                ['DEBUG','DEBUG'],
                ['INFO','INFO'],
                ['NOTICE','NOTICE'],
                ['WARNING','WARNING'],
                ['ERROR','ERROR'],
                ['CRITICAL','CRITICAL'],
                ['ALERT','ALERT'],
                ['EMERGENCY','EMERGENCY']
            ],
            mandatory : true
        });
    },

    getTooltip : function (text) {
        return text ? '<img src="/pimcore/static6/img/flat-color-icons/info.svg" width="17" height="17" class="pm_tooltip_icon" data-qtip="' + text + '"></img>' : '';
    },

    getFieldLabel : function (fieldName, config) {
        config = defaultValue(config,{});
        var fieldLabel = t('plugin_pm_' + fieldName);
        if(config.mandatory){
            this.mandatoryFields.push(fieldName);
            fieldLabel += ' <span style="color:#f00;">*</span>';
        }
        return fieldLabel;
    },

    getTextField: function (fieldName, config) {
        config = defaultValue(config,{});

        return new Ext.form.TextField({
            fieldLabel: this.getFieldLabel(fieldName , config),
            width: '100%',
            name: fieldName,
            readOnly: false,
            afterLabelTextTpl: this.getTooltip(config.tooltip),
            value: this.getFieldValue(fieldName)
        });
    },

    getNumberField: function (fieldName, config) {
        if (typeof this[fieldName] == 'undefined') {
            config = defaultValue(config,{});
            this[fieldName] = new Ext.form.NumberField({
                fieldLabel: this.getFieldLabel(fieldName , config),
                name: fieldName,
                minValue: 0,
                afterLabelTextTpl: this.getTooltip(config.tooltip),
                value : this.getFieldValue(fieldName),
                cls : 'pm_number_select'
            });
        }
        return this[fieldName];
    },

    getTextArea : function(fieldName ,config){
        config = defaultValue(config,{});
        return new Ext.form.TextArea({
            fieldLabel: this.getFieldLabel(fieldName , config),
            width : '100%',
            name: fieldName,
            readOnly: false,
            afterLabelTextTpl: this.getTooltip(config.tooltip),
            value: this.getFieldValue(fieldName)
        });
    },

    getRoleSelection: function (fieldName, config) {
        config = defaultValue(config,{});
        var roles = processmanagerPlugin.config.roles;
        roles.unshift({
            'id' : 0,
            'name' : t('plugin_pm_role_admin')
        });
        var rolesStore = Ext.create('Ext.data.JsonStore', {
            fields: ["id","name"],
            data: roles
        });

        var value = this.getFieldValue(fieldName);
        if(value == ''){
            value = null;
        }
        this.roles = Ext.create('Ext.ux.form.MultiSelect', {
            name: fieldName,
            triggerAction:"all",
            editable:false,
            fieldLabel: this.getFieldLabel(fieldName, config),
            width:'100%',
            maxHeight : 100,
            store: rolesStore,
            displayField: "name",
            valueField: "id",
            afterLabelTextTpl: this.getTooltip(config.tooltip),
            value: value
        });

        return this.roles;
    },


    getItemSelector : function (fieldName,config) {
        config = defaultValue(config,{});
        this.specialFormFields.push({
            name : fieldName,
            type : 'grid'
        });

        var itemSelectorConfig = defaultValue(config.itemSelectorConfig,{});


        var store = Ext.create("Ext.data.ArrayStore", {
            fields: ["id", "path", "type"]
        });


        var itemIsAddable = function (data) {
            if(itemSelectorConfig.type){
                if(itemSelectorConfig.type.indexOf(data.elementType) == -1){
                    return false;
                }
            }

            if(itemSelectorConfig.specific && itemSelectorConfig.specific.classes) {
                if (itemSelectorConfig.specific.classes.indexOf(data.className) !== -1) {
                    return true;
                }else{
                    return false;
                }
            }else{
                return true;
            }

        };

        var buttons = [
            {
                xtype: "tbspacer",
                width: 20,
                height: 16,
                cls: "pimcore_icon_droptarget"
            },
            this.getFieldLabel(fieldName , config),
            "->",
            {
                xtype: "button",
                iconCls: "pimcore_icon_delete",
                handler: function () {
                    this['formElement' + fieldName].getStore().removeAll();
                }.bind(this)
            }
            ,
            {
                xtype: "button",
                iconCls: "pimcore_icon_search"
                ,
                handler: function () {
                    pimcore.helpers.itemselector(true, function (items) {
                        if (items.length > 0) {
                            for (var i = 0; i < items.length; i++) {
                                var item = items[i];
                                if(itemSelectorConfig.specific && itemSelectorConfig.specific.classes){
                                    if(itemSelectorConfig.specific.classes.indexOf(item.classname) !== -1){
                                        this['formElement' + fieldName].getStore().add({
                                            id: item.id,
                                            path: item.fullpath,
                                            type: item.type
                                        });
                                    }
                                }else{
                                    this['formElement' + fieldName].getStore().add({
                                        id: item.id,
                                        path: item.fullpath,
                                        type: item.type
                                    });
                                }
                            }
                        }
                    }.bind(this),itemSelectorConfig);
                }.bind(this)
            }
        ];

        if(config.buttons){
            for(var i = 0; i < config.buttons.length; i++){
                buttons.push(config.buttons[i]);
            }
        }

        this['formElement' + fieldName] = Ext.create("Ext.grid.Panel", {
            store: store,
            authHeight: true,
            minHeight: 50,
            maxHeight : 200,
            style: "margin: 15px 0",
            selModel: Ext.create("Ext.selection.RowModel"),
            columns: {
                defaults: {
                    sortable: false
                },
                items: [
                    {header: 'ID', dataIndex: 'id', width: 50},
                    {header: t("path"), dataIndex: 'path', flex: 200},
                    {header: t("type"), dataIndex: 'type', width: 100},
                    {
                        xtype: 'actioncolumn',
                        width: 30,
                        items: [{
                            tooltip: t('remove'),
                            icon: "/pimcore/static6/img/flat-color-icons/delete.svg",
                            handler: function (grid, rowIndex) {
                                grid.getStore().removeAt(rowIndex);
                            }.bind(this)
                        }]
                    }
                ]
            },
            tbar: buttons
        });

        this['formElement' + fieldName].on("afterrender", function () {
            var dropTargetEl = this['formElement' + fieldName].getEl();
            var gridDropTarget = new Ext.dd.DropZone(dropTargetEl, {
                ddGroup: 'element',
                getTargetFromEvent: function (e) {
                    return true;
                }.bind(this),

                onNodeOver: function (overHtmlNode, ddSource, e, data) {
                    return itemIsAddable(data.records[0].data) ? Ext.dd.DropZone.prototype.dropAllowed : Ext.dd.DropZone.prototype.dropNotAllowed;
                }.bind(this),

                onNodeDrop: function (target, dd, e, r) {
                    var data = r.records[0].data;
                    if(itemIsAddable(data)){
                        this['formElement' + fieldName].getStore().add({
                            id: data.id,
                            path: data.path,
                            type: data.elementType
                        });
                        return true;
                    }
                    return false;
                }.bind(this)
            });
        }.bind(this));


        return this['formElement' + fieldName];
    },

    formHasErrors : function (data) {
        var missingFields = [];
        for(var i = 0; i < this.mandatoryFields.length; i++){
            var validationMethod = 'formElementIsValid' + this.mandatoryFields[i].ucFirst();

            if(typeof this[validationMethod] == 'function'){
                if(!this[validationMethod](data[this.mandatoryFields[i]])){
                    missingFields.push(this.mandatoryFields[i]);
                }
            }else{
                var val = data[this.mandatoryFields[i]];
                if(typeof val == 'object'){
                    if(!val.length){
                        missingFields.push(this.mandatoryFields[i]);
                    }
                }else{
                    if(!val){
                        missingFields.push(this.mandatoryFields[i]);
                    }
                }
            }

        }
        return missingFields.length == 0 ? false : missingFields;
    },

    alertFormErrors :function (fields) {
        for(var i = 0; i < fields.length; i++){
            fields[i] = ' "' + this.getFieldLabel(fields[i]) + '"';
        }
        Ext.MessageBox.alert(t('plugin_pm_error_form_validation'), t('plugin_pm_error_form_fields') + '<br/><b>' + fields.join(', ') + '</b>');
    }
});