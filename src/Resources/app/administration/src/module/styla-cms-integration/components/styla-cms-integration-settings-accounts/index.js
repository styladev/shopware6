import template from './styla-cms-integration-settings-accounts.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('styla-cms-integration-settings-accounts', {
    template,

    inject: [
        'repositoryFactory',
    ],

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
        defaultAccountNameErrorState: {
            type: Object,
            required: false,
            default: null,
        },
        defaultAccountNameFilled: {
            type: Boolean,
            required: true,
        }
    },

    data() {
        return {
            configPath: 'StylaCmsIntegration.settings.accountNames',
            isLoading: false,
            systemLanguages: [],
        };
    },

    created() {
        this.createdComponent();
    },

    computed: {
        languageRepository() {
            return this.repositoryFactory.create('language');
        },

        accountNames: {
            get: function () {
                return this.allConfigs[this.selectedSalesChannelId]['StylaCmsIntegration.settings.accountNames'];
            }
        }
    },

    methods: {
        createdComponent() {
            this.isLoading = true;

            const criteria = new Criteria();
            criteria.addSorting(Criteria.sort('createdAt', 'ASC'));

            this.languageRepository.search(criteria, Shopware.Context.api).then(result => {
                this.systemLanguages = result;
                this.initLanguageConfig();
            }).finally(() => {
                this.isLoading = false;
            });
        },

        initLanguageConfig() {
            if (this.allConfigs[this.selectedSalesChannelId][this.configPath] === undefined) {
                /**
                 * Here is a trick: we are using "accountNames" computed prop only for reading data in template
                 * and creating config entry here to make it reactive, cuz our account config is an object.
                 */
                this.$set(this.allConfigs[this.selectedSalesChannelId], 'StylaCmsIntegration.settings.accountNames', {});
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
