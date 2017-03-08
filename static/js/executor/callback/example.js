pimcore.registerNS("pimcore.plugin.processmanager.executor.callback.example");
pimcore.plugin.processmanager.executor.callback.example = Class.create(pimcore.plugin.processmanager.executor.callback.abstractCallback,{
    //a custom name
    name : "example",

    // remove this param or set it to "window" if you want that the callback is opened in a modal window instead of a tab
    callbackWindowType : 'tab',

    //change the label width
    //labelWidth : 300,

    getFormItems : function () {
        var items = [];

        //general properties - supported by all field types
        var config = {
            mandatory : false, //bool - defines if the field is required
            tooltip : t("plugin_pm_myTooltip") //add a tooltip icon for explanations
        }

        //Input field
        items.push(this.getTextField('myTextField',config));

        //Textarea field
        items.push(this.getTextArea('myTextArea',config));

        //Number field
        items.push(this.getNumberField('myNumber',config));

        //Date field
        items.push(this.getDateField('myDate',config));

        //Select a pimcore role
        items.push(this.getRoleSelection('myRole',config));

        //Select a pimcore locale
        items.push(this.getLocaleSelection('myLocale',config));

        //Checkbox
        items.push(this.getCheckbox('myCheckbox',config));

        //Select field
        var selectConfig = config;
        selectConfig.store = [
            ['key1',t('plugin_pm_example_select_1')],
            ['key2',t('plugin_pm_example_select_2')]
        ];
        items.push(this.getSelectField('mySelectField',selectConfig));

        //Href field (a itemSelectorConfig can also be passed to limit the selection -> @see items.push(this.getItemSelector('myItems',itemSelectorConfig)); below
        items.push(this.getHref('myHref',config));

        //item Selector

        var itemSelectorConfig = config;
        /*
        //Add a custom button to the item selector and restrict the selections to objects of the class "Product"
        var materialAddButton = {
            xtype: "button",
            iconCls: "pimcore_icon_add",
            handler: function () {
                alert('Open Custom window...');
            }.bind(this)
        };

        //values for type: "object","document","asset"
        var itemSelectorConfig = {
            itemSelectorConfig: {type: ["object"], specific: {classes: ['Product']}},
            buttons : [materialAddButton],
            mandatory : true
        };
         */

        items.push(this.getItemSelector('myItems',itemSelectorConfig));

        var propertySelectorConfig = config;

        var propertySelectorConfig = {
            storeUrl : '/plugin/ProcessManager/index/property-list',
            mandatory : false
            /*,
            //column config - optional - default to "name" column for display
            columns : [
                {
                    text: 'Id',
                    sortable: true,
                    dataIndex: "id"
                },
                {
                    text: t("plugin_pm_property_selector_propertyname"),
                    sortable: true,
                    dataIndex: "name",
                    flex: 1
                }
            ]*/
        };

        /*
         * The /plugin/ProcessManager/index/property-list call returns a array like this
        {
            "success": true,
            "data": [
            {
                "id": 1,
                "name": "Display text - myProperties - 1"
            },
            {
                "id": 2,
                "name": "Display text - myProperties - 2"
            }
        }*/

        items.push(this.getPropertySelector('myProperties',propertySelectorConfig));




        return items;
    }
});