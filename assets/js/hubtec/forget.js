const API_LOGIN = '/assets/api/auth/forget';

document.getElementById('forget').addEventListener('submit', function(e) {
    e.preventDefault();

    // Convert Form Data to a JSON Object
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    const input_email = document.getElementById("email")
    input_email.disabled = true;

    const button_submit = document.getElementById('button_submit');
    button_submit.style.display = "none";

    const button_loading = document.getElementById('button_loading');
    button_loading.style.display = "initial";

    const response_alert = document.getElementById('alert');

    // Send via Axios
    axios.post(API_LOGIN, data)
        .then(function (response) {
            input_email.value = "";
            input_email.disabled = false;

            button_submit.style.display = "initial";
            button_loading.style.display = "none";

            response_alert.style.display = "block";
            response_alert.className = 'alert alert-success';
            response_alert.innerText = response.data.data.message;
        })
        .catch(function (error) {
            input_email.disabled = false;

            button_submit.style.display = "initial";
            button_loading.style.display = "none";

            response_alert.style.display = "block";

            // This block runs for 4xx and 5xx status codes (errors)
            if (error.response) {
                // The server responded with a status code outside the 2xx range
                response_alert.className = 'alert alert-danger';
                response_alert.innerText = error.response.data.data.errors.message;
            } else if (error.request) {
                // The request was made but no response was received (e.g., network error)
                response_alert.className = 'alert alert-danger';
                response_alert.innerText = 'Network Error. Could not connect to server.';
            } else {
                // Something else happened in setting up the request
                response_alert.className = 'alert alert-danger';
                response_alert.innerText = 'An unexpected error occurred.';
            }
        });
});
