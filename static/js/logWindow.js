pimcore.registerNS("pimcore.plugin.processmanager.logWindow");
pimcore.plugin.processmanager.logWindow = Class.create({
    refreshInterval : 2,

    initialize : function(id){
        var url = '/plugin/ProcessManager/index/monitoring-item-log?id=' + id;
        var modal = new Ext.Window({
            id:'dataLoggerWindow',
            title: t('plugin_process_manager_log_info'),
            modal:true,
            height: 500,
            width: 800,
            items : [{
                xtype : "box",
                autoEl: {tag: 'iframe', src: url, width : '100%', height : '100%'}
            }]
        });
        modal.show();
    }
});