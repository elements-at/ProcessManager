pimcore.registerNS("pimcore.plugin.processmanager.window.detailwindow");
pimcore.plugin.processmanager.window.detailwindow = Class.create({
    getClassName: function (){
        return "pimcore.plugin.processmanager.window.detailwindow";
    },
    
	initialize: function (data) {
		this.data = data;
		this.getInputWindow();
        this.detailWindow.show();
	},    


    getInputWindow: function () {
        
        if(!this.detailWindow) {
            this.detailWindow = new Ext.Window({
				width: '80%',
				height: '80%',
                title: t('plugin_pm_monitoring_item_detail_window'),
				closeAction:'close',
				plain: true,
				maximized: false,
				autoScroll: true,
				modal: true,
				buttons: [
                    {
                        text: t('close'),
                        iconCls: "pimcore_icon_cancel",
                        scale: "medium",
                        handler: function(){
                            this.detailWindow.hide();
                            this.detailWindow.destroy();
                        }.bind(this)
                    }					
                ]
			});
			
			this.createPanel();
        }
        return this.detailWindow;
    },
	

	createPanel: function() {
		var items = [];
        items.push({
            xtype: "textfield",
            fieldLabel: t("name"),
            name: "name",
            readOnly: true,
            value: this.data.name,
            width : "100%"
        });

        items.push({
            xtype: "textfield",
            fieldLabel: t("plugin_pm_command"),
            name: "command",
            readOnly: true,
            value: this.data.command,
            width : "100%"
        });

        items.push({
            xtype: "textarea",
            fieldLabel: t('plugin_pm_message'),
            name: "message",
            readOnly: true,
            value: this.data.message,
            width : "100%",
            height: 100
        });

        items.push({
            xtype: "textarea",
            fieldLabel: t('plugin_pm_callback_settings'),
            name: "callbackSettings",
            readOnly: true,
            value: JSON.stringify(Ext.decode(this.data.callbackSettingsString), null, '\t'),
            width : "100%",
            height: 250
        });

        items.push({
            xtype: "textarea",
            fieldLabel: t('plugin_pm_metaData'),
            name: "metaData",
            readOnly: true,
            value: this.data.metaData ? JSON.stringify(Ext.decode(this.data.metaData), null, '\t') : '',
            width : "100%",
            height: 250
        });

        items.push({
            xtype: "textarea",
            fieldLabel: t('plugin_pm_logger_data'),
            name: "loggers",
            readOnly: true,
            value: JSON.stringify(Ext.decode(this.data.loggers), null, '\t'),
            width : "100%",
            height: 250
        });

        items.push({
            xtype: "textarea",
            fieldLabel: t('plugin_pm_logger_actions'),
            name: "actions",
            readOnly: true,
            value: JSON.stringify(Ext.decode(this.data.actions), null, '\t'),
            width : "100%",
            height: 250
        });

        items.push({
            xtype: "textfield",
            fieldLabel: t("plugin_pm_executedByUser"),
            name: "executedByUser",
            readOnly: true,
            value: this.data.executedByUser,
            width : "100%"
        });

        items.push({
            xtype: "textfield",
            fieldLabel: t("plugin_pm_group"),
            name: "group",
            readOnly: true,
            value: this.data.group,
            width : "100%"
        });

		items.push({
			xtype: "numberfield",
			fieldLabel: "ID",
			name: "id",
            readOnly: true,
			value: this.data.id
		});

        items.push({
            xtype: "numberfield",
            fieldLabel: "Parent ID",
            name: "parentId",
            readOnly: true,
            value: this.data.parentId
        });

        items.push({
            xtype: "numberfield",
            fieldLabel: "CID",
            name: "cid",
            readOnly: true,
            value: this.data.configurationId
        });
        items.push({
            xtype: "numberfield",
            fieldLabel: "PID",
            name: "pid",
            readOnly: true,
            value: this.data.pid
        });

        var dateRenderer = function(d) {
            if (d !== undefined && d) {
                var date = new Date(d * 1000);
                return Ext.Date.format(date, "Y-m-d H:i:s");
            } else {
                return "";
            }
        };

        items.push({
            xtype: "textfield",
            fieldLabel: t("plugin_pm_monitor_creationDate"),
            name: "creationDate",
            readOnly: true,
            value: dateRenderer(this.data.creationDate)
        });

        items.push({
            xtype: "textfield",
            fieldLabel: t("plugin_pm_monitor_modificationDate"),
            name: "modificationDate",
            readOnly: true,
            value: dateRenderer(this.data.modificationDate)
        });
        items.push({
            xtype: "textfield",
            fieldLabel: t("plugin_pm_monitor_reportedDate"),
            name: "reportedDate",
            readOnly: true,
            value: dateRenderer(this.data.reportedDate)
        });

        items.push({
            xtype: "textfield",
            fieldLabel: t("status"),
            name: "status",
            readOnly: true,
            value: this.data.status
        });

        items.push({
            xtype: "textfield",
            fieldLabel: t("steps"),
            name: "steps",
            readOnly: true,
            value: this.data.steps
        });

        items.push({
            xtype: "textfield",
            fieldLabel: t("plugin_pm_monitor_duration"),
            name: "steps",
            readOnly: true,
            value: this.data.duration
        });

        var panel = new Ext.form.FormPanel({
            border: false,
			frame:false,
		    bodyStyle: 'padding:10px',
            items: items,
			labelWidth: 130,
			collapsible: false,
            autoScroll: true
        });
		
		this.detailWindow.add(panel);
	}

});