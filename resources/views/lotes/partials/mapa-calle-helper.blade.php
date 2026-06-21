<script>
window.AgroFusionMapaCalle = {
    async resolver(lat, lng) {
        try {
            const url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2'
                + '&lat=' + encodeURIComponent(lat)
                + '&lon=' + encodeURIComponent(lng)
                + '&zoom=17&addressdetails=1';
            const res = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'Accept-Language': 'es',
                },
            });
            if (!res.ok) return null;
            const data = await res.json();
            const a = data.address || {};
            const calle = a.road || a.pedestrian || a.path || a.footway || a.residential || a.hamlet;
            const zona = a.suburb || a.neighbourhood || a.quarter || a.village || a.county;
            const ciudad = a.city || a.town || a.municipality || 'Santa Cruz de la Sierra';
            const partes = [calle, zona, ciudad].filter(Boolean);
            if (partes.length) {
                return partes.join(', ');
            }
            if (data.display_name) {
                return data.display_name.split(',').slice(0, 3).join(',').trim();
            }
        } catch (e) {
            console.warn('Geocodificación inversa no disponible', e);
        }
        return null;
    },

    esTextoGps(texto) {
        if (!texto) return true;
        return /^(?:Parcela\s+)?GPS\s/i.test(texto.trim())
            || /^-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?$/.test(texto.trim());
    },
};
</script>
