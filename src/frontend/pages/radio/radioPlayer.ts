// radioPlayer.ts
// Núcleo genérico y reutilizable para reproductores de radio online.
// Cada emisora aporta solo su configuración concreta (URLs + cómo parsear
// la respuesta de su API de "programa en emisión"); toda la lógica común
// (logo, stream, fetch del programa, horario, reprogramación) vive aquí.

import Hls from 'hls.js';

export interface ProgramaInfo {
  titulo: string;
  descripcion: string;
  inicio?: Date;
  fin?: Date;
}

export interface RadioPlayerIds {
  audio?: string;
  logo?: string;
  programa?: string;
  descripcion?: string;
  horarios?: string;
  btnActualizar?: string;
}

export interface RadioPlayerConfig {
  /** URL del stream de audio en directo. */
  streamUrl: string;
  /** true si el stream es HLS y hace falta hls.js para reproducirlo.
   *  false (por defecto) si el <audio><source> del HTML ya apunta al stream. */
  useHls?: boolean;

  /** URL de la API que da información del programa en emisión. Omite junto con parsePrograma si la emisora no tiene una fuente fiable de esta info. */
  programaApiUrl?: string;
  /** Convierte la respuesta cruda (ya parseada de JSON) de esa API en un ProgramaInfo homogéneo. */
  parsePrograma?: (raw: any) => ProgramaInfo;

  /** Logo de la emisora. */
  logoUrl: string;
  logoAlt: string;
  /** Clase CSS del <img> del logo (por defecto 'logo-radio'). */
  logoClass?: string;

  /** IDs de los elementos del HTML (con valores por defecto habituales). */
  ids?: RadioPlayerIds;

  /**
   * Conecta los controles de reproducción/volumen. Por defecto busca los
   * botones ▶️⏸️🔇🔊🔉 dentro de `.controls-radio` (patrón de Rai Radio 3).
   * Pásalo si tu emisora usa otros ids/botones (p. ej. Catalunya Música,
   * que solo tiene volumen con ids propios).
   */
  initControls?: (audio: HTMLAudioElement) => void;

  /** Refresco periódico de seguridad, en ms (por defecto 15 min). */
  intervaloRefresco?: number;
  /** Reintento tras un error al pedir el programa, en ms (por defecto 10 min). */
  intervaloError?: number;
}

const DEFAULT_IDS: Required<RadioPlayerIds> = {
  audio: 'audio',
  logo: 'logo',
  programa: 'programa',
  descripcion: 'descripcion',
  horarios: 'horarios',
  btnActualizar: 'btnActualizar',
};

function getEl<T extends HTMLElement>(id: string): T {
  const el = document.getElementById(id);
  if (!el) {
    throw new Error(`Elemento con id "${id}" no encontrado`);
  }
  return el as T;
}

function initLogo(id: string, url: string, alt: string, className: string): void {
  const elLogo = getEl<HTMLDivElement>(id);
  const img = document.createElement('img');
  img.src = url;
  img.alt = alt;
  img.className = className;
  elLogo.appendChild(img);
}

function intentarReproducir(audio: HTMLAudioElement): void {
  // Los navegadores bloquean el autoplay sin gesto previo del usuario.
  // Si falla, no es un error real: el usuario podrá darle a ▶️ manualmente.
  audio.play().catch((error) => {
    console.warn('Autoplay bloqueado por el navegador, pulsa ▶️ para reproducir:', error);
  });
}

function initStream(audio: HTMLAudioElement, streamUrl: string, useHls: boolean): void {
  if (!useHls) {
    // Stream directo (mp3/icecast, etc.): el propio JS asigna el src,
    // así el <audio> del HTML puede quedar vacío en todas las emisoras.
    audio.src = streamUrl;
    intentarReproducir(audio);
    return;
  }

  if (Hls.isSupported()) {
    const hls = new Hls();
    hls.loadSource(streamUrl);
    hls.attachMedia(audio);
    hls.on(Hls.Events.MANIFEST_PARSED, () => {
      intentarReproducir(audio);
    });
  } else if (audio.canPlayType('application/vnd.apple.mpegurl')) {
    // Safari y otros navegadores con soporte nativo para HLS
    audio.src = streamUrl;
    intentarReproducir(audio);
  }
}

function initControlesPorDefecto(audio: HTMLAudioElement): void {
  const controls = document.querySelector<HTMLDivElement>('.controls-radio');
  if (!controls) return;

  const [btnPlay, btnPause, btnMute, btnVolUp, btnVolDown] = controls.querySelectorAll<HTMLButtonElement>('button');

  btnPlay?.addEventListener('click', () => {
    audio.play().catch((error) => {
      console.warn('No se ha podido reproducir el audio:', error);
    });
  });
  btnPause?.addEventListener('click', () => audio.pause());
  btnMute?.addEventListener('click', () => {
    audio.muted = !audio.muted;
  });
  btnVolUp?.addEventListener('click', () => {
    audio.volume = Math.min(1, audio.volume + 0.1);
  });
  btnVolDown?.addEventListener('click', () => {
    audio.volume = Math.max(0, audio.volume - 0.1);
  });
}

export function initRadioPlayer(config: RadioPlayerConfig): void {
  const ids = { ...DEFAULT_IDS, ...config.ids };
  const intervaloRefresco = config.intervaloRefresco ?? 15 * 60 * 1000;
  const intervaloError = config.intervaloError ?? 10 * 60 * 1000;

  // Todas las búsquedas de elementos ocurren aquí dentro, nunca a nivel
  // superior del módulo, para poder importar varios reproductores a la vez
  // sin que se rompan entre sí en páginas donde solo hay uno de ellos.
  const audio = getEl<HTMLAudioElement>(ids.audio);
  const elPrograma = getEl<HTMLDivElement>(ids.programa);
  const elDescripcion = getEl<HTMLDivElement>(ids.descripcion);
  const elHorarios = getEl<HTMLDivElement>(ids.horarios);
  const btnActualizar = getEl<HTMLButtonElement>(ids.btnActualizar);

  let timeoutId: ReturnType<typeof setTimeout> | undefined;

  async function actualizarPrograma(): Promise<void> {
    if (!config.programaApiUrl || !config.parsePrograma) {
      // Emisora sin fuente fiable de "programa en emisión": solo texto fijo.
      elPrograma.innerHTML = `<strong>${config.logoAlt}</strong>`;
      elDescripcion.innerText = 'En directo';
      elHorarios.innerText = '';
      return;
    }

    try {
      const response = await fetch(config.programaApiUrl);
      const raw = await response.json();
      const info = config.parsePrograma(raw);

      elPrograma.innerHTML = `<strong>${info.titulo}</strong>`;
      elDescripcion.innerHTML = info.descripcion;

      if (info.inicio && info.fin) {
        const formatHHMM = (d: Date): string => d.toTimeString().slice(0, 5);

        elHorarios.innerText = `Horario: ${formatHHMM(info.inicio)} - ${formatHHMM(info.fin)}`;

        // Programar próxima actualización justo al acabar el programa
        const ahora = new Date();
        const msHastaFin = info.fin.getTime() - ahora.getTime();

        clearTimeout(timeoutId);

        if (msHastaFin > 0) {
          timeoutId = setTimeout(actualizarPrograma, msHastaFin + 2000);
        } else {
          timeoutId = setTimeout(actualizarPrograma, 2 * 60 * 1000);
        }
      } else {
        elHorarios.innerText = '';
        clearTimeout(timeoutId);
      }
    } catch (error) {
      console.error('Error al obtener datos del programa:', error);
      elPrograma.innerText = 'Error al cargar programa';
      elDescripcion.innerText = '';
      elHorarios.innerText = '';
      timeoutId = setTimeout(actualizarPrograma, intervaloError);
    }
  }

  initLogo(ids.logo, config.logoUrl, config.logoAlt, config.logoClass ?? 'logo-radio');
  initStream(audio, config.streamUrl, config.useHls ?? false);
  (config.initControls ?? initControlesPorDefecto)(audio);

  btnActualizar.addEventListener('click', () => {
    actualizarPrograma();
  });

  actualizarPrograma();

  // Actualización extra periódica para mantener datos frescos
  setInterval(() => {
    console.log('Actualización periódica cada 15 minutos');
    actualizarPrograma();
  }, intervaloRefresco);
}
