pimcore.registerNS("pimcore.plugin.processmanager.executor.callback.phing");
pimcore.plugin.processmanager.executor.callback.phing = Class.create(pimcore.plugin.processmanager.executor.callback.abstractCallback, {

    name: "phing",
    initialize: function () {
        this.settings.windowHeight = 600;
    },
    getFormItems: function () {
        var items = [];
        var fieldSetLocal = new Ext.form.FieldSet({
            title: t("plugin_pm_settings_phing_local"),
            collapsible: true,
            combineErrors: false,
            items: [this.getSelectField('localReleaseType', [
                ['bugfix', t('plugin_pm_phing_release_type_bugfix')],
                ['minor', t('plugin_pm_phing_release_type_minor')],
                ['major', t('plugin_pm_phing_release_type_major')]
            ]),
                this.getTextArea('localBuildMessage'),
                this.getTextField('localAttaskLink'),
                this.getCheckbox('localDoRemoteInstallation')
            ]
        });

        var fieldSetRemote = new Ext.form.FieldSet({
            title: t("plugin_pm_settings_phing_remote"),
            collapsible: true,
            combineErrors: false,
            items: [
                this.getCheckbox('remoteSkipDbBackup')
            ]
        });

        items.push(fieldSetLocal);
        items.push(fieldSetRemote);
        return items;
    },

    execute: function () {
        this.openConfigWindow();
    }
});