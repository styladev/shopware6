import template from './styla-cms-integration-settings-general.html.twig';

const { Component } = Shopware;

Component.register('styla-cms-integration-settings-general', {
    template,

    props: {
        actualConfigData: {
            type: Object,
            required: true,
        },
        allConfigs: {
            type: Object,
            required: true,
        },
        selectedSalesChannelId: {
            type: String,
            required: false,
            default: null,
        },
    },

    created() {
        this.createdComponent();
    },

    computed: {
        useFullPathOptions: {
            get: function () {
                const tc = 'styla-cms-integration-plugin.configuration.field.useFullPath.';
                return [
                    { label: this.$tc(tc+'optionNo'), value: false },
                    { label: this.$tc(tc+'optionYes'), value: true },
                ];
            }
        },
    },

    methods: {
        createdComponent() {
            const configPrefix = 'StylaCmsIntegration.settings.',
                defaultConfigs = {
                    pagesListSynchronizationInterval: 10,
                    pageCacheDuration: 3600,
                    useFullPath: false,
                };

            /**
             * Initialize config data with default values.
             */
            for (const [key, defaultValue] of Object.entries(defaultConfigs)) {
                if (this.allConfigs['null'][configPrefix + key] === undefined) {
                    this.$set(this.allConfigs['null'], configPrefix + key, defaultValue);
                }
            }
        },

        checkTextFieldInheritance(value) {
            if (typeof value !== 'string') {
                return true;
            }

            return value.length <= 0;
        },
    },
});
