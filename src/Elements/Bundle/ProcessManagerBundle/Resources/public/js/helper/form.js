pimcore.registerNS("pimcore.plugin.processmanager.helper.form");
pimcore.plugin.processmanager.helper.form = Class.create({

    specialFormFields : [],
    mandatoryFields : [],
    labelWidth : 120,

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
            this[fieldName] = new Ext.form.Checkbox(this.mergeConfigs({
                fieldLabel: this.getFieldLabel(fieldName,config),
                labelWidth: defaultValue(config.labelWidth,this.labelWidth),
                xtype: "checkbox",
                name: fieldName,
                afterLabelTextTpl: this.getTooltip(config.tooltip),
                checked: this.getFieldValue(fieldName)
            },config));
        }
        return this[fieldName];
    },

    getDateField : function(fieldName, config) {
        config = defaultValue(config,{});
        this.specialFormFields.push({
            name : fieldName,
            type : 'date'
        });

        var val = this.getFieldValue(fieldName);
        return this.mergeConfigs({
            xtype: 'datefield',
            fieldLabel: this.getFieldLabel(fieldName , config),
            labelWidth: defaultValue(config.labelWidth,this.labelWidth),
            name: fieldName,
            submitFormat: 'U',
            afterLabelTextTpl: this.getTooltip(config.tooltip),
            value: val
        },config);
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

        return this.mergeConfigs({
            xtype: "combo",
            name: fieldName,
            store: localestore,
            labelWidth: defaultValue(config.labelWidth,this.labelWidth),
            editable: false,
            width: '100%',
            triggerAction: 'all',
            mode: "local",
            afterLabelTextTpl: this.getTooltip(config.tooltip),
            fieldLabel: this.getFieldLabel(fieldName , config)
        },config);
    },

    getLocaleSelectionMultiSelect : function (fieldName,config) {
        config = defaultValue(config,{});
        var locales = [];
        var websiteLanguages = pimcore.settings.websiteLanguages;
        var selectContent = "";
        for (var i = 0; i < websiteLanguages.length; i++) {
            selectContent = pimcore.available_languages[websiteLanguages[i]] + " [" + websiteLanguages[i] + "]";
            locales.push({locale : websiteLanguages[i], name : selectContent});
        }

        var localestore = Ext.create('Ext.data.JsonStore', {
            fields: ["locale","name"],
            data: locales,
            sorters: [{
                property: 'name',
                direction: 'ASC'
            }]
        });

        var value = this.getFieldValue(fieldName);
        if(value == ''){
            value = null;
        }
        return Ext.create('Ext.ux.form.MultiSelect', this.mergeConfigs({
            name: fieldName,
            triggerAction:"all",
            editable:false,
            labelWidth: defaultValue(config.labelWidth,this.labelWidth),
            fieldLabel: this.getFieldLabel(fieldName, config),
            width:'100%',
            height : defaultValue(config.height,100),
            store: localestore,
            displayField: "name",
            valueField: "locale",
            afterLabelTextTpl: this.getTooltip(config.tooltip),
            value: value
        },config));
    },

    mergeConfigs : function (defaultConfig,customConfig) {
        return $.extend(defaultConfig, customConfig);
    },

    getSelectField : function(fieldName,config){
        config = defaultValue(config,{});
        return this.mergeConfigs({
            xtype: "combo",
            name: fieldName,
            store: config.store,
            labelWidth: defaultValue(config.labelWidth,this.labelWidth),
            editable: false,
            width: '100%',
            triggerAction: 'all',
            mode: "local",
            value : this.getFieldValue(fieldName),
            afterLabelTextTpl: this.getTooltip(config.tooltip),
            fieldLabel: this.getFieldLabel(fieldName , config)
        },config);
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

        return new Ext.form.TextField(this.mergeConfigs({
            fieldLabel: this.getFieldLabel(fieldName , config),
            width: '100%',
            name: fieldName,
            labelWidth: defaultValue(config.labelWidth,this.labelWidth),
            readOnly: false,
            afterLabelTextTpl: this.getTooltip(config.tooltip),
            value: this.getFieldValue(fieldName)
        },config));
    },

    getNumberField: function (fieldName, config) {
        if (typeof this[fieldName] == 'undefined') {
            config = defaultValue(config,{});
            this[fieldName] = new Ext.form.NumberField(this.mergeConfigs({
                fieldLabel: this.getFieldLabel(fieldName , config),
                labelWidth: defaultValue(config.labelWidth,this.labelWidth),
                name: fieldName,
                minValue: 0,
                afterLabelTextTpl: this.getTooltip(config.tooltip),
                value : this.getFieldValue(fieldName),
                cls : 'pm_number_select'
            },config));
        }
        return this[fieldName];
    },

    getTextArea : function(fieldName ,config){
        config = defaultValue(config,{});
        return new Ext.form.TextArea(this.mergeConfigs({
            fieldLabel: this.getFieldLabel(fieldName , config),
            labelWidth: defaultValue(config.labelWidth,this.labelWidth),
            width : '100%',
            name: fieldName,
            readOnly: false,
            afterLabelTextTpl: this.getTooltip(config.tooltip),
            value: this.getFieldValue(fieldName)
        },config));
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
        this.roles = Ext.create('Ext.ux.form.MultiSelect', this.mergeConfigs({
            name: fieldName,
            triggerAction:"all",
            editable:false,
            labelWidth: defaultValue(config.labelWidth,this.labelWidth),
            fieldLabel: this.getFieldLabel(fieldName, config),
            width:'100%',
            height : defaultValue(config.height,100),
            store: rolesStore,
            displayField: "name",
            valueField: "id",
            afterLabelTextTpl: this.getTooltip(config.tooltip),
            value: value
        },config));

        return this.roles;
    },

    getHref : function (fieldName, config) {
        config = defaultValue(config,{});
        this.specialFormFields.push({
            name : fieldName,
            type : 'href'
        });

        var href = {
            fieldLabel: this.getFieldLabel(fieldName,config),
            afterLabelTextTpl: this.getTooltip(config.tooltip),
            name: fieldName,
            cls: "object_field",
            fieldCls : 'pimcore_droptarget_input',
            labelWidth: defaultValue(config.labelWidth,this.labelWidth)
        };

        if (this.data) {
            if (this.data.path) {
                href.value = this.data.path;
            }
        }

        if(config.width) {
            href.width = config.width;
        }else{
            href.flex = true;
        }
        var itemSelectorConfig = this.getItemSelectorConfig(config);
        itemSelectorConfig.allowObjectFolder = true;


        var doDelete = function () {
            this['formElement' + fieldName].itemData = null;
            this['formElement' + fieldName].setValue('');
        };

        var doOpenElement = function (){
            var data = this['formElement' + fieldName].itemData;
            if(data && data.id) {
                pimcore.helpers.openElement(data.id, data.type);
            }
        };

        var doSearch = function () {
            pimcore.helpers.itemselector(false, function (item) {
                if(this.itemSelectorDndAllowed(item,itemSelectorConfig)){
                    var itemData = {
                        id: item.id,
                        path: item.fullpath,
                        type: item.type
                    };
                    this['formElement' + fieldName].itemData = itemData;
                    this['formElement' + fieldName].setValue(itemData.path);
                }
            }.bind(this),itemSelectorConfig);
        };


        this['formElement' + fieldName] = new Ext.form.TextField(href);

        this['formElement' + fieldName].on("render", function (el) {

            // add drop zone
            new Ext.dd.DropZone(el.getEl(), {
                reference: this,
                ddGroup: "element",
                getTargetFromEvent: function(e) {
                    return true;
                },
                onNodeOver: function (overHtmlNode, ddSource, e, data) {
                    return this.itemSelectorDndAllowed(data.records[0].data,itemSelectorConfig) ? Ext.dd.DropZone.prototype.dropAllowed : Ext.dd.DropZone.prototype.dropNotAllowed;
                }.bind(this),

                onNodeDrop: function (target, dd, e, r) {
                    var data = r.records[0].data;
                    if(this.itemSelectorDndAllowed(data,itemSelectorConfig)){
                        var itemData = {
                            id: data.id,
                            path: data.path,
                            type: data.elementType
                        };
                        this['formElement' + fieldName].itemData = itemData;
                        this['formElement' + fieldName].setValue(itemData.path);
                        return true;
                    }
                    return false;
                }.bind(this)
            });


            el.getEl().on("contextmenu", function (e) {
                var menu = new Ext.menu.Menu();
                menu.add(new Ext.menu.Item({
                    text: t('empty'),
                    iconCls: "pimcore_icon_delete",
                    handler: doDelete.bind(this)
                }));

                menu.add(new Ext.menu.Item({
                    text: t('open'),
                    iconCls: "pimcore_icon_open",
                    handler: doOpenElement.bind(this)
                }));

                menu.add(new Ext.menu.Item({
                    text: t('search'),
                    iconCls: "pimcore_icon_search",
                    handler: doSearch.bind(this)
                }));

                menu.showAt(e.getXY());

                e.stopEvent();

            }.bind(this));

        }.bind(this));



        var composite = Ext.create('Ext.form.FieldContainer', {
            layout: 'hbox',
            items: [
                this['formElement' + fieldName],
                {
                    xtype: "button",
                    iconCls: "pimcore_icon_edit",
                    handler: doOpenElement.bind(this)
                },
                {
                    xtype: "button",
                    iconCls: "pimcore_icon_delete",
                    style: "margin-left: 5px",
                    handler: doDelete.bind(this)
                },
                {
                    xtype: "button",
                    iconCls: "pimcore_icon_search",
                    style: "margin-left: 5px",
                    handler: doSearch.bind(this)
                }
            ],
            componentCls: "object_field",
            border: false,
            style: {
                padding: 0
            }
        });

        return composite;

    },


    getItemSelectorConfig : function (config) {
        //create default restritions
        var possibleClassRestrictions = [];
        var classStore = pimcore.globalmanager.get("object_types_store");
        classStore.each(function (rec) {
            possibleClassRestrictions.push(rec.data.text);
        });

        var restrictionDefaults = {
            type: ["document","asset","object"],
            subtype: {
                document: ["page", "snippet","folder","link","hardlink","email"], //email added by ckogler
                asset: ["folder", "image", "text", "audio", "video", "document", "archive", "unknown"],
                object: ["object", "folder", "variant"]
            },
            specific: {
                classes: possibleClassRestrictions // put here all classes from global class store ...
            }
        };
        return Ext.applyIf(defaultValue(config.itemSelectorConfig,{}), restrictionDefaults);
    },

    itemSelectorDndAllowed : function (data,itemSelectorConfig) {
        var type = data.elementType || data.type;

        if(itemSelectorConfig.type){
            if(itemSelectorConfig.type.indexOf(type) == -1){
                return false;
            }
        }

        if(type == 'object' && itemSelectorConfig.specific.classes) {
            if(data.type == 'folder' && !itemSelectorConfig.allowObjectFolder){
                return false;
            }

            if(data.className){
                if(itemSelectorConfig.specific.classes.indexOf(data.className) !== -1){
                    return true;
                }else{
                    return false;
                }
            }
            return true;
        }else{
            return true;
        }
    },

    getItemSelector : function (fieldName,config) {
        config = defaultValue(config,{});
        this.specialFormFields.push({
            name : fieldName,
            type : 'grid'
        });

        var itemSelectorConfig = this.getItemSelectorConfig(config);

        var store = Ext.create("Ext.data.ArrayStore", {
            fields: ["id", "path", "type"]
        });

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

                                if(this.itemSelectorDndAllowed(item,itemSelectorConfig)){
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
                    return this.itemSelectorDndAllowed(data.records[0].data,itemSelectorConfig) ? Ext.dd.DropZone.prototype.dropAllowed : Ext.dd.DropZone.prototype.dropNotAllowed;
                }.bind(this),

                onNodeDrop: function (target, dd, e, r) {
                    var data = r.records[0].data;
                    if(this.itemSelectorDndAllowed(data,itemSelectorConfig)){
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

    getPropertySelector : function (fieldName,config) {
        config = defaultValue(config,{});

        this.specialFormFields.push({
            name : fieldName,
            type : 'propertySelector'
        });

        var url = config.storeUrl || '/plugin/ProcessManager/index/property-list';

        if(url.indexOf('?') == -1){
            url += '?';
        }
        url += 'fieldName=' + fieldName;

        var store = new Ext.data.Store({
            proxy: {
                url: url,
                type: 'ajax',
                reader: {
                    type: 'json',
                    rootProperty: "data"
                }
            }
            /*,
            fields: ["id","name","description","settings","type"]*/
        });
        store.load();

        var columns = defaultValue(config.columns,[
            {
                text: t("plugin_pm_property_selector_propertyname"),
                sortable: true,
                dataIndex: "name",
                flex: 1
            }
        ]);


        var groupName1 = 'pm_drag_group_property_selector_' + fieldName;
        var groupName2 = 'pm_drop_group_property_selector_' + fieldName;

        var minHeight = defaultValue(config.minHeight,150);
        var maxHeight = defaultValue(config.maxHeight,250);

        var sourcePanel = Ext.create("Ext.grid.Panel", {
            title: t("plugin_pm_property_selector_source_property"),
            cls : 'plugin_pm_property_selector_panel_source',
            minHeight: minHeight,
            maxHeight: maxHeight,
            multiSelect: true,
            flex: 1,
            viewConfig: {
                plugins: {
                    ptype: "gridviewdragdrop",
                    containerScroll: true,
                    dragGroup: groupName1,
                    dropGroup: groupName2
                }
            },
            sortable: true,
            store: store,
            columns: columns,
            stripeRows: true
        });

        var targetPanel = Ext.create("Ext.grid.Panel", {
            title: t("plugin_pm_property_selector_target_property"),
            cls : 'plugin_pm_property_selector_panel_target',
            minHeight: minHeight,
            maxHeight: maxHeight,
            flex: 1,
            viewConfig: {
                plugins: {
                    ptype: "gridviewdragdrop",
                    containerScroll: true,
                    dragGroup: groupName2,
                    dropGroup: groupName1
                }
            },
            store: Ext.create("Ext.data.ArrayStore"),
            columns: columns,
            stripeRows: true
        });

        this['formElement' + fieldName] = Ext.create("Ext.panel.Panel", {
            title: this.getFieldLabel(fieldName,config),
            flex: 1,
            width : defaultValue(config.panelWidth,'100%'),
            layout: {
                type: "hbox",
                align: "stretch"
            },
            border: true,
            items: [sourcePanel, targetPanel],
            cls : 'plugin_pm_property_selector_panel'
        });

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
                if(Array.isArray(val)){
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
    },

    getStorageValueDate : function (name) {
        return this.formPanel.getForm().findField(name).getSubmitValue();
    },

    setStorageValueDate : function (name,data) {
        var timestamp = data[name];
        if(timestamp){
            this.formPanel.getForm().findField(name).setValue(new Date(timestamp*1000));
        }
    },

    getStorageValueGrid : function (name) {
        var elementData = [];
        this['formElement' + name].getStore().each(function(rec) {
                elementData.push(rec.data);
            }
        );
        return elementData;
    },

    setStorageValueGrid : function (name,data) {
        var d = data[name];
        if(d){
            this['formElement' + name].getStore().removeAll();
            for(var i = 0; i < d.length;i++){
                this['formElement' + name].getStore().add(d[i]);
            }
        }
    },

    getStorageValuePropertySelector : function (name) {
        var elementData = [];
        var targetPanel = this['formElement' + name].items.items[1];
        targetPanel.getStore().each(function(rec) {
                elementData.push(rec.data);
            }
        );
        return elementData;
    },

    setStorageValuePropertySelector : function (name,data) {
        var d = data[name];
        if(d){
            var targetPanel = this['formElement' + name].items.items[1];
            for(var i = 0; i < d.length;i++){
                targetPanel.getStore().add(d[i]);
            }
        }
    },

    getStorageValueHref : function (name,data) {
        return this['formElement' + name].itemData;
    },

    setStorageValueHref : function (name,data) {
        var itemData = data[name];
        if(itemData){
            this['formElement' + name].itemData = itemData;
            this['formElement' + name].setValue(itemData.path);
        }
    }
});