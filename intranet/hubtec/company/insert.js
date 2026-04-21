document.getElementById('processNewCompany').addEventListener('submit', function(e) {
    e.preventDefault();

    // Convert Form Data to a JSON Object
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    const input_document_id = document.getElementById("document_id")
    input_document_id.disabled = true;

    const input_document_number = document.getElementById("document_number")
    input_document_number.disabled = true;

    const input_company_name = document.getElementById("company_name")
    input_company_name.disabled = true;

    const input_company_email = document.getElementById("company_email")
    input_company_email.disabled = true;

    const input_company_phone_number = document.getElementById("company_phone_number")
    input_company_phone_number.disabled = true;

    const input_department = document.getElementById("department")
    input_department.disabled = true;

    const input_province = document.getElementById("province")
    input_province.disabled = true;

    const input_district = document.getElementById("district")
    input_district.disabled = true;

    const input_address = document.getElementById("address")
    input_address.disabled = true;

    const btn_submit = document.getElementById('btn_submit');
    btn_submit.style.display = "none";

    const btn_loading = document.getElementById('btn_loading');
    btn_loading.style.display = "initial";

    const response = document.getElementById('response');

    const token = localStorage.getItem('token');
    const config = {
        headers: {
            'Authorization': `${token}`,
            'Accept': 'application/json',
        }
    };

    // Send via Axios
    axios.post('insert', data, config)
        .then(function (response) {
            input_document_id.value = "";
            input_document_id.disabled = false;

            input_document_number.value = "";
            input_document_number.disabled = false;

            input_company_name.value = "";
            input_company_name.disabled = false;

            input_company_email.value = "";
            input_company_email.disabled = false;

            input_company_phone_number.value = "";
            input_company_phone_number.disabled = false;

            input_department.value = "";
            input_department.disabled = false;

            input_province.value = "";
            input_province.disabled = false;

            input_district.value = "";
            input_district.disabled = false;

            input_address.value = "";
            input_address.disabled = false;

            btn_submit.style.display = "initial";
            btn_loading.style.display = "none";

            response.style.display = "block";
            response.className = 'alert alert-success';
            response.innerText = response.data.data.message;
        })
        .catch(function (error) {
            input_document_id.disabled = false;
            input_document_number.disabled = false;
            input_company_name.disabled = false;
            input_company_email.disabled = false;
            input_company_phone_number.disabled = false;
            input_department.disabled = false;
            input_province.disabled = false;
            input_district.disabled = false;
            input_address.disabled = false;

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
