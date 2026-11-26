// Para probar: abre la aplicación en localhost; localhost es un contexto seguro para PWA.
// En Chrome: F12 > Lighthouse > Progressive Web App y ejecuta el análisis.
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .catch((error) => console.error('No se pudo registrar el service worker:', error));
    });
}
