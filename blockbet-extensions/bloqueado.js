// bloqueado.js
const params = new URLSearchParams(window.location.search);
const sitio = params.get('sitio');

if (sitio) {
    document.getElementById('sitio-loading').style.display = 'none';
    const sitioDiv = document.getElementById('sitio-bloqueado');
    sitioDiv.textContent = '🚫 ' + sitio;
    sitioDiv.style.display = 'block';
}