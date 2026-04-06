const API_HOMEPAGE = '/assets/api/public/homepage';

axios.get(API_HOMEPAGE, {
    headers: {
        //'Authorization': `Bearer ${token}`,
        //'Authorization': 'Bearer 12345',
        'Accept': 'application/json'
    }
})
.then(function (response) {
    const homepage = response.data;

    const hero_title = document.getElementById('hero_title');
    if (hero_title) {
        hero_title.innerHTML = homepage.data.hero.title;
    }

    const hero_sub_title = document.getElementById('hero_sub_title');
    if (hero_sub_title) {
        hero_sub_title.innerHTML = homepage.data.hero.sub_title;
    }

    const about_us_title_shadow = document.getElementById('about_us_title_shadow');
    if (about_us_title_shadow) {
        about_us_title_shadow.innerHTML = homepage.data.about_us.title;
    }

    const about_us_title = document.getElementById('about_us_title');
    if (about_us_title) {
        about_us_title.innerHTML = homepage.data.about_us.title;
    }

    const about_us_description = document.getElementById('about_us_description');
    if (about_us_description) {
        about_us_description.innerHTML = homepage.data.about_us.description;
    }

    const clients_title_shadow = document.getElementById('clients_title_shadow');
    if (clients_title_shadow) {
        clients_title_shadow.innerHTML = homepage.data.clients.title;
    }

    const clients_title = document.getElementById('clients_title');
    if (clients_title) {
        clients_title.innerHTML = homepage.data.clients.title;
    }

    const clients_description = document.getElementById('clients_description');
    if (clients_description) {
        clients_description.innerHTML = homepage.data.clients.description;
    }

    const copyright = document.getElementById('copyright');
    if (copyright) {
        copyright.innerHTML = homepage.data.copyright;
    }
})
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
});
