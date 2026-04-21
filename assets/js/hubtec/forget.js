const API_LOGIN = '/assets/api/auth/forget';

document.getElementById('forget').addEventListener('submit', function(e) {
    e.preventDefault();

    // Convert Form Data to a JSON Object
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    const input_email = document.getElementById("email")
    input_email.disabled = true;

    const btn_submit = document.getElementById('btn_submit');
    btn_submit.style.display = "none";

    const btn_loading = document.getElementById('btn_loading');
    btn_loading.style.display = "initial";

    const response = document.getElementById('response');

    // Send via Axios
    axios.post(API_LOGIN, data)
        .then(function (response) {
            input_email.value = "";
            input_email.disabled = false;

            btn_submit.style.display = "initial";
            btn_loading.style.display = "none";

            response.style.display = "block";
            response.className = 'alert alert-success';
            response.innerText = response.data.data.message;
        })
        .catch(function (error) {
            input_email.disabled = false;

            btn_submit.style.display = "initial";
            btn_loading.style.display = "none";

            response.style.display = "block";

            // This block runs for 4xx and 5xx status codes (errors)
            if (error.response) {
                // The server responded with a status code outside the 2xx range
                response.className = 'alert alert-danger';
                response.innerText = error.response.data.data.errors.message;
            } else if (error.request) {
                // The request was made but no response was received (e.g., network error)
                response.className = 'alert alert-danger';
                response.innerText = 'Network Error. Could not connect to server.';
            } else {
                // Something else happened in setting up the request
                response.className = 'alert alert-danger';
                response.innerText = 'An unexpected error occurred.';
            }
        });
});
