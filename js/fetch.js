async function fetchApi(url, referrer) {
    try {
        const res = await fetch(url, {
            referrer
        });
        const json = await res.json();
        return {
            "type": "json",
            "code": res.status,
            "data": json,
            "headers": res.headers
        };

    } catch (e) {
        return {
            "type": "json",
            "code": 503,
            "data": null,
            "headers": e.headers
        };
    }
}

window.fetchApi = fetchApi;
