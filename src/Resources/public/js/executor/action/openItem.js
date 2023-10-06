pimcore.registerNS("pimcore.plugin.processmanager.executor.action.openItem");
pimcore.plugin.processmanager.executor.action.openItem = new Class.create(pimcore.plugin.processmanager.executor.action.abstractAction,{

    getButton : function(){
        this.button = {
            icon: '/bundles/pimcoreadmin/img/flat-color-icons/open_window.svg',
            text: t("plugin_pm_open_item"),
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
        items.push(this.getSelectField('type', {
            store : [
                ['object', t('plugin_pm_type_object')],
                ['document', t('plugin_pm_type_document')],
                ['asset', t('plugin_pm_type_asset')]
            ]
        }));
        items.push(this.getNumberField('itemId'));
        items.push(this.getTextField('class',{hidden: true,value : '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Action\\OpenItem'}));

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
        if(actionData.executeAtStates.includes(monitoringItem.status)){
            let icons = {
                document : "/bundles/pimcoreadmin/img/flat-white-icons/page.svg",
                object : "/bundles/pimcoreadmin/img/flat-white-icons/object.svg",
                asset : "/bundles/pimcoreadmin/img/flat-white-icons/camera.svg"
            };
            let text = actionData.label ? actionData.label : t('open');

            let button = Ext.create('Ext.Button', {
                text: text,
                icon : icons[actionData.type],
                disabled : !actionData.dynamicData.item_exists,
                iconCls : ' pimcore_icon_overlay_go ',
                style: (index > 0 ? 'margin-left:5px;' : ''),
                scale: 'small',
                handler: function() {
                    let type = actionData.type;
                    if(type === 'asset'){
                        pimcore.helpers.openAsset(actionData.itemId,actionData.dynamicData.item_type);
                    }
                    if(type === 'object'){
                        pimcore.helpers.openObject(actionData.itemId,actionData.dynamicData.item_type);
                    }
                    if(type === 'document'){
                        pimcore.helpers.openDocument(actionData.itemId,actionData.dynamicData.item_type);
                    }
                }
            });
            actionButtonPanel.items.add(button);
        }
    }

});

document.addEventListener('processManager.monitoringItemGrid', (e) => {
    e.preventDefault();
    let currentTarget = e.detail.sourceEvent.currentTarget;
    if(e.detail.trigger === 'openItem'){
        let itemId = currentTarget.getAttribute('data-process-manager-item-id');
        let itemType = currentTarget.getAttribute('data-process-manager-item-type');
        let actionType = currentTarget.getAttribute('data-process-manager-item-action-type');
        pimcore.helpers["open" + actionType](itemId,itemType);
    }
});