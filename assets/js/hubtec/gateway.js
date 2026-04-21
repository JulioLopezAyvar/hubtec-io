const API_GATEWAY = '/assets/api/auth/gateway';
//const token = localStorage.getItem('token');

axios.get(API_GATEWAY, {
    headers: {
        'Authorization': `${token}`,
        'Accept': 'application/json'
    }
})
.then(function (response) {
    const gateway = response.data.data;

    const list = document.getElementById('companies-list');

    // Generate HTML string
    let htmlContent = '';
    gateway.forEach(item => {
        htmlContent += `
            <div class="mb-3">
                <a class="icon-link" href="${item.url}/">
                    <svg xmlns="http://www.w3.org/2000/svg" class="bi" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                    </svg>
                    ${item.name} <span class="badge rounded-pill text-bg-light">${item.profile}</span>
                </a>
            </div>
        `;
    });

    // Inject into DOM
    list.innerHTML = htmlContent;
})
/*
.catch(function (error) {
    // This block runs for 4xx and 5xx status codes (errors)
    if (error.response) {
        // The server responded with a status code outside the 2xx range
        document.getElementById('hero_title').innerText = "Internal error";
        document.getElementById('hero_sub_title').innerText = "Internal error";

        document.getElementById('about_us_title').innerText = "Internal error";
        document.getElementById('about_us_description').innerText = "Internal error";
    } else if (error.request) {
        // The request was made but no response was received (e.g., network error)
        document.getElementById('hero_title').innerText = "Network error. Could not connect to server.";
        document.getElementById('hero_sub_title').innerText = "Network error. Could not connect to server.";

        document.getElementById('about_us_title').innerText = "Network error. Could not connect to server.";
        document.getElementById('about_us_description').innerText = "Network error. Could not connect to server.";
    } else {
        // Something else happened in setting up the request
        document.getElementById('hero_title').innerText = "An unexpected error occurred.";
        document.getElementById('hero_sub_title').innerText = "An unexpected error occurred.";

        document.getElementById('about_us_title').innerText = "An unexpected error occurred.";
        document.getElementById('about_us_description').innerText = "An unexpected error occurred.";
    }
})*/;
