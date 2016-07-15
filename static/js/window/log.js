pimcore.registerNS("pimcore.plugin.processmanager.window.log");
pimcore.plugin.processmanager.window.log = Class.create({
    refreshInterval : 2,

    initialize : function(id){
        var url = '/plugin/ProcessManager/monitoring-item/log?id=' + id;
        var modal = new Ext.Window({
            id:'dataLoggerWindow',
            title: t('plugin_pm_log_info'),
            modal:true,
            height: 500,
            maximizable: true,
            width: 1000,
            items : [{
                xtype : "box",
                autoEl: {tag: 'iframe', src: url, width : '100%', height : '100%'}
            }]
        });
        modal.show();
    }
});