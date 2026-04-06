const API_HOMEPAGE = '/assets/api/public/homepage';
const API_LOGIN = '/assets/api/auth/login';
const API_GATEWAY = '/assets/api/auth/gateway';

axios.get(API_HOMEPAGE, {
    headers: {
        //'Authorization': `Bearer ${token}`,
        //'Authorization': 'Bearer 12345',
        'Accept': 'application/json'
    }
})
.then(function (response) {
    const homepage = response.data;

    document.getElementById('hero_title').innerHTML  = homepage.data.hero.title;
    document.getElementById('hero_sub_title').innerHTML  = homepage.data.hero.sub_title;

    document.getElementById('about_us_title_shadow').innerHTML  = homepage.data.about_us.title;
    document.getElementById('about_us_title').innerHTML  = homepage.data.about_us.title;
    document.getElementById('about_us_description').innerHTML  = homepage.data.about_us.description;

    document.getElementById('clients_title_shadow').innerHTML  = homepage.data.clients.title;
    document.getElementById('clients_title').innerHTML  = homepage.data.clients.title;
    document.getElementById('clients_description').innerHTML  = homepage.data.clients.description;

    document.getElementById('copyright').innerHTML  = homepage.data.copyright;
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

document.getElementById('login').addEventListener('submit', function(e) {
    e.preventDefault();

    // Convert Form Data to a JSON Object
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    // Send via Axios
    axios.post(API_LOGIN, data)
        .then(function (response) {
            localStorage.setItem('token', response.data.data.tokens.type + " " + response.data.data.tokens.authentication);

            //Redirecting
            if (response.data.data.status) {
                window.location.href = "/intranet/gateway";
            }
            // Show response
            //document.getElementById('alert').innerText = 'Success: ' + response.data.data.status;
        })
        .catch(function (error) {
            document.getElementById("alert").style.display = "block";

            // This block runs for 4xx and 5xx status codes (errors)
            if (error.response) {
                // The server responded with a status code outside the 2xx range
                document.getElementById('alert').innerText = error.response.data.data.errors.message;
            } else if (error.request) {
                // The request was made but no response was received (e.g., network error)
                document.getElementById('alert').innerText = 'Network Error. Could not connect to server.';
            } else {
                // Something else happened in setting up the request
                document.getElementById('alert').innerText = 'An unexpected error occurred.';
            }
        });
});

const token = localStorage.getItem('token');

axios.get(API_GATEWAY, {
    headers: {
        'Authorization': `${token}`,
        'Accept': 'application/json'
    }
})
.then(function (response) {
    const homepage = response.data;

    document.getElementById('hero_title').innerHTML  = homepage.data.hero.title;
    document.getElementById('hero_sub_title').innerHTML  = homepage.data.hero.sub_title;

    document.getElementById('about_us_title_shadow').innerHTML  = homepage.data.about_us.title;
    document.getElementById('about_us_title').innerHTML  = homepage.data.about_us.title;
    document.getElementById('about_us_description').innerHTML  = homepage.data.about_us.description;

    document.getElementById('clients_title_shadow').innerHTML  = homepage.data.clients.title;
    document.getElementById('clients_title').innerHTML  = homepage.data.clients.title;
    document.getElementById('clients_description').innerHTML  = homepage.data.clients.description;

    document.getElementById('copyright').innerHTML  = homepage.data.copyright;
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
