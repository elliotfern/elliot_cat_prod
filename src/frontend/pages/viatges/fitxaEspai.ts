import { renderFitxaInformacio } from '../../components/renderFitxaInformacio/renderFitxaInformacio';
import { getIsAdmin } from '../../services/auth/isAdmin';
import * as L from 'leaflet';
import 'leaflet/dist/leaflet.css'; // Importar el CSS de Leaflet
import { Espai } from '../../types/Espai';
import { api } from '../../core/api/client';

export async function fitxaEspai(espai: string) {
  const isAdmin = await getIsAdmin();

  let result: Espai;
  try {
    result = await api.get<Espai>(`viatges/get/fitxaEspaiDetalls`, {
      espai,
    });
  } catch (error) {
    console.error(error);

    return;
  }

  const data = {
    nameImg: result.nameImg ?? '',
    alt: result.alt ?? '',
    tipusImatge: 'viatge-espai',
    details: {
      Titol: result.nom ?? '',
      Ciutat: result.ciutat ?? '—',
      Fundació: result.any_fundacio ?? '',
      'Tipus espai': result.tipus ?? '',
      Web: result.web ?? '',
      'Data de creació': result.dateCreated ?? '',
      'Última modificació': result.dateModified ?? '',
      Descripció: result.descripcio ?? '',
    },
  };

  const containerBoto = document.getElementById('modificaBoto');

  if (isAdmin && containerBoto && result?.id) {
    // Crear botón
    const button = document.createElement('button');

    button.textContent = 'Modifica fitxa';
    button.className = 'button btn-gran btn-secondari';

    // Evento click (mejor que inline onclick)
    button.addEventListener('click', () => {
      window.location.href = `/gestio/viatges/modifica-espai/${result.id}`;
    });

    // Limpiar por si acaso
    containerBoto.innerHTML = '';

    // Insertar botón
    containerBoto.appendChild(button);
  }

  const container = document.getElementById('dadesContainer');
  if (container) {
    container.innerHTML = ''; // limpiar contenido previo
    container.appendChild(renderFitxaInformacio(data));
  }

  const containerMapa = document.getElementById('dadesMapa');
  if (containerMapa) {
    // Configurar las rutas de los iconos de marcador
    L.Icon.Default.mergeOptions({
      iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
      iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
      shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    });

    const lat = parseFloat(result.coordinades_latitud);
    const lon = parseFloat(result.coordinades_longitud);

    if (!isNaN(lat) && !isNaN(lon)) {
      const map = L.map('dadesMapa').setView([lat, lon], 17);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
      }).addTo(map);

      L.marker([lat, lon]).addTo(map).bindPopup(`<b>${result.nom}</b><br>${result.ciutat}`).openPopup();

      map.invalidateSize();
    } else {
      containerMapa.innerHTML = '<p>Sense coordenades disponibles</p>';
    }
  }
}
