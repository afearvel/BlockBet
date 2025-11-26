console.log('🛡️ BlockBet Popup cargado');

document.addEventListener('DOMContentLoaded', function() {
    console.log(' DOM listo');
    
    // Verificar sesión guardada
    chrome.storage.local.get(['usuarioEmail', 'usuarioId'], function(storage) {
        console.log(' Storage:', storage);
        
        if (storage.usuarioEmail && storage.usuarioId) {
            mostrarStats(storage.usuarioEmail, storage.usuarioId);
        } else {
            mostrarLogin();
        }
    });
    
    // Event Listeners
    document.getElementById('btn-login').addEventListener('click', iniciarSesion);
    
    document.getElementById('btn-dashboard').addEventListener('click', function() {
        chrome.tabs.create({ url: 'http://localhost/blockbet/dashboard.php' });
    });
    
    document.getElementById('btn-logout').addEventListener('click', function() {
        if (confirm('¿Desactivar la protección?\n\nLos sitios ya no serán bloqueados.')) {
            // Primero notificar al background que limpie los sitios
            chrome.runtime.sendMessage({
                action: 'clearSitios'
            }, function() {
                // Luego limpiar storage local
                chrome.storage.local.clear(function() {
                    console.log('✅ Sesión cerrada y protección desactivada');
                    mostrarLogin();
                });
            });
        }
    });
    
    // Enter para login
    document.getElementById('email').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') iniciarSesion();
    });
    
    document.getElementById('password').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') iniciarSesion();
    });
});

function mostrarLogin() {
    console.log(' Mostrando login');
    document.getElementById('login-section').classList.remove('hidden');
    document.getElementById('stats-section').classList.add('hidden');
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
}

function mostrarStats(email, usuarioId) {
    console.log(' Mostrando stats para:', email);
    
    document.getElementById('login-section').classList.add('hidden');
    document.getElementById('stats-section').classList.remove('hidden');
    document.getElementById('user-email').textContent = email;
    
    // Iniciales del avatar
    var iniciales = email.substring(0, 2).toUpperCase();
    document.getElementById('user-avatar').textContent = iniciales;
    
    // Cargar estadísticas
    fetch('http://localhost/blockbet/api/stats_extension.php?usuario_id=' + usuarioId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            console.log(' Stats recibidas:', data);
            
            if (data.success) {
                document.getElementById('total-sitios').textContent = data.total_sitios;
                document.getElementById('intentos-hoy').textContent = data.intentos_hoy;
                
                // Animación de números
                animarNumero('total-sitios', data.total_sitios);
                animarNumero('intentos-hoy', data.intentos_hoy);
            }
        })
        .catch(function(error) {
            console.error(' Error al cargar stats:', error);
        });
}

function iniciarSesion() {
    console.log(' Iniciando sesión...');
    
    var email = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value;
    var errorMsg = document.getElementById('error-msg');
    var btnLogin = document.getElementById('btn-login');
    
    if (!email || !password) {
        mostrarError(' Por favor completa todos los campos');
        return;
    }
    
    // Validar email
    if (!email.includes('@')) {
        mostrarError(' Ingresa un email válido');
        return;
    }
    
    errorMsg.classList.add('hidden');
    btnLogin.innerHTML = '<span class="loading"></span> Verificando...';
    btnLogin.disabled = true;
    
    fetch('http://localhost/blockbet/api/login_extension.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email, password: password })
    })
    .then(function(response) { 
        return response.json(); 
    })
    .then(function(data) {
        console.log(' Respuesta login:', data);
        
        if (data.success) {
            chrome.storage.local.set({
                usuarioId: data.usuario_id,
                usuarioEmail: email
            }, function() {
                console.log(' Datos guardados en storage');
                
                chrome.runtime.sendMessage({
                    action: 'setUsuario',
                    usuarioId: data.usuario_id
                }, function(response) {
                    console.log(' Notificado a background:', response);
                    mostrarStats(email, data.usuario_id);
                    btnLogin.innerHTML = ' Activar Protección';
                    btnLogin.disabled = false;
                });
            });
        } else {
            mostrarError(' ' + (data.message || 'Credenciales incorrectas'));
            btnLogin.innerHTML = ' Activar Protección';
            btnLogin.disabled = false;
        }
    })
    .catch(function(error) {
        console.error(' Error:', error);
        mostrarError(' Error de conexión. Verifica que el servidor esté activo.');
        btnLogin.innerHTML = ' Activar Protección';
        btnLogin.disabled = false;
    });
}

function mostrarError(mensaje) {
    var errorMsg = document.getElementById('error-msg');
    errorMsg.textContent = mensaje;
    errorMsg.classList.remove('hidden');
}

function animarNumero(elementId, valorFinal) {
    var elemento = document.getElementById(elementId);
    var valorActual = 0;
    var incremento = valorFinal / 20;
    
    var intervalo = setInterval(function() {
        valorActual += incremento;
        if (valorActual >= valorFinal) {
            valorActual = valorFinal;
            clearInterval(intervalo);
        }
        elemento.textContent = Math.floor(valorActual);
    }, 30);
}