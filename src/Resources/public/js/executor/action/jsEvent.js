pimcore.registerNS("pimcore.plugin.processmanager.executor.action.jsEvent");
pimcore.plugin.processmanager.executor.action.jsEvent = new Class.create(pimcore.plugin.processmanager.executor.action.abstractAction,{

    getButton : function(){
        this.button = {
            icon: '/bundles/pimcoreadmin/img/flat-color-icons/biohazard.svg',
            text: t("plugin_pm_jsEvent"),
            "handler" : this.addForm.bind(this)
        }
        return this.button;
    },

    getForm : function(){
        if(!this.button){
            this.getButton();
        }
        let myId = Ext.id();
        let items = [];
        items.push(this.getTextField('label'));
        items.push(this.getTextField('icon'));
        items.push(this.getTextField('eventName',{mandatory : true}));
        items.push(this.getTextArea('eventData',{tooltip : t('plugin_pm_eventData_tooltip')}));
        items.push(this.getTextField('class',{hidden: true,value : '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Action\\JsEvent'}));
        this.form =  new Ext.form.FormPanel({
            forceLayout: true,
            id: myId,
            type : 'formPanel',
            style: "margin: 10px",
            bodyStyle: "padding: 10px 30px 10px 30px; min-height:40px;",
            tbar: this.getTopBar(this.button.text,myId),
            items: items
        });

        return this.form;
    },

    executeActionForActiveProcessList : function(actionButtonPanel,actionData,monitoringItem,obj,index){
        let detail = {
            source : 'activeProcessList',
            monitoringItem : monitoringItem,
            actionData : actionData,
            actionButtonPanel : actionButtonPanel,
            obj : obj,
            index : index
        }
        const event = new CustomEvent(actionData.eventName, {detail: detail});
        document.dispatchEvent(event);
    },

    executeActionForGridList : function (data) {
        let detail = {
            source : 'gridList',
            monitoringItem : data.monitoringItem,
            actionData : data.actionData
        };

        const event = new CustomEvent(data.actionData.eventName, {detail: detail});
        document.dispatchEvent(event);
    }
});

var processmanagerPluginJsEvent = new pimcore.plugin.processmanager.executor.action.jsEvent();