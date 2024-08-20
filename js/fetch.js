class ApiError extends Error {
    code = -1;
    headers = {};

    constructor(code, headers) {
        super();
        this.code = code;
        this.headers = headers;
    }
}

async function fetchApi(url, referrer) {
    try {
        const res = await fetch(url, {
            referrer
        });

        let headers = {};
        for (const h of res.headers) {
            headers[h[0]] = h[1];
        }

        if (res.ok) {
            const text = await res.text();
            if (text !== "") {
                const json = JSON.parse(text);
                return {
                    "type": "json",
                    "code": res.status,
                    "data": json,
                    "headers": headers
                };
            }

            // Empty response
            throw new ApiError(res.status, headers);
        }

        // HTTP Error
        throw new ApiError(res.status, headers);
    } catch (e) {
        return {
            "type": "json",
            "code": e.code ?? 503,
            "data": null,
            "headers": e.headers ?? {}
        };
    }
}

window.fetchApi = fetchApi;
