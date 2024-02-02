class StylaPageApiService {
    constructor(httpClient, loginService) {
        this.httpClient = httpClient;
        this.loginService = loginService;
        this.name = 'stylaPageApiService';
    }

    doSynchronization() {
        const headers = this.getHeaders();
        return this.httpClient.post('/styla/page/_action/synchronize-pages', {}, { headers });
    }

    scheduleSynchronization() {
        const headers = this.getHeaders();
        return this.httpClient.post('/styla/page/_action/schedule-pages-synchronization', {}, { headers });
    }

    refreshPageDetails(pageId) {
        const headers = this.getHeaders();
        return this.httpClient.post('/styla/page/_action/refresh-details/'+pageId, {}, { headers });
    }

    getHeaders() {
        return {
            Accept: 'application/json',
            Authorization: `Bearer ${this.loginService.getToken()}`,
            'Content-Type': 'application/json'
        };
    }
}

export default StylaPageApiService;
