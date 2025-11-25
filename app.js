// ========== TOGGLE ENTRE LOGIN Y REGISTRO ==========
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.toggle-btn');
    const forms = document.querySelectorAll('.auth-form');

    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remover clase active de todos los botones
            toggleButtons.forEach(btn => btn.classList.remove('active'));
            
            // Agregar clase active al botón clickeado
            this.classList.add('active');

            // Obtener el modo (login o register)
            const mode = this.getAttribute('data-mode');

            // Ocultar todos los formularios
            forms.forEach(form => form.classList.add('hidden'));

            // Mostrar el formulario seleccionado
            const selectedForm = document.getElementById(`${mode}-form`);
            if (selectedForm) {
                selectedForm.classList.remove('hidden');
            }
        });
    });

    // ========== MANEJO DE FORMULARIOS ==========
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            // VALIDAR SOLO CONTRASEÑAS
            const password = document.getElementById('register-password').value;
            const confirm = document.getElementById('register-confirm').value;

            if (password !== confirm) {
                e.preventDefault(); // SOLO detener si hay error
                alert('Las contraseñas no coinciden');
                return;
            }
        });
    }


    // ========== DASHBOARD INTERACTIVIDAD ==========
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // ========== EFECTOS EN CARDS ==========
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease-out';
        });
    });

    // ========== GRÁFICO INTERACTIVO ==========
    const chartBars = document.querySelectorAll('.chart-bar');
    chartBars.forEach(bar => {
        bar.addEventListener('mouseenter', function() {
            const title = this.getAttribute('title');
            console.log(title);
        });
    });

    // ========== SIMULAR CARGA ==========
    window.addEventListener('load', function() {
        console.log(' BlockBet cargado correctamente');
    });
});
