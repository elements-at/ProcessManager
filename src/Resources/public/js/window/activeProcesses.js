pimcore.registerNS("pimcore.plugin.processmanager.window.activeProcesses");

pimcore.plugin.processmanager.window.activeProcesses = Class.create({
    displayList : [],
    toast : null,
    refreshIntervalSeconds : 3,

    initialize: function () {
        let runner = new Ext.util.TaskRunner(), task;
        this.refreshTask = runner.newTask({
            run: this.requestServerData.bind(this),
            fireOnStart : true,
            interval: this.refreshIntervalSeconds * 1000
        });
    },

    getPanelByItemId : function(id){
        return Ext.getCmp('processmanager_active_process_' + id);
    },

    _buildBar: function(item) {

        if (this.getPanelByItemId(item.id)) {
            let currentItem = this.getPanelByItemId(item.id);
            let progressBar = Ext.getCmp('processmanager_monitoring_item_progress_bar_' + item.id);
            let actionBtnsPanel = Ext.getCmp('processmanager_active_process_action_buttons_' + item.id);

            this._updateProgressBar(progressBar, item);
            this._updatePrimaryBtn(item);
            this._updateActionBtns(actionBtnsPanel, item);

            return;
        }

        let progressBar = Ext.create('Ext.ProgressBar', {
            cls: 'process-manager-notification-progress-bar',
            id: 'processmanager_monitoring_item_progress_bar_' + item.id,
            alignOnScroll: false,
            height: 30,
            flex: 5,
            style: 'margin-top: 0px'
        });

        this._updateProgressBar(progressBar, item);

        let headerText = {
            hideLabel : true,
            xtype: 'displayfield',
            alignOnScroll : false,
            value: item.name + ' (ID: <a href="#" class="process-manager-details-link" id="processMangerOpenLogIdToastPanel'+ item.id+'" title="Show Process log entry" >' + item.id +')</a>',
            labelStyle : 'display:none',
            cls: 'process-manager-active-processes-header-text',
            listeners: {
                afterrender : function (df,x,y){
                    Ext.get('processMangerOpenLogIdToastPanel'+ item.id).on("click",function (){
                        processmanagerPlugin.openLogId(item.id);
                    });
                }
            }
        };

        let primaryBtn = Ext.create('Ext.Button', {
            scale: 'small',
            tooltip: t('plugin_pm_process_list_set_unpublished'),
            id: 'processmanager_monitoring_item_primary_button_' + item.id,
            icon : '/bundles/pimcoreadmin/img/flat-color-icons/approve.svg',
            style: 'margin-left:5px;height:30px'

        });

        let progressPanel = Ext.create('Ext.panel.Panel', {
            //bodyPadding: 5,  // Don't want content to crunch against the borders
            id: 'processmanager_monitoring_item_progress_panel_' + item.id,
            flex: 12,
            layout: {
                type: 'hbox',
            },
            items: [
                progressBar,
                primaryBtn
            ],
        });

        this._updatePrimaryBtn(item);
        let actionBtnsPanel = Ext.create('Ext.panel.Panel', {
            id: 'processmanager_active_process_action_buttons_' + item.id,
            width: '100%',
            style: {
                marginTop: '5px'
            }
        });

        let panel = Ext.create('Ext.panel.Panel', {
            id: 'processmanager_active_process_' + item.id,
            monitoringItemData : item,
            hideLabel: true,
            style: {
                borderBottom: '1px solid #e0e1e2',
                paddingBottom: '5px',
                marginBottom: '12px'
            },
            items: [
                headerText,
                progressPanel,
                actionBtnsPanel
            ],
        });

        this._updateActionBtns(actionBtnsPanel, item);

        //workaround with displayList go the toast displayed properly the fist time (items has to be added before toast is created)
        this.displayList.push(panel);
        if (this.toast != null) {
            this.toast.insert(0,panel);

            this.toast.show();

            //workaround for toast appearing at a random location after adding a process:
            //after showing, set the postion to the bottom right
            let width = window.innerWidth;
            let height = window.innerHeight;
            let toastSize = this.toast.getSize();
            //does only seem to work without animation if animations are disabled for the toast
            this.toast.setPosition(width-toastSize.width,height-toastSize.height,false);
        }

    },

    _updateProgressBar: function(progressBar, item) {

        let message = '';
        if(item.message){
            message = item.message;
        }

        if(item.progressPercentage){
            message += ' (' + Math.round(item.progressPercentage) + '%)';
        }

        if(item.totalSteps > 1){
            message += ' - Step ' + item.currentStep + '/' + item.totalSteps;
        }

        message = message.trim();

        progressBar.updateProgress(item.progressPercentage/100, message, true);
        progressBar.removeCls('unknown');
        progressBar.removeCls('finished');
        progressBar.removeCls('finished_with_errors');
        progressBar.removeCls('failed');
        progressBar.removeCls('running');
        progressBar.removeCls('initializing');

        progressBar.addCls(item.status);
    },

    _updatePrimaryBtn: function(item) {
        let button = Ext.getCmp('processmanager_monitoring_item_primary_button_' + item.id);
        if(item.isAlive){
            button.setIcon('/bundles/pimcoreadmin/img/flat-color-icons/cancel.svg')
            button.setTooltip(t('plugin_pm_stop'));
        }else{
            button.setIcon('/bundles/pimcoreadmin/img/flat-color-icons/approve.svg');
            button.setTooltip(t('plugin_pm_process_list_set_unpublished'));
        }
        //need to set the handler here to get the correct value for the item
        button.handler = function() {
            if(item.isAlive){
                Ext.Ajax.request({
                    url: '/admin/elementsprocessmanager/monitoring-item/cancel',
                    method : 'get',
                    params : {
                        id : item.id
                    },
                    success: function (content) {
                        let result = Ext.decode(content.responseText);
                        if(result.success){
                        }
                    }.bind(this)
                });
            }else{
                Ext.Ajax.request({
                    url: '/admin/elementsprocessmanager/monitoring-item/update',
                    method : 'post',
                    params : {
                        id : item.id,
                        published : false
                    },
                    success: function (content) {
                        let result = Ext.decode(content.responseText);
                        if(result.success){
                            this._removeProgressPanel(result.data.id);
                        }
                    }.bind(this)
                });
            }
        }.bind(this)
        //button.setDisabled(item.isAlive);
    },

    _updateActionBtns: function(actionBtnsPanel, item) {

        actionBtnsPanel.items.removeAll(); //do not remove them as it causes flickering
        actionBtnsPanel.update();

        if(item.actionItems.length){
            for(let i = 0; i < item.actionItems.length; i++){
                let actionData = item.actionItems[i];

                if(actionData.dynamicData && actionData.dynamicData.extJsClass){

                    let obj = eval('new ' + actionData.dynamicData.extJsClass + '()');
                    if(typeof obj.executeActionForActiveProcessList == 'function'){
                        obj.executeActionForActiveProcessList(actionBtnsPanel, actionData,item,this,i);
                    }
                }

            }
        }

        actionBtnsPanel.update();
        actionBtnsPanel.updateLayout();
    },

    _updatePanelTitle : function(data){
        if(this.toast){
            //  let title = t('plugin_pm_process_list_title') + ' <small id="processManagerActiveText">(' + data.active +' from  ' + data.total +' active)</small> ';
            //this.toast.setTitle(title);
            //do not use setTitle as it causes scrolling
            let el = document.getElementById('processManagerActiveText');
            if(el){
                let s = '';
                if(data.active){
                    s = '(' + data.active +' ' + t('plugin_pm_from') +' ' + data.total +' ' + t('plugin_pm_active') + ')';
                }else{
                    s = '(<img src="/bundles/pimcoreadmin/img/flat-color-icons/approve.svg" alt="" class="active_processes_finished_icon">' + t('plugin_pm_all_finished') +')'
                }
                el.innerHTML = s;
            }
        }

    },

    requestServerData: function() {
        //console.log('Query Monitoring items... ');
        Ext.Ajax.request({
            url: '/admin/elementsprocessmanager/monitoring-item/list-processes-for-user',
            success: function (content) {
                let data = Ext.decode(content.responseText);
                let items = data.items;

                let activeItemIds = [];

                if (items.length > 0) {


                    // let activeItemIds = [];
                    for (itemKey in items) {
                        this._buildBar(items[itemKey]);
                        activeItemIds.push(items[itemKey].id);
                    }

                    if(!this.toast){

                        this.toast = Ext.create('Ext.window.Toast', {
                            //region: 'north',
                            icon : '/bundles/pimcoreadmin/img/flat-color-icons/cable_release.svg',
                            align: 'br',
                            id : 'processmanager_active_processes',
                            items: this.displayList,
                            title: t('plugin_pm_process_list_title') + ' <small id="processManagerActiveText"></small>',
                            width: 700,
                            autoScroll : false,
                            animate: false,
                            maxHeight:400,
                            autoShow: true,
                            closeAction : 'hide', //do not destroy as we get errors when panel disappears and a new process is started - seems to be a bug in Ext.Toast
                            autoClose: false,
                            beforeLayout: function() {
                                let me = this,
                                    scroller = me.getScrollable();

                                if (scroller) {
                                    me.savedScrollPos = scroller.getPosition();
                                }
                            },
                            afterLayout: function() {
                                let me = this,
                                    scroller = me.getScrollable();
                                if (scroller && me.savedScrollPos) {
                                    scroller.scrollTo(me.savedScrollPos);
                                }
                            },
                            draggable: true,
                            scrollable : {
                                x : false,
                                y : true
                            },
                            collapseDirection :'bottom',
                            tools:[{
                                type: 'save',
                                tooltip: t('plugin_pm_process_list_set_all_unpublished'),
                                handler: function(event, toolEl, panelHeader) {
                                    Ext.Ajax.request({
                                        url: '/admin/elementsprocessmanager/monitoring-item/update-all-user-monitoring-items',
                                        method : 'post',
                                        params : {
                                            published : false
                                        },
                                        success: function (content) {
                                            let result = Ext.decode(content.responseText);
                                            if(result.success){
                                                this.requestServerData();
                                            }
                                        }.bind(this)
                                    })
                                }.bind(this)
                            }
                            ],
                            constrain : true, //important to get resize window when new items are added
                            collapseToolText : false,
                            expandToolText : false,
                            closable: true,
                            collapsible: true,
                            listeners: {
                                collapse : function (p,direction,animate,e) {
                                    p.alignTo(document, 'br-br',[-25,0]);
                                },
                                beforecollapse : function (p,direction,animate,e) {
                                    p.expandePosition = p.getPosition();
                                },
                                beforeexpand : function (p,direction,animate,e) {
                                    p.setPosition(p.expandePosition[0],p.expandePosition[1]);
                                },
                                beforeclose : function (p,eOpts) {
                                    this.refreshTask.stop();
                                }.bind(this)
                            }
                        });
                    }

                } else{
                    this.refreshTask.stop();
                }

                if(data.active == 0){
                    this.refreshTask.stop();
                }else{
                    if(this.toast.isHidden()){
                        this.toast.show(); //show on restart if hidden
                    }
                }

                //monitoring item might have been delete through an other process or in the grid view
                if(this.toast){
                    for(let i = 0; i < this.toast.items.items.length;i++){
                        let currentPanel = this.toast.items.items[i];
                        if(!activeItemIds.includes(currentPanel.monitoringItemData.id)){
                            this._removeProgressPanel(currentPanel.monitoringItemData.id);
                        }
                    }
                }
                this._updatePanelTitle(data);

            }.bind(this)
        });
    },


    _removeProgressPanel: function(itemId) {
        if (!this.toast) {
            return;
        }

        let panel = this.getPanelByItemId(itemId);
        panel.el.slideOut('t', {
            easing: 'easeOut',
            duration: 500,
            scope: this,
            callback : function () {
                for (let i=0; i < this.toast.items.length; i++) {
                    let p = this.toast.items.get(i);
                    if (p.id == panel.id) {

                        this.toast.remove(i,true);
                        if (this.toast.items.length == 0) {
                            this.toast.removeAll(true,true);
                            this.toast.remove(true);
                            this.toast.close();
                        }
                    }
                }
            }
        });
    }

});
