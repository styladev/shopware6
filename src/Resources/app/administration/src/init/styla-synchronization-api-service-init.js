import StylaSynchronizationApiService from '../services/api/styla.synchronization.api.service'

Shopware.Service().register('stylaSynchronizationApiService', (container) => {
    const initContainer = Shopware.Application.getContainer('init');
    return new StylaSynchronizationApiService(initContainer.httpClient, container.loginService);
});
