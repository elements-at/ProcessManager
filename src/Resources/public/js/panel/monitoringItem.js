pimcore.registerNS("pimcore.plugin.processmanager.panel.monitoringItem");

pimcore.plugin.processmanager.panel.monitoringItem = Class.create({
    refreshInterval: 5,

    getPanel: function () {
        if (this.layout == null) {
            this.layout = new Ext.Panel({
                title: t("plugin_pm_monitoring_items"),
                border: false,
                icon: '/bundles/pimcoreadmin/img/flat-color-icons/rules.svg',
                layout: "fit",
                region: "center"
            });

            this.createGrid();
        }

        this.layout.on("activate", this.panelActivated.bind(this));
        this.layout.on("deactivate", this.panelDeActivated.bind(this));

        return this.layout;
    },

    panelDeActivated: function () {
        Ext.TaskManager.stop(this.autoRefreshTask);
    },

    panelActivated: function () {
        Ext.TaskManager.start(this.autoRefreshTask);
    },

    createGrid: function (response) {
        this.fields = ['id', 'logFile', 'configurationId', 'command', 'name', 'status', 'duration', 'executedByUser', 'steps', 'creationDate', 'modificationDate', 'progress', 'action', 'callbackSettings'];

        var readerFields = [];
        for (var i = 0; i < this.fields.length; i++) {
            readerFields.push({name: this.fields[i]});
        }

        var itemsPerPage = pimcore.helpers.grid.getDefaultPageSize(-1);
        var url = "/admin/elementsprocessmanager/monitoring-item/list?";

        this.store = pimcore.helpers.grid.buildDefaultStore(
            url,
            readerFields,
            itemsPerPage
        );


        this.intervalInSeconds = {
            xtype: "numberfield",
            name: "interval",
            width: 70,
            value: 5,
            minValue: 0,
            listeners: {
                change: function (item, value) {
                    Ext.TaskManager.stop(this.autoRefreshTask);
                    var value = value * 1;
                    if (value > 0 && this.autoRefresh && this.autoRefresh.getValue()) {
                        this.autoRefreshTask.interval = value * 1000;
                        Ext.TaskManager.start(this.autoRefreshTask);
                    }
                }.bind(this)
            }
        }

        this.autoRefreshTask = {
            run: function () {
                this.store.reload({
                    callback: function (records, operation, success) {
                        // do not retry refreshing in an endless loop when requests are failing
                        if (!success) {
                            Ext.TaskManager.stop(this.autoRefreshTask);
                        }
                    }.bind(this)
                });
            }.bind(this),
            interval: (this.refreshInterval * 1000)
        }


        this.autoRefresh = new Ext.form.Checkbox({
            stateful: true,
            stateId: 'plugin_pm_auto_refresh',
            stateEvents: ['click'],
            checked: true,
            boxLabel: t('plugin_pm_auto_refresh'),
            listeners: {
                change: function (cbx, checked) {
                    if (checked) {
                        Ext.TaskManager.start(this.autoRefreshTask);
                    } else {
                        Ext.TaskManager.stop(this.autoRefreshTask);
                    }
                }.bind(this)
            }
        });

        this.showHiddenMonitoringItems = new Ext.form.Checkbox({
            stateful: true,
            stateId: 'plugin_pm_show_hidden_monitoring_items',
            stateEvents: ['click'],
            checked : false,
            boxLabel: t('plugin_pm_show_hidden_monitoring_items'),
            listeners: {
                "change": function (field, checked) {
                    // this.grid.filters.clearFilters();
                    this.store.getProxy().setExtraParam("showHidden", checked);
                    this.store.reload();
                }.bind(this)
            }
        });


        this.pagingtoolbar = this.getPagingToolbar(this.store,
            {
                pageSize: itemsPerPage
            });

        var listeners = {};

        this.store.addListener("exception", function (conn, mode, action, request, response, store) {
            if (action == "update") {
                Ext.MessageBox.alert(t('error'), t('cannot_save_object_please_try_to_edit_the_object_in_detail_view'));
                this.store.rejectChanges();
            }
        }.bind(this));

        var gridColumns = [];

        gridColumns.push({header: "ID", width: 70, sortable: true, dataIndex: 'id', filter: 'numeric',
            renderer: function (v,meta, record) {
                var data = record.getData();
                if(data.parentId){
                    v += '/' + data.parentId;
                }
                return v;
            }
        });
        gridColumns.push({header: "Parent ID", width: 70, sortable: true, dataIndex: 'parentId', filter: 'numeric',hidden: true});

        gridColumns.push({
            header: "Configuration ID",
            width: 40,
            hidden: true,
            sortable: true,
            dataIndex: 'configurationId',
            filter: 'numeric'
        });
        gridColumns.push({
            header: "PID",
            width: 100,
            hidden: true,
            sortable: true,
            dataIndex: 'pid',
            filter: 'numeric'
        });
        gridColumns.push({
            header: t("plugin_pm_command"),
            width: 800,
            sortable: true,
            dataIndex: 'command',
            hidden: true,
            filter: 'string'
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
                header: t("plugin_pm_monitor_creationDate"),
                sortable: true,
                dataIndex: 'creationDate',
                editable: false,
                hidden: true,
                width: 150,
                filter: {
                    type: 'date',
                    dateFormat: 'timestamp'
                },
                dateFormat: 'timestamp',
                renderer: dateRenderer
            }
        );

        gridColumns.push(
            {
                header: t("plugin_pm_monitor_modificationDate"),
                sortable: true,
                dataIndex: 'modificationDate',
                editable: false,
                width: 150,
                filter: {
                    type: 'date',
                    dateFormat: 'timestamp'
                },
                renderer: dateRenderer
            }
        );

        gridColumns.push({header: t("name"), width: 200, sortable: true, dataIndex: 'name', filter: 'string'});
        gridColumns.push({
            header: t("plugin_pm_executedByUser"),
            width: 100,
            sortable: false,
            dataIndex: 'executedByUser',
            filter: 'string'
        });
        gridColumns.push({header: t("group"), width: 100, sortable: false, dataIndex: 'group', filter: 'string'});
        gridColumns.push({header: t("status"), width: 100, sortable: true, dataIndex: 'status', filter: 'string'});
        gridColumns.push({header: t("steps"), width: 50, sortable: false, dataIndex: 'steps'});
        gridColumns.push({header: t("plugin_pm_monitor_duration"), width: 100, dataIndex: 'duration'});

        gridColumns.push({
            header: 'Progress',
            dataIndex: 'progress',
            sortable: false,
            width: 110
        });

        gridColumns.push({
            header: t("plugin_pm_message"),
            flex: 30,
            sortable: true,
            dataIndex: 'message',
            filter: 'string'
        });

        gridColumns.push({
            header: t('plugin_pm_callback_settings'),
            sortable: false,
            dataIndex: 'callbackSettings',
            width: 400,
            hidden: true,
            renderer: function (v) {
                return v;
            }
        });

        gridColumns.push({
            header: t('plugin_pm_metaData'),
            sortable : false,
            dataIndex: 'metaData',
            width : 400,
            hidden : true,
            renderer: function (v) {
                return v;
            }
        });

        gridColumns.push({header: t("action"), width: 50, dataIndex: 'action', sortable: false});
        gridColumns.push({
            header: t("plugin_pm_log"),
            dataIndex: 'logger',
            sortable: false,
            width: 50
        });
        gridColumns.push({
            header: t("plugin_pm_details"),
            xtype: 'actioncolumn',
            width: 50,
            sortable: false,
            items: [
                {
                    icon   : '/bundles/pimcoreadmin/img/flat-color-icons/about.svg',                // Use a URL in the icon config
                    tooltip: 'Details',
                    handler: function(grid, rowIndex, colIndex,item,e) {
                        var rec = grid.store.getAt(rowIndex);
                        new pimcore.plugin.processmanager.window.detailwindow(rec.getData());
                    }
                }
            ]
        });

        gridColumns.push({
            header: t("plugin_pm_retry"),
            width: 50,
            sortable: false,
            renderer: function (v, x, record) {
                if (record.get('retry')) {
                    return '<a href="#" onClick="processmanagerPlugin.monitoringItemRestart(' + record.get('id') + ')"><img src="/bundles/pimcoreadmin/img/flat-color-icons/refresh.svg" height="18" title="Restart" /></a>';
                } else {
                    return '<a href="#" onClick="processmanagerPlugin.monitoringItemCancel(' + record.get('id') + ')"><img src="/bundles/pimcoreadmin/img/flat-color-icons/cancel.svg" height="18" title="Stop"/></a>';
                }
                return '';
            }
        });

        if (pimcore.globalmanager.get("user").isAllowed("plugin_pm_permission_execute")) {
            gridColumns.push({
                xtype: 'actioncolumn',
                header: t("delete"),
                sortable: false,
                width: 50,
                items: [{
                    tooltip: t('delete'),
                    icon: "/bundles/pimcoreadmin/img/icon/cross.png",
                    handler: function (grid, rowIndex) {
                        var rec = grid.getStore().getAt(rowIndex);
                        Ext.Ajax.request({
                            url: '/admin/elementsprocessmanager/monitoring-item/delete',
                            success: function (response) {
                                var data = Ext.decode(response.responseText);
                                if (!data.success) {
                                    alert("Could not delete monitoring item");
                                }
                            },
                            failure: function () {
                                alert("Could not delete monitoring item");
                            },
                            params: {
                                id: rec.get('id')
                            }
                        });
                        grid.getStore().removeAt(rowIndex);
                    }.bind(this)
                }]
            });
        }

        var plugins = ['pimcore.gridfilters'];

        var tbar = []
        tbar.push(this.autoRefresh);
        tbar.push(this.intervalInSeconds);
        tbar.push(t("plugin_pm_auto_refresh_seconds"));

        var clearMonitoringItems = new Ext.Button({
            icon: "/bundles/pimcoreadmin/img/icon/cross.png",
            text: t("plugin_pm_clear_monitoring_items"),
            enableToggle: false,
            handler: this.clearMonitoringItems.bind(this)
        });
        tbar.push(clearMonitoringItems);
        tbar.push("->");


        tbar.push(this.showHiddenMonitoringItems);
        var gridConfig = {
            frame: false,
            id: 'plugin_pmmonitoring_item_list_panel',
            store: this.store,
            columns: gridColumns,
            remoteFilter: true,
            loadMask: true,
            columnLines: true,
            stripeRows: true,
            bodyCls: "pimcore_editable_grid",
            plugins: plugins,
            viewConfig: {
                forceFit: false,
                loadMask: false,
                getRowClass: function (record) {
                    return 'plugin-process-manager-status-' + record.get('status');
                }
            },
            listeners: {
                rowdblclick: function (grid, record, tr, rowIndex, e, eOpts) {
                    new pimcore.plugin.processmanager.window.detailwindow(this.store.getAt(rowIndex).data);
                }.bind(this)
            },
            bbar: this.pagingtoolbar,
            tbar: tbar
        };

        this.grid = Ext.create('Ext.grid.Panel', gridConfig);

        this.store.load();

        this.layout.removeAll();
        this.layout.add(this.grid);
        this.layout.updateLayout();
    },

    clearMonitoringItems: function () {
        var buttons = [
            {
                xtype: 'button',
                scale: "medium",
                //autoWidth : true,
                text: t("plugin_pm_delete_monitoring_item_button"),
                icon: '/bundles/pimcoreadmin/img/flat-color-icons/go.svg',
                hideLabel: true,
                style: {
                    marginLeft: (200) + 'px'
                },
                handler: function () {
                    var values = this.formPanel.getValues();

                    Ext.Ajax.request({
                        url: '/admin/elementsprocessmanager/monitoring-item/delete-batch',
                        params: values,
                        method: 'post',
                        success: function (response) {
                            var data = Ext.decode(response.responseText);
                            if (data.success) {
                                pimcore.helpers.showNotification(t("success"), t("plugin_pm_clear_monitoring_items_success"), "success");
                                this.store.reload();
                                this.window.destroy();
                            } else {
                                pimcore.helpers.showNotification(t("error"), t("plugin_pm_error_process_manager"), "error", t(data.message));
                            }
                        }.bind(this),
                        failure: function () {
                            alert("Could not delete monitoring items");
                        }
                    });

                }.bind(this)
            },
            {
                text: t("cancel"),
                scale: "medium",
                icon: "/bundles/pimcoreadmin/img/icon/cross.png",
                handler: function () {
                    this.window.destroy();
                }.bind(this)
            }
        ];

        var store = Ext.create('Ext.data.ArrayStore', {
            fields: ['id', 'text'],
            data: [
                ['error', 'Error'],
                ['finished', 'Finished'],
                ['unknown', 'Unknown'],
                ['failed', 'Failed'],
                ['initializing', 'Initializing']
            ]
        });


        var logLevels = new Ext.ux.form.MultiSelect({
            name: "logLevels",
            fieldLabel: t('plugin_pm_log_levels'),
            triggerAction: "all",
            editable: false,
            store: store,
            valueField: "id",
            width: '100%',
            height: 180
        });

        this.formPanel = new Ext.FormPanel({
            border: false,
            bodyPadding: 15,
            items: [logLevels]
        });


        this.window = new Ext.Window({
            id: 'clearMonitoringItems',
            height: 300,
            layout: "fit",
            title: t('plugin_pm_clear_monitoring_items'),
            icon: "/bundles/pimcoreadmin/img/icon/cross.png",
            modal: true,
            width: 500,
            close: function () {
                this.window.destroy();
            }.bind(this),
            items: [this.formPanel],
            buttons: buttons
        });

        this.window.show();

    },

    getPagingToolbar: function (store, options) {
        var config = {
            pageSize: pimcore.helpers.grid.getDefaultPageSize(),
            store: store,
            displayInfo: false,
            displayMsg: '{0} - {1} / {2}',
            emptyMsg: t("no_items_found"),
            width: 200
        };
        if (typeof options !== "undefined") {
            config = Ext.applyIf(options, config);
        }
        var pagingtoolbar = Ext.create('Ext.PagingToolbar', config);


        if (!config.hideSelection) {
            // add per-page selection
            pagingtoolbar.add("->");
            pagingtoolbar.add(Ext.create('Ext.Toolbar.TextItem', {
                text: t("items_per_page")
            }));


            pagingtoolbar.add(Ext.create('Ext.form.ComboBox', {
                store: [
                    [25, "25"],
                    [50, "50"],
                    [100, "100"],
                    [200, "200"],
                    [999999, t("all")]
                ],
                mode: "local",
                width: 80,
                value: config.pageSize,
                triggerAction: "all",
                editable: true,
                listeners: {
                    change: function (box, newValue, oldValue) {
                        var store = this.getStore();
                        newValue = intval(newValue);
                        if (!newValue) {
                            newValue = options.pageSize;
                        }
                        store.setPageSize(newValue);
                        this.moveFirst();
                    }.bind(pagingtoolbar)
                }
            }));
        }

        return pagingtoolbar;
    }
});