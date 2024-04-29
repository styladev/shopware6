class StylaPageApiService {
    constructor(httpClient, loginService) {
        this.httpClient = httpClient;
        this.loginService = loginService;
        this.name = 'stylaPageApiService';
    }

    getLastSuccessSynchronizationDate() {
        const headers = this.getHeaders();
        return this.httpClient.get('/styla/synchronization/page/_action/get_last_success_date_time', {}, { headers });
    }

    resetSynchronizationStatus() {
        const headers = this.getHeaders();
        return this.httpClient.get('/styla/synchronization/page/_action/reset_synchronization_status', {}, { headers });
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
