document.getElementById('contactForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const message = document.getElementById('message').value;

    if (!name || !email || !phone || !message) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Todos los campos son obligatorios.",
            confirmButtonColor: "#0D6EFD",
        });
        return;
    }

    fetch(window.location.origin + '/api/mail/send', {
        method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ name, email, phone, message })
        })
        .then(response => {
            if (response.ok) {
                document.getElementById('contactForm').reset();
                return response.json();
            } else if (response.status === 400) {
                return response.json().then(err => {
                    throw err.message || 'Error en la solicitud';
                });
            } else {
                return response.json().then(err => {
                    throw err.message || 'Error desconocido';
                });
            }
        })
        .then(data => {
            Swal.fire({
                icon: "success",
                title: "Success",
                text: data.message,
                timer: 1000,
                timerProgressBar: true,
                confirmButtonColor: "#0D6EFD",
                showConfirmButton: false,
            });
        })
        .catch(error => {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: error || "An unknown error occurred.",
                confirmButtonColor: "#0D6EFD",
            });
        });
});

