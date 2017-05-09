pimcore.registerNS("pimcore.plugin.processmanager.panel.config");
pimcore.plugin.processmanager.panel.config = Class.create({

    getPanel: function () {
        if (this.layout == null) {
            this.layout = new Ext.Panel({
                title: t("plugin_pm_processes"),
                border: false,
                icon: '/pimcore/static6/img/flat-color-icons/cable_release.svg',
                layout: "fit",
                region: "center"
            });

            this.createGrid();
        }

        return this.layout;
    },

    createGrid: function (response) {
        this.fields = ['id', 'name', 'type', 'description', 'command', 'cronJob', 'group', 'creationDate', 'modificationDate'];

        var readerFields = [];
        for (var i = 0; i < this.fields.length; i++) {
            readerFields.push({name: this.fields[i], allowBlank: true});
        }

        var itemsPerPage = pimcore.helpers.grid.getDefaultPageSize(-1);
        var url = "/admin/elementsprocessmanager/config/list?";

        this.store = pimcore.helpers.grid.buildDefaultStore(
            url,
            readerFields,
            itemsPerPage
        );
        this.pagingtoolbar = pimcore.helpers.grid.buildDefaultPagingToolbar(this.store,
            {
                pageSize: itemsPerPage
            });


        this.store.addListener("exception", function (conn, mode, action, request, response, store) {
            if (action == "update") {
                Ext.MessageBox.alert(t('error'), t('cannot_save_object_please_try_to_edit_the_object_in_detail_view'));
                this.store.rejectChanges();
            }
        }.bind(this));

        var gridColumns = [];

        gridColumns.push({header: "ID", width: 40, sortable: true, dataIndex: 'id', filter: 'numeric'});
        gridColumns.push(
            {
                header: t("type"),
                width: 200,
                sortable: false,
                dataIndex: 'type',
                renderer: function (value) {
                    return t('plugin_pm_executor_' + value);
                }
            }
        );
        gridColumns.push({
            header: t("plugin_pm_name"),
            width: 200,
            sortable: true,
            dataIndex: 'name',
            filter: 'string'
        });
        gridColumns.push({
            header: t("plugin_pm_group"),
            width: 100,
            sortable: true,
            dataIndex: 'group',
            filter: 'string'
        });
        gridColumns.push({
            header: t("plugin_pm_description"),
            flex: 300,
            sortable: true,
            dataIndex: 'description',
            filter: 'string'
        });
        gridColumns.push({
            header: t("plugin_pm_command"),
            flex: 400,
            sortable: false,
            dataIndex: 'command',
            hidden: true
        });
        gridColumns.push({
            header: t("plugin_pm_cronjob"),
            width: 100,
            sortable: false,
            dataIndex: 'cronJob',
            renderer: function (v) {
                return v;
            }
        });

        var dateRenderer = function (d) {
            if (d !== undefined) {
                var date = new Date(d * 1000);
                return Ext.Date.format(date, "Y-m-d H:i:s");
            } else {
                return "";
            }
        };

        gridColumns.push(
            {
                header: t("creationDate"), sortable: true, dataIndex: 'creationDate', editable: false, width: 150,
                hidden: true,
                renderer: dateRenderer
            }
        );

        gridColumns.push(
            {
                header: t("modificationDate"),
                sortable: true,
                dataIndex: 'modificationDate',
                editable: false,
                width: 150,
                hidden: true,
                renderer: dateRenderer
            }
        );

        if (pimcore.globalmanager.get("user").isAllowed("plugin_pm_permission_execute")) {

            if (pimcore.globalmanager.get("user").isAllowed("plugin_pm_permission_execute")) {
                gridColumns.push({
                    hideable: false,
                    xtype: 'actioncolumn',

                    header: t("plugin_pm_permission_execute_column_name"),
                    width: 60,
                    items: [
                        {
                            tooltip: t('plugin_pm_permission_execute'),
                            icon: "/pimcore/static6/img/flat-color-icons/go.svg",
                            handler: function (grid, rowIndex) {
                                processmanagerPlugin.executeJob(grid.getStore().getAt(rowIndex).get('id'));
                            },
                            getClass: function (v, meta, rec) {
                                if (!rec.get("active")) {
                                    return 'process-manager-hide-icon';
                                }
                            },
                        }
                    ]
                });
            }
        }

        if (pimcore.globalmanager.get("user").isAllowed("plugin_pm_permission_configure")) {
            gridColumns.push({
                hideable: true,
                xtype: 'actioncolumn',
                header: t("settings"),
                width: 60,
                items: [
                    {
                        tooltip: t('settings'),
                        icon: "/pimcore/static6/img/flat-color-icons/settings.svg",
                        handler: function (grid, rowIndex) {
                            var rec = grid.getStore().getAt(rowIndex);
                            var className = rec.get('extJsSettings').executorConfig.extJsClass;
                            var obj = eval('new ' + className);
                            obj.setRecord(rec);
                            obj.show();
                        }.bind(this)
                    }
                ]
            });

            gridColumns.push({
                hideable: true,
                xtype: 'actioncolumn',
                header: t("delete"),
                width: 60,
                items: [
                    {
                        tooltip: t('delete'),
                        icon: "/pimcore/static6/img/flat-color-icons/delete.svg",
                        handler: function (grid, rowIndex) {
                            var rec = grid.getStore().getAt(rowIndex);

                            var modal = new Ext.Window({
                                layout: 'fit',
                                width: 650,
                                height: 150,
                                closeAction: 'close',
                                modal: true,
                                title: t("plugin_pm_delete_headline"),
                                items: [{
                                    xtype: "panel",
                                    border: false,
                                    bodyStyle: "padding:20px;font-size:14px;",
                                    html: t("plugin_pm_delete_text") + " <strong>\"" + rec.get('name') + "\"</strong>?"
                                }],
                                buttons: [
                                    {
                                        text: t("plugin_pm_delete_confirm"),
                                        iconCls: "pimcore_icon_apply",
                                        handler: function () {

                                            var rec = grid.getStore().getAt(rowIndex);

                                            Ext.Ajax.request({
                                                url: "/admin/elementsprocessmanager/config/delete",
                                                params: {
                                                    id: rec.get("id")
                                                },
                                                success: function (response) {
                                                    Ext.getCmp('plugin_pm_config_list_panel').store.reload();
                                                    modal.close();
                                                }.bind(this)
                                            });
                                        }
                                    },
                                    {
                                        text: t("cancel"),
                                        iconCls: "pimcore_icon_cancel",
                                        handler: function () {
                                            modal.close();
                                        }.bind(this)
                                    }

                                ]
                            });
                            modal.show();
                        }.bind(this)
                    }
                ]
            });
            gridColumns.push({
                header: t('enable') + " / " + t("disable"),
                xtype: 'actioncolumn',
                width: 100,
                items: [{
                    tooltip: t('enable') + " / " + t("disable"),
                    getClass: function (v, meta, rec) {
                        var klass = "pimcore_action_column ";
                        if (rec.get("active")) {
                            klass += "pimcore_icon_stop  ";
                        } else {
                            klass += "pimcore_icon_add ";
                        }
                        return klass;
                    },
                    handler: function (grid, rowIndex) {

                        var rec = grid.getStore().getAt(rowIndex);
                        var value = rec.get("active") == 1 ? 0 : 1;
                        Ext.Ajax.request({
                            url: "/admin/elementsprocessmanager/config/activate-disable",
                            params: {
                                value: value,
                                id: rec.get("id")
                            },
                            success: function (response) {
                                var data = Ext.decode(response.responseText);
                                if (data.success) {
                                    this.store.reload();
                                } else {
                                    pimcore.helpers.showNotification(t("error"), t("plugin_pm_error_process_manager"), "error", t(data.message));
                                }
                            }.bind(this)
                        });
                    }.bind(this)
                }]
            });
        }

        if (pimcore.globalmanager.get("user").isAllowed("plugin_pm_permission_configure")) {

            this.toolbarButtons = {};

            var i = 0;
            for (var key in processmanagerPlugin.config.executorClasses) {
                if (processmanagerPlugin.config.executorClasses.hasOwnProperty(key)) {
                    var h = function (button) {
                        var obj = eval('new ' + processmanagerPlugin.config.executorClasses[this.executorType].extJsClass);
                        obj.setExecutorConfig(processmanagerPlugin.config.executorClasses[this.executorType]);
                        obj.show();
                    };

                    if (i == 0) {
                        this.toolbarButtons = new Ext.SplitButton({
                            text: t('plugin_pm_button_add_' + key),
                            iconCls: "pimcore_icon_add",
                            scale: "medium",
                            executorType: key,
                            handler: h,
                            menu: []
                        });
                    } else {
                        var item = {
                            text: t('plugin_pm_button_add_' + key),
                            iconCls: "pimcore_icon_add",
                            executorType: key,
                            handler: h
                        }
                        this.toolbarButtons.menu.add(item);
                    }
                    i++;
                }
            }
        }

        var plugins = ['pimcore.gridfilters'];


        var gridConfig = {
            frame: false,
            disableSelection: true,
            trackMouseOver: false,
            store: this.store,
            id: 'plugin_pm_config_list_panel',
            columns: gridColumns,
            loadMask: true,
            columnLines: true,
            bodyCls: "pimcore_editable_grid",
            stripeRows: true,
            plugins: plugins,
            viewConfig: {
                forceFit: false
            },
            bbar: this.pagingtoolbar,
            tbar: [
                this.toolbarButtons
            ]
        };

        this.grid = Ext.create('Ext.grid.Panel', gridConfig);

        this.store.load();

        this.layout.removeAll();
        this.layout.add(this.grid);
        this.layout.updateLayout();
    }

    /*,

     executeJob : function (grid, rowIndex) {
     var record = grid.getStore().getAt(rowIndex);
     var callbackClass = record.get('extJsSettings').values.callback;
     if(callbackClass){
     this.callback = eval('new ' + callbackClass + '(grid, rowIndex)');
     }else{
     this.callback = new pimcore.plugin.processmanager.executor.callback.default(grid, rowIndex);
     }
     this.callback.reset();
     this.callback.setRecord(record);
     this.callback.execute();
     }*/

});

