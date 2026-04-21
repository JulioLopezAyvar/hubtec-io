const API_PROFILE = '/assets/api/me/profile';
const token = localStorage.getItem('token');

axios.get(API_PROFILE, {
    headers: {
        'Authorization': `${token}`,
        'Accept': 'application/json'
    }
})
.then(function (response) {
    const user = response.data;

    const id = document.getElementById('id');
    if (id) {
        id.innerHTML = user.data.id;
    }

    const full_name = document.getElementById('full_name');
    if (full_name) {
        full_name.innerHTML = user.data.full_name;
    }

    const email = document.getElementById('email');
    if (email) {
        email.innerHTML = user.data.email;
    }

    const phone_number = document.getElementById('phone_number');
    if (phone_number) {
        phone_number.innerHTML = user.data.phone_number;
    }
})
.catch(function (error) {
    // This block runs for 4xx and 5xx status codes (errors)
    if (error.response) {
        // The server responded with a status code outside the 2xx range
        document.getElementById('id').innerText = "Internal error";
        document.getElementById('full_name').innerText = "Internal error";
        document.getElementById('email').innerText = "Internal error";
        document.getElementById('phone_number').innerText = "Internal error";
    } else if (error.request) {
        // The request was made but no response was received (e.g., network error)
        document.getElementById('id').innerText = "Network error. Could not connect to server.";
        document.getElementById('full_name').innerText = "Network error. Could not connect to server.";
        document.getElementById('email').innerText = "Network error. Could not connect to server.";
        document.getElementById('phone_number').innerText = "Network error. Could not connect to server.";
    } else {
        // Something else happened in setting up the request
        document.getElementById('id').innerText = "An unexpected error occurred.";
        document.getElementById('full_name').innerText = "An unexpected error occurred.";
        document.getElementById('email').innerText = "An unexpected error occurred.";
        document.getElementById('phone_number').innerText = "An unexpected error occurred.";
    }
});

