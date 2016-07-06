/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

pimcore.registerNS("pimcore.plugin.processmanager.panel.callbackSetting");
pimcore.plugin.processmanager.panel.callbackSetting = Class.create({

    getPanel: function () {
        if (this.layout == null) {
            this.layout = new Ext.Panel({
                title: t("plugin_pm_callback_settings"),
                border: false,
                icon : '/pimcore/static6/img/flat-color-icons/settings.svg',
                layout: "fit",
                region: "center"
            });

            this.createGrid();
        }

        return this.layout;
    },

    createGrid: function(response) {
        this.fields = ['id', 'name','type', 'description','creationDate', 'modificationDate'];

        var readerFields = [];
        for (var i = 0; i < this.fields.length; i++) {
            readerFields.push({name: this.fields[i], allowBlank: true});
        }

        var itemsPerPage = pimcore.helpers.grid.getDefaultPageSize(-1);
        var url =  "/plugin/ProcessManager/callback-settings/list?";

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
            if(action == "update") {
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
                dataIndex: 'type'
            }
        );
        gridColumns.push({header: t("name"), width: 200, sortable: true, dataIndex: 'name', filter: 'string'});
        gridColumns.push({header: t("description"), width: 300, sortable: true, dataIndex: 'description', filter: 'string'});

        var dateRenderer =  function(d) {
            if (d !== undefined) {
                var date = new Date(d * 1000);
                return Ext.Date.format(date, "Y-m-d H:i:s");
            } else {
                return "";
            }
        };

        gridColumns.push(
            {header: t("creationDate"), sortable: true, dataIndex: 'creationDate', editable: false, width: 150,
                hidden: true,
                renderer: dateRenderer
            }
        );

        gridColumns.push(
            {header: t("modificationDate"), sortable: true, dataIndex: 'modificationDate', editable: false, width: 150,
                hidden: true,
                renderer: dateRenderer            }
        );
        if(pimcore.globalmanager.get("user").isAllowed("plugin_pm_permission_configure")) {
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

                            var conf = processmanagerPlugin.config.executorCallbackClasses[rec.get('type')];
                            var obj =  eval('new ' + conf.extJsClass);
                            obj.setRecord(rec);
                            obj.openSaveSettingsWindow();
                        }.bind(this)
                    }
                ]
            });
            var copy = {
                header: t('plugin_pm_copy'),
                xtype: 'actioncolumn',
                width: 70,
                items: [{
                    tooltip: t('plugin_pm_copy'),
                    icon: "/pimcore/static/img/icon/page_white_copy.png",
                    handler: function (grid, rowIndex) {

                        var rec = grid.getStore().getAt(rowIndex);
                        Ext.Ajax.request({
                            url: "/plugin/ProcessManager/callback-settings/copy",
                            params: {
                                id: rec.get("id")
                            },
                            success: function (transport) {
                                var res = Ext.decode(transport.responseText);
                                if(res.success){
                                    grid.getStore().reload();
                                }else{
                                    Ext.Msg.alert(t('error'), res.error);
                                }
                            }.bind(this)
                        });
                    }.bind(this)
                }]
            };
            gridColumns.push(copy);

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
                                        text: t("plugin_pm_delete_confirm_callback_setting"),
                                        iconCls: "pimcore_icon_apply",
                                        handler: function () {

                                            var rec = grid.getStore().getAt(rowIndex);

                                            Ext.Ajax.request({
                                                url: "/plugin/ProcessManager/callback-settings/delete",
                                                params: {
                                                    id: rec.get("id")
                                                },
                                                success: function (response) {
                                                    Ext.getCmp('plugin_pm_callback_settings_panel').store.reload();
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
        }

        if(pimcore.globalmanager.get("user").isAllowed("plugin_pm_permission_configure")) {

            this.toolbarButtons = {};

            var i = 0;
            for (var key in processmanagerPlugin.config.executorCallbackClasses) {
                if (processmanagerPlugin.config.executorCallbackClasses.hasOwnProperty(key)) {
                    var h = function(button){
                        var obj =  eval('new ' + processmanagerPlugin.config.executorCallbackClasses[this.callbackType].extJsClass);
                        obj.openSaveSettingsWindow();
                    };

                    if(i == 0){
                        this.toolbarButtons = new Ext.SplitButton({
                            text: t('plugin_pm_button_add_' + key),
                            iconCls: "pimcore_icon_add",
                            scale: "medium",
                            callbackType : key,
                            handler : h,
                            menu : []
                        });
                    } else {
                        var item = {
                            text: t('plugin_pm_button_add_' + key),
                            iconCls: "pimcore_icon_add",
                            callbackType : key,
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
            disableSelection : true,
            trackMouseOver: false,
            store: this.store,
            id : 'plugin_pm_callback_settings_panel',
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
        } ;

        this.grid = Ext.create('Ext.grid.Panel', gridConfig);

        this.store.load();

        this.layout.removeAll();
        this.layout.add(this.grid);
        this.layout.updateLayout();
    },

    executeJob : function (grid, rowIndex) {
        var record = grid.getStore().getAt(rowIndex);
        var callbackClass = record.get('settings').values.callback;
        if(callbackClass){
            var callback = eval('new ' + callbackClass + '(grid, rowIndex)');
        }else{
            var callback = new pimcore.plugin.processmanager.executor.callback.default(grid, rowIndex);
        }
        callback.execute();
    }

});

