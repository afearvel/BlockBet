// ============================================
// BACKGROUND.JS - Motor que bloquea sitios
// ============================================

let sitiosBloqueados = [];
let usuarioId = null;
let proteccionActiva = false; // ⭐ NUEVA BANDERA

// Al instalar
chrome.runtime.onInstalled.addListener(function() {
  console.log('BlockBet instalado');
  cargarSitiosBloqueados();
});

// Al iniciar navegador
chrome.runtime.onStartup.addListener(function() {
  cargarSitiosBloqueados();
});

// Cargar sitios desde servidor
async function cargarSitiosBloqueados() {
  try {
    const storage = await chrome.storage.local.get(['usuarioId']);
    usuarioId = storage.usuarioId;
    
    if (!usuarioId) {
      console.log('No hay usuario logueado');
      proteccionActiva = false; // ⭐ Desactivar
      return;
    }
    
    const url = 'http://localhost/blockbet/api/obtener_sitios.php?usuario_id=' + usuarioId;
    const response = await fetch(url);
    const data = await response.json();
    
    if (data.success) {
      sitiosBloqueados = data.sitios.map(function(s) { return s.dominio; });
      proteccionActiva = true; // ⭐ Activar
      console.log('✅ Protección ACTIVADA. Sitios cargados:', sitiosBloqueados);
    }
  } catch (error) {
    console.error('Error al cargar sitios:', error);
    proteccionActiva = false;
  }
}

// Interceptar cuando se navega a una página
chrome.tabs.onUpdated.addListener(function(tabId, changeInfo, tab) {
  if (changeInfo.url) {
    verificarYBloquear(tabId, changeInfo.url);
  }
});

// Verificar y bloquear
function verificarYBloquear(tabId, url) {
  try {
    // ⭐ VERIFICAR SI LA PROTECCIÓN ESTÁ ACTIVA
    if (!proteccionActiva) {
      console.log('🔓 Protección desactivada, permitiendo acceso');
      return;
    }
    
    if (!url || url.startsWith('chrome://') || url.startsWith('chrome-extension://')) {
      return;
    }
    
    const urlObj = new URL(url);
    const dominio = urlObj.hostname.replace('www.', '');
    
    // Verificar si está bloqueado
    let estaBloqueado = false;
    for (let i = 0; i < sitiosBloqueados.length; i++) {
      if (dominio.includes(sitiosBloqueados[i]) || sitiosBloqueados[i].includes(dominio)) {
        estaBloqueado = true;
        break;
      }
    }
    
    if (estaBloqueado) {
      console.log('🚫 BLOQUEADO:', dominio);
      
      // Registrar intento
      registrarIntento(dominio);
      
      // Redirigir a página de bloqueo
      const bloqueadoUrl = chrome.runtime.getURL('bloqueado.html') + '?sitio=' + encodeURIComponent(dominio);
      chrome.tabs.update(tabId, { url: bloqueadoUrl });
    }
  } catch (error) {
    console.error('Error al verificar:', error);
  }
}

// Registrar intento
async function registrarIntento(dominio) {
  if (!usuarioId) return;
  
  try {
    await fetch('http://localhost/blockbet/api/registrar_intento.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        usuario_id: usuarioId,
        dominio: dominio
      })
    });
    console.log('Intento registrado:', dominio);
  } catch (error) {
    console.error('Error al registrar:', error);
  }
}

// Escuchar mensajes
chrome.runtime.onMessage.addListener(function(request, sender, sendResponse) {
  if (request.action === 'setUsuario') {
    usuarioId = request.usuarioId;
    chrome.storage.local.set({usuarioId: usuarioId});
    cargarSitiosBloqueados().then(function() {
      sendResponse({success: true});
    });
    return true;
  }
  
  // Desactivar protección 
  if (request.action === 'clearSitios') {
    console.log('🧹 Desactivando protección...');
    sitiosBloqueados = [];
    usuarioId = null;
    proteccionActiva = false;
    console.log('✅ Protección DESACTIVADA');
    sendResponse({success: true});
    return true;
  }
});

// Recargar cada 5 minutos (solo si está activa)
setInterval(function() {
  if (proteccionActiva && usuarioId) {
    cargarSitiosBloqueados();
  }
}, 5 * 60 * 1000);