;(function(d, w, undefined) {
    const baseUrl = `http://${API_HOST}/api`;
    const defaultHeaders = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    };

    const personsList = d.querySelector('.persons-list');
    const personsListCount = d.querySelector('.persons-list-count');

    const api = () => {
        const doFetch = (method, endpoint, data, headers = {}) => {
            const url = `${baseUrl}${endpoint}`;
            const options = {
                method: method.toUpperCase(),
                headers: { ...defaultHeaders, ...headers },
                body: data != null ? JSON.stringify(data) : undefined,
            };

            return fetch(url, options)
                .then(res => (res.ok ? res.json() : Promise.reject(res)));
        };

        ['get', 'post', 'put', 'delete'].forEach(method => {
            doFetch[method] = doFetch.bind(null, method);
        });

        return doFetch;
    };


    api().get('/person').then((res) => {
        const htmlPersonsList = res.data.map((person, idx) => {
            return `<li><a data-uuid="${person.uuid}">${person.name}</a></li>`;
        });

        personsList.insertAdjacentHTML('beforeend', htmlPersonsList.join(''));
        personsListCount.insertAdjacentHTML('beforeend', htmlPersonsList.length);
    });

}(document, window));
