import StylaPageApiService from '../services/api/styla.page.api.service'

Shopware.Service().register('stylaPageApiService', (container) => {
    const initContainer = Shopware.Application.getContainer('init');
    return new StylaPageApiService(initContainer.httpClient, container.loginService);
});
