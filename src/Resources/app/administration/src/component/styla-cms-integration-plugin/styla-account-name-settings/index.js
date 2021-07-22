import template from './styla-account-name-settings.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('styla-account-name-settings', {
    template,
    model: {
        prop: 'value',
        event: 'change'
    },

    inject: [
        'repositoryFactory'
    ],

    props: {
        value: {
            required: true
        }
    },
    created() {
        this.fetchAvailableLocales();
    },
    data() {
        return {
            accountNames: {},
            languages: {},
            defaultAccountNameError: null
        }
    },
    methods: {
        fetchAvailableLocales() {
            this.repository = this.repositoryFactory.create('language');

            const criteria = new Criteria();
            criteria.addSorting(Criteria.sort('createdAt', 'ASC'));

            this.repository.search(
                criteria,
                Shopware.Context.api
            ).then(
                (result) => {
                    if (!Array.isArray(result)) {
                        this.updateLanguages([]);
                    } else {
                        this.updateLanguages(result);
                    }
                    this.checkForm();
                }
            );
        },
        updateLanguages: function(languagesArray) {
            const languages = {};
            languagesArray.forEach(function(language){
                languages[language.id] = language;
            });
            this.languages = languages;
            this.updateAccountNamesList();
        },

        updateAccountNamesList: function() {
            let storedAccountNames = typeof this.value === 'object' ? this.value : {};

            let accountNames = {
                'default': storedAccountNames.hasOwnProperty('default') ? storedAccountNames.default : ''
            };
            for (const languageId in this.languages) {
                accountNames[languageId] = storedAccountNames.hasOwnProperty(languageId)
                    ? storedAccountNames[languageId]
                    : '';
            }
            this.accountNames = accountNames;
        },
        onChange() {
            this.$emit('change', this.accountNames);
        },
        checkForm(){
            let isFormValid = true;
            if (!this.accountNames.hasOwnProperty('default')) {
                this.addDefaultAccountNameError();
                isFormValid = false;
            } else if (this.accountNames.default === '') {
                this.addDefaultAccountNameError();
                isFormValid = false;
            }

            if (isFormValid) {
                this.defaultAccountNameError = null;
            }

            this.$emit('preventSave', isFormValid);
        },

        addDefaultAccountNameError(){
            this.defaultAccountNameError = {
                code: 1,
                detail: this.$tc('styla-cms-integration-plugin.configuration.field.account_names.default.error.empty')
            };
        },
    }
});
