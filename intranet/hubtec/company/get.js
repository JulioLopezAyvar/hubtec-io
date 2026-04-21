const token = localStorage.getItem('token');

axios.get('get', {
    headers: {
        'Authorization': `${token}`,
        'Accept': 'application/json'
    }
})
.then(function (response) {
    const companies = response.data.data;
    const list = document.getElementById('companies-list');

    // Generate HTML string
    let htmlContent = `
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>ID</td>
                        <th>Documento</td>
                        <th>Razón social</td>
                        <th>Correo</td>
                        <th>Teléfono</td>
                        <th>Estado</td>
                        <th>Acciones</td>
                    </tr>
                </thead>
                <tbody>
    `;

    if (!companies || companies.length === 0) {
        htmlContent += `
                    <tr>
                        <td colspan="7">No existen registros</td>
                    </tr>
        `;
    }
    else {
        companies.forEach(item => {
            htmlContent += `
                    <tr>
                        <td>${item.id}</td>
                        <td>${item.document}</td>
                        <td>${item.full_name}</td>
                        <td>${item.email}</td>
                        <td>${item.phone_number}</td>
                        <td>${item.state}</td>
                        <td>
                            <a href="update?id=${item.id}" class="link-offset-2 link-underline link-underline-opacity-0 mx-1" onclick="window.open(this.href, 'newwindow', 'width=600,height=800'); return false;">
                                <i class="ri-file-edit-fill" style="color:blue;"></i>
                            </a>
            `;

            if (item.state == 1) {
                htmlContent += `
                            <a href="delete?id=${item.id}" class="link-offset-2 link-underline link-underline-opacity-0 mx-1" onclick="window.open(this.href, 'newwindow', 'width=600,height=450'); return false;">
                                <i class="ri-close-circle-fill" style="color:red;"></i>
                            </a>
                `;
            }

            htmlContent += `
                        </td>
                    </tr>
            `;
        });
    }

    htmlContent += `
                </tbody>
            </table>
        </div>
    `;

    // Inject into DOM
    list.innerHTML = htmlContent;
})
.catch(function (error) {
    /*
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
    */
});
