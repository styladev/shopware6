import template from './listing.html.twig';
import './listing.scss';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;
const { date } = Shopware.Utils.format;

Component.register(
    'styla-cms-integration-page-listing',
    {
        template,

        inject: [
            'repositoryFactory',
            'stylaPageApiService',
            'stylaSynchronizationApiService',
            'systemConfigApiService'
        ],

        mixins: [
            Mixin.getByName('notification')
        ],

        data() {
            return {
                repository: null,
                pages: null,
                lastSuccessSynchronizationDate: null,
                synchronizationSuccess: false,
                synchronizationProcessing: false,
                scheduleSynchronizationSuccess: false,
                scheduleSynchronizationProcessing: false,
                resetSynchronizationSuccess: false,
                resetSynchronizationProcessing: false,
            }
        },

        metaInfo() {
            return {
                title: this.$createTitle()
            }
        },

        computed: {
            columns() {
                return this.getColumns();
            }
        },

        created() {
            this.createdComponent();
        },

        methods: {
            createdComponent() {
                this.repository = this.repositoryFactory.create('styla_cms_page');
                this.domainsRepository = this.repositoryFactory.create('sales_channel_domain');

                let domainSearchCriteria = new Criteria();
                domainSearchCriteria.addSorting(Criteria.sort('createdAt', 'ASC'));
                domainSearchCriteria.addFilter(
                    Criteria.equals('salesChannel.typeId', '8a243080f92e4c719546314b577cf82b')
                );
                let domainsSearchPromise =this.domainsRepository.search(domainSearchCriteria, Shopware.Context.api);
                domainsSearchPromise.then(
                    (result) => {
                        this.domainsList = result;
                    }
                )

                let systemConfigurationFetchPromise = this.systemConfigApiService
                    .getValues('StylaCmsIntegration');
                systemConfigurationFetchPromise.then(
                    (result) => {
                        this.settings = result;
                    }
                )

                let foundPages = null;
                let pagesRequestPromise = this.repository.search(
                    new Criteria(),
                    Shopware.Context.api
                );
                pagesRequestPromise.then(
                    (result) => {
                        foundPages = result;
                    }
                );

                Promise.all([pagesRequestPromise, systemConfigurationFetchPromise, domainsSearchPromise]).then(
                    () => {
                        this.pages = foundPages;
                    }
                );

                this.stylaSynchronizationApiService
                    .getLastSuccessSynchronizationDate()
                    .then(
                        (result) => {
                            if (result?.data?.result) {
                                this.lastSuccessSynchronizationDate = date(
                                    result.data.result,
                                    {
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    }
                                )
                            } else {
                                this.lastSuccessSynchronizationDate = this.$tc(
                                    'styla-cms-integration-plugin.actions.get-last-success-date.not_available'
                                );
                            }
                        }
                    ).catch(
                        (error) => {
                            console.error(
                                'Failed to get last success synchronization date',
                                error
                            );

                            this.createNotificationError({
                                message: this.$tc(
                                    'styla-cms-integration-plugin.actions.get-last-success-date.message.failed'
                                )
                            });
                        }
                    );
            },
            getColumns() {
                return [
                    {
                        property: 'title',
                        label: this.$tc('styla-cms-integration-plugin.page.listing.grid.column.title'),
                        allowResize: true,
                        primary: true
                    },
                    {
                        property: 'path',
                        label: this.$tc('styla-cms-integration-plugin.page.listing.grid.column.path'),
                        allowResize: true
                    },
                    {
                        property: 'accountName',
                        label: this.$tc('styla-cms-integration-plugin.page.listing.grid.column.account-name'),
                        allowResize: true
                    },
                    {
                        property: 'createdAt',
                        label: this.$tc('styla-cms-integration-plugin.page.listing.grid.column.created-at'),
                        allowResize: true
                    },
                ];
            },

            resetSynchronizationStatus() {
                this.resetSynchronizationProcessing = true;
                const promise = this.stylaSynchronizationApiService.resetSynchronizationStatus();

                promise.then(function (response) {
                    this.resetSynchronizationProcessing = false;
                    if (response.data.stuck <= 0) {
                        this.createNotificationSuccess({
                            message: this.$tc(
                                'styla-cms-integration-plugin.actions.reset-synchronization.message.none'
                            )
                        });
                    } else if (response.data.stuck > 0 && response.data.stuck === response.data.cleared) {
                        this.createNotificationSuccess({
                            message: this.$tc(
                                'styla-cms-integration-plugin.actions.reset-synchronization.message.success'
                            )
                        });
                    } else if (response.data.stuck > 0 && response.data.cleared > 0 && response.data.stuck > response.data.cleared) {
                        this.createNotificationWarning({
                            message: this.$tc(
                                'styla-cms-integration-plugin.actions.reset-synchronization.message.partial'
                            )
                        });
                    } else {
                        this.createNotificationWarning({
                            message: this.$tc(
                                'styla-cms-integration-plugin.actions.reset-synchronization.message.failed'
                            )
                        });
                    }
                }.bind(this)).catch(function (error) {
                    this.resetSynchronizationProcessing = false;
                    if (error.response.data.errorCode !== undefined) {
                        console.error(
                            'Failed to reset synchronization status, error code: '
                            + error.response.data.errorCode
                        )
                    }

                    this.createNotificationError({
                        message: this.$tc(
                            'styla-cms-integration-plugin.actions.reset-synchronization.message.failed'
                        )
                    });
                }.bind(this));
            },

            synchronization() {
                this.synchronizationProcessing = true;
                const promise = this.stylaPageApiService.doSynchronization();

                promise.then(function (response) {
                    this.synchronizationProcessing = false;
                    if (response.data.isSynced) {
                        this.createNotificationSuccess({
                            message: this.$tc(
                                'styla-cms-integration-plugin.actions.pages-synchronization.message.success'
                            )
                        });
                    } else if (response.data.errorCode === 'SYNCHRONIZATION_IS_ALREADY_RUNNING') {
                        this.createNotificationWarning({
                            message: this.$tc(
                                'styla-cms-integration-plugin.actions.pages-synchronization.message.is-running'
                            )
                        });
                    } else {
                        this.createNotificationWarning({
                            message: this.$tc(
                                'styla-cms-integration-plugin.actions.pages-synchronization.message.failed'
                            )
                        });
                    }
                }.bind(this)).catch(function (error) {
                    this.synchronizationProcessing = false;
                    if (error.response.data.errorCode !== undefined) {
                        console.error(
                            'Failed to sync styla pages, error code: '
                            + error.response.data.errorCode
                        )
                    }

                    this.createNotificationError({
                        message: this.$tc(
                            'styla-cms-integration-plugin.actions.pages-synchronization.message.failed'
                        )
                    });
                }.bind(this));
            },

            scheduleSynchronization() {
                this.scheduleSynchronizationProcessing = true;
                const promise = this.stylaPageApiService.scheduleSynchronization();

                promise.then(function (response) {
                    this.scheduleSynchronizationProcessing = false;
                    if (response.data.isScheduled) {
                        this.createNotificationSuccess({
                            message: this.$tc(
                                'styla-cms-integration-plugin.actions.schedule-pages-synchronization.message.success'
                            )
                        });
                    } else if (response.data.errorCode === 'SYNCHRONIZATION_IS_ALREADY_RUNNING') {
                        this.createNotificationWarning({
                            message: this.$tc(
                                'styla-cms-integration-plugin.actions.schedule-pages-synchronization.message.is-running'
                            )
                        });
                    } else {
                        this.createNotificationWarning({
                            message: this.$tc(
                                'styla-cms-integration-plugin.actions.schedule-pages-synchronization.message.was-not-scheduled'
                            )
                        });
                    }
                }.bind(this)).catch(function (error) {
                    this.scheduleSynchronizationProcessing = false;
                    if (error.response.data.errorCode !== undefined) {
                        console.error(
                            'Failed to schedule styla pages synchronization, error code: '
                            + error.response.data.errorCode
                        )
                    }

                    this.createNotificationError({
                        message: this.$tc(
                            'styla-cms-integration-plugin.actions.schedule-pages-synchronization.message.failed'
                        )
                    });
                }.bind(this));
            },

            refreshPageDetails(item) {
                const promise = this.stylaPageApiService.refreshPageDetails(item.id);
                this.createNotificationInfo({
                    message: this.$tc(
                        'styla-cms-integration-plugin.actions.refresh-page-details.message.scheduled'
                    )
                });

                promise.then(function (response) {
                    if (response.data.isSuccess) {
                        this.createNotificationSuccess({
                            message: this.$tc(
                                'styla-cms-integration-plugin.actions.refresh-page-details.message.success'
                            )
                        });
                    } else if (response.data.errorCode === 'PAGE_NOT_FOUND') {
                        this.createNotificationWarning({
                            message: this.$tc(
                                'styla-cms-integration-plugin.actions.refresh-page-details.message.page-not-found'
                            )
                        });
                    } else {
                        this.createNotificationError({
                            message: this.$tc(
                                'styla-cms-integration-plugin.actions.refresh-page-details.failed'
                            )
                        });
                    }
                }.bind(this)).catch(function (error) {
                    if (error.response.data.errorCode !== undefined) {
                        console.error(
                            'Failed to refresh styla page details, error code: '
                            + error.response.data.errorCode
                        )
                    }

                    this.createNotificationError({
                        message: this.$tc(
                            'styla-cms-integration-plugin.actions.refresh-page-details.message.failed'
                        )
                    });
                }.bind(this));
            },
            computePathLink(value){
                if (this.domainsList.length === 0) {
                    return null;
                }

                if (this.pagesPathHashMap && this.pagesPathHashMap[value.id]) {
                    return this.pagesPathHashMap[value.id];
                }

                let matchedLanguageId = null;
                const accountNames = this.settings['StylaCmsIntegration.settings.accountNames'];
                for (let languageId in accountNames) {
                    if (value.accountName === accountNames[languageId] && accountNames[languageId]) {
                        matchedLanguageId = languageId;
                        break;
                    }
                }

                let domainEntity = null;
                if (matchedLanguageId && matchedLanguageId !== 'default') {
                    domainEntity = this.domainsList.find(
                        element => {
                            return element.languageId === matchedLanguageId;
                        }
                    );
                }

                if (!domainEntity) {
                    domainEntity = this.domainsList[0];
                }

                let domainUrl = domainEntity.url;
                const defaultDomainUrl = this.settings['StylaCmsIntegration.settings.defaultDomainUrl'];
                if (defaultDomainUrl) {
                    domainUrl = defaultDomainUrl;
                }

                const domainUrls = this.settings['StylaCmsIntegration.settings.domainUrls'];
                if (domainUrls && matchedLanguageId) {
                    for (let languageId in domainUrls) {
                        if (matchedLanguageId === matchedLanguageId && domainUrls[languageId]) {
                            domainUrl = domainUrls[languageId];
                            break;
                        }
                    }
                }

                let pathString = `${value.path}`;

                const url = domainUrl.replace(/\/$/, '') //Trim "/" at the end of the url
                    + '/'
                    + pathString.replace(/^\//, '') // Trim "/" at the beginning of the path

                if (!this.pagesPathHashMap) {
                    this.pagesPathHashMap = {};
                }
                this.pagesPathHashMap[value.id] = url;

                return url;
            },
            resetSynchronizationState() {
                this.synchronizationSuccess = false;
                this.synchronizationProcessing = false;
            },
            resetScheduleSynchronizationState() {
                this.scheduleSynchronizationSuccess = false;
                this.scheduleSynchronizationProcessing = false;
            },
            resetTheResetSynchronizationState() {
                this.resetSynchronizationSuccess = false;
                this.resetSynchronizationProcessing = false;
            }
        }
    }
);
