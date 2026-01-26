import { renderFitxaInformacio } from '../../components/renderFitxaInformacio/renderFitxaInformacio';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import * as L from 'leaflet';
import 'leaflet/dist/leaflet.css'; // Importar el CSS de Leaflet

export async function fitxaEspai() {
  const isAdmin = await getIsAdmin();
  const url = window.location.href;
  const pageType = getPageType(url);
  let slug: string = '';
  let gestioUrl: string = '';

  if (isAdmin) {
    slug = pageType[3];
    gestioUrl = '/gestio';
  } else {
    slug = pageType[2];
  }

  const response = await fetch(`https://${window.location.host}/api/viatges/get/?fitxaEspaiDetalls=${slug}`);
  const result = await response.json();

  const data = {
    nameImg: result.nameImg,
    alt: result.alt,
    tipusImatge: 'viatge-espai',
    details: {
      Titol: result.nom,
      Ciutat: result.ciutat,
      Fundació: result.EspFundacio,
      'Tipus espai': result.TipusNom,
      Web: result.EspWeb,
      'Data de creació': result.dateCreated,
      'Última modificació': result.dateModified,
      Descripció: result.EspDescripcio,
    },
  };

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
    const lat = result.coordinades_latitud; // Latitud
    const lon = result.coordinades_longitud; // Longitud

    // Crear el mapa y centrarlo en las coordenadas
    const map = L.map('dadesMapa').setView([lat, lon], 17); // 13 es el nivel de zoom

    // Añadir capa de OpenStreetMap al mapa
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    // Añadir un marcador en las coordenadas
    L.marker([lat, lon]).addTo(map).bindPopup(`<b>${result.nom}</b><br>${result.ciutat}`).openPopup();

    // Ajustar el tamaño del mapa si el contenedor cambia o no es visible inicialmente
    map.invalidateSize();
  }
}
