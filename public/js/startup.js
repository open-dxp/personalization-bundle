opendxp.registerNS("opendxp.bundle.personalization.startup");

opendxp.bundle.personalization.startup = Class.create({
    initialize: function () {

        // target groups
        Ext.define('opendxp.model.target_groups', {
            extend: 'Ext.data.Model',
            fields: ["id", "text"]
        });

        var targetGroupStore = Ext.create('Ext.data.JsonStore', {
            model: "opendxp.model.target_groups",
            proxy: {
                type: 'ajax',
                url: Routing.generate('opendxp_bundle_personalization_targeting_targetgrouplist'),
                reader: {
                    type: 'json'
                }
            }
        });

        targetGroupStore.load();
        opendxp.globalmanager.add("target_group_store", targetGroupStore);

        document.addEventListener(opendxp.events.preMenuBuild, this.preMenuBuild.bind(this));

        document.addEventListener(opendxp.events.prepareDocumentPageSettingsLayout, this.assignTargetGroupToPage.bind(this));
    },


    preMenuBuild: function (e) {
        let menu = e.detail.menu;
        const user = opendxp.globalmanager.get('user');
        const perspectiveCfg = opendxp.globalmanager.get("perspective");
        if(menu.marketing) {
            if (user.isAllowed("targeting") && perspectiveCfg.inToolbar("marketing.targeting")) {
                menu.marketing.items.push({
                    text: t("personalization") + " / " + t("targeting"),
                    iconCls: "opendxp_nav_icon_usergroup",
                    itemId: 'opendxp_menu_marketing_personalization',
                    hideOnClick: false,
                    menu: {
                        cls: "opendxp_navigation_flyout",
                        shadow: false,
                        items: [
                            {
                                text: t("global_targeting_rules"),
                                iconCls: "opendxp_nav_icon_targeting",
                                itemId: 'opendxp_menu_marketing_personalization_global_targeting_rules',
                                handler: this.showTargetingRules
                            }, {
                                text: t('target_groups'),
                                iconCls: "opendxp_nav_icon_target_groups",
                                itemId: 'opendxp_menu_marketing_personalization_target_groups',
                                handler: this.showTargetGroups
                            }, {
                                text: t("targeting_toolbar"),
                                iconCls: "opendxp_nav_icon_targeting_toolbar",
                                itemId: 'opendxp_menu_marketing_personalization_targeting_toolbar',
                                handler: this.showTargetingToolbarSettings
                            }
                        ]
                    }
                });
            }
        }
    },

    showTargetingRules: function () {
        var tabPanel = Ext.getCmp("opendxp_panel_tabs");
        try {
            tabPanel.setActiveTab(opendxp.globalmanager.get("targeting").getLayout());
        } catch (e) {
            var targeting = new opendxp.bundle.personalization.settings.rules.panel();
            opendxp.globalmanager.add("targeting", targeting);

            tabPanel.add(targeting.getLayout());
            tabPanel.setActiveTab(targeting.getLayout());

            targeting.getLayout().on("destroy", function () {
                opendxp.globalmanager.remove("targeting");
            }.bind(this));

            opendxp.layout.refresh();
        }
    },

    showTargetGroups: function () {
        var tabPanel = Ext.getCmp("opendxp_panel_tabs");
        try {
            tabPanel.setActiveTab(opendxp.globalmanager.get("targetGroupsPanel").getLayout());
        } catch (e) {
            var targetGroups = new opendxp.bundle.personalization.settings.targetGroups.panel();
            opendxp.globalmanager.add("targetGroupsPanel", targetGroups);

            tabPanel.add(targetGroups.getLayout());
            tabPanel.setActiveTab(targetGroups.getLayout());

            targetGroups.getLayout().on("destroy", function () {
                opendxp.globalmanager.remove("targetGroupsPanel");
            }.bind(this));

            opendxp.layout.refresh();
        }
    },

    showTargetingToolbarSettings: function () {
        new opendxp.bundle.personalization.settings.targetingtoolbar();
    },

    assignTargetGroupToPage: function (e) {
        const document = e.detail.document;
        let documentTargetIds = (document.data["targetGroupIds"]) ??  '';

        const assignTargetGroupBlock = {
            xtype: 'fieldset',
            title: t('assign_target_groups'),
            collapsible: true,
            autoHeight: true,
            defaults: {
                labelWidth: 300
            },
            defaultType: 'textfield',
            items: [
                Ext.create('Ext.ux.form.MultiSelect', {
                    fieldLabel: t('visitors_of_this_page_will_be_automatically_associated_with_the_selected_target_groups'),
                    store: opendxp.globalmanager.get("target_group_store"),
                    displayField: "text",
                    valueField: "id",
                    name: 'targetGroupIds',
                    width: 700,
                    //listWidth: 200,
                    value: documentTargetIds.split(',').map(Number).filter(item => item),
                    minHeight: 100
                })
            ]
        };

        e.detail.layout.add(assignTargetGroupBlock);
    }
})

const personalization = new opendxp.bundle.personalization.startup();
