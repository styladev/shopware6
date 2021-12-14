import template from './styla-cms-integration-settings.html.twig';
import './styla-cms-integration-settings.scss';

const { Component, Defaults } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('styla-cms-integration-settings', {
    template,

    inject: [
        'repositoryFactory',
    ],

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
            defaultAccountNameFilled: false,
            messageAccountBlankErrorState: null,
            config: null,
            salesChannels: [],
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    created() {
        this.createdComponent();
    },

    computed: {
        salesChannelRepository() {
            return this.repositoryFactory.create('sales_channel');
        },

        defaultAccountNameErrorState() {
            if (this.defaultAccountNameFilled) {
                return null;
            }

            return this.messageAccountBlankErrorState;
        },

        hasError() {
            return !this.defaultAccountNameFilled;
        }
    },

    watch: {
        config: {
            handler() {
                const defaultConfig = this.$refs.configComponent.allConfigs.null;
                const salesChannelId = this.$refs.configComponent.selectedSalesChannelId;

                if (salesChannelId === null) {
                    this.defaultAccountNameFilled = !!this.config['StylaCmsIntegration.settings.defaultAccountName'];
                } else {
                    this.defaultAccountNameFilled = !!this.config['StylaCmsIntegration.settings.defaultAccountName']
                        || !!defaultConfig['StylaCmsIntegration.settings.defaultAccountName'];
                }
            },
            deep: true,
        },
    },

    methods: {
        createdComponent() {
            this.isLoading = true;

            const criteria = new Criteria();
            criteria.addFilter(Criteria.equalsAny('typeId', [
                Defaults.storefrontSalesChannelTypeId,
                Defaults.apiSalesChannelTypeId,
            ]));

            this.salesChannelRepository.search(criteria, Shopware.Context.api).then(res => {
                res.add({
                    id: null,
                    translated: {
                        name: this.$tc('sw-sales-channel-switch.labelDefaultOption'),
                    },
                });

                this.salesChannels = res;
            }).finally(() => {
                this.isLoading = false;
            });

            this.messageAccountBlankErrorState = {
                code: 1,
                detail: this.$tc('styla-cms-integration-plugin.configuration.field.accountNames.default.error.empty'),
            };
        },

        onSave() {
            if (this.hasError) {
                return;
            }

            this.save();
        },

        save() {
            this.isLoading = true;

            this.$refs.configComponent.save().then(() => {
                this.isSaveSuccessful = true;
            }).finally(() => {
                this.isLoading = false;
            });
        },
    }
});
