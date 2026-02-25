let googleMapsScriptPromise = null;

export const loadGoogleMapsScript = (apiKey) => {
    // Return existing promise if already loading or loaded
    if (googleMapsScriptPromise) {
        return googleMapsScriptPromise;
    }

    // Check if already loaded
    if (window.google && window.google.maps && window.google.maps.places) {
        return Promise.resolve();
    }

    googleMapsScriptPromise = new Promise((resolve, reject) => {
        // Create script element
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&language=es`;
        script.async = true;
        script.defer = true;

        script.onload = () => {
            resolve();
        };

        script.onerror = () => {
            googleMapsScriptPromise = null;
            reject(new Error('Failed to load Google Maps script'));
        };

        document.head.appendChild(script);
    });

    return googleMapsScriptPromise;
};
