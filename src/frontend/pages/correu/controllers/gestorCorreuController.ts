import { obtenirCarpetes, obtenirMissatges, obtenirMissatge, descarregarAdjunt, obtenirRecursInline, marcarMissatgeLlegit, enviarMissatge, guardarEsborrany, enviarEsborrany, eliminarEsborrany, eliminarMissatge, eliminarMissatgeDefinitivament, moureMissatge } from '../services/correuApi';
import { crearOpcionsResposta, crearOpcionsRespostaTots, crearOpcionsReenviament } from '../services/conversacioCorreu';
import { renderitzarCarpetes } from '../components/carpetes/carpetes';
import { renderitzarMissatges } from '../components/missatges/llistaMissatges';
import { renderitzarLectura } from '../components/missatges/lecturaMissatge';
import { crearFormulariRedaccio } from '../components/redaccio/formulariRedaccio';

import type { ElementsGestorCorreu } from '../components/layout/interficieCorreu';
import type { CarpetaCorreu, EsborranyPerGuardar, IdentificadorMissatgeCorreu, MissatgeDetall, MissatgeLlista } from '../types/Correu';
import type { OpcionsRedaccio } from '../types/Redaccio';
import { mostrarEstat } from '../utils/dom';

type VistaMobil = 'carpetes' | 'missatges' | 'lectura';

export async function inicialitzarGestorCorreuController(elements: ElementsGestorCorreu): Promise<void> {
  let carpetaActual = 'INBOX';
  let tipusCarpetaActual = 'inbox';

  let missatgesActuals: MissatgeLlista[] = [];
  let missatgeSeleccionat: number | null = null;
  let redaccioActiva = false;

  const limitPaginacio = 50;
  let offsetActual = 0;
  let teMes = false;

  let vistaMobilActual: VistaMobil = 'missatges';
  let carpetesActuals: CarpetaCorreu[] = [];
  let urlsRecursosInline: string[] = [];

  function alliberarRecursosInline(): void {
    for (const url of urlsRecursosInline) {
      URL.revokeObjectURL(url);
    }

    urlsRecursosInline = [];
  }

  async function carregarRecursosInline(missatge: MissatgeDetall): Promise<Map<string, string>> {
    const recursos = new Map<string, string>();

    alliberarRecursosInline();

    for (const recurs of missatge.recursos_inline) {
      if (!recurs.content_id) {
        continue;
      }

      const blob = await obtenirRecursInline(
        missatge.carpeta,
        {
          uid: missatge.id.uid,
          uid_validity: missatge.id.uid_validity,
        },
        recurs.identificador
      );

      const url = URL.createObjectURL(blob);

      urlsRecursosInline.push(url);

      const contentId = recurs.content_id.replace(/^<|>$/g, '').trim().toLowerCase();

      recursos.set(contentId, url);
    }

    return recursos;
  }

  /**
   * Canvia la vista visible en mòbil.
   * En escriptori les tres columnes continuen visibles.
   */
  function canviarVistaMobil(vista: VistaMobil): void {
    vistaMobilActual = vista;

    elements.gestor.classList.remove('vista-carpetes', 'vista-missatges', 'vista-lectura');

    elements.gestor.classList.add(`vista-${vista}`);
  }

  function actualitzarBotoRedaccio(): void {
    elements.botoRedactar.textContent = redaccioActiva ? 'Tancar redacció' : 'Redactar';

    elements.botoRedactar.classList.toggle('btn-outline-secondary', redaccioActiva);

    elements.botoRedactar.classList.toggle('btn-outline-primary', !redaccioActiva);
  }

  function actualitzarLlistaMissatges(): void {
    renderitzarMissatges(elements.contenidorMissatges, missatgesActuals, obrirMissatge, elements.campCerca.value, missatgeSeleccionat);
  }

  async function actualitzarLlistaSenseTancarLectura(): Promise<void> {
    let resultat = await obtenirMissatges(carpetaActual, limitPaginacio, offsetActual);

    // Si la pàgina queda buida, retrocedim a la pàgina anterior.
    if (resultat.missatges.length === 0 && offsetActual > 0) {
      offsetActual -= limitPaginacio;

      resultat = await obtenirMissatges(carpetaActual, limitPaginacio, offsetActual);
    }

    missatgesActuals = resultat.missatges;
    teMes = resultat.meta.te_mes;

    actualitzarLlistaMissatges();
    actualitzarPaginacio();
  }

  function actualitzarPaginacio(): void {
    const inici = missatgesActuals.length > 0 ? offsetActual + 1 : 0;

    const final = offsetActual + missatgesActuals.length;

    elements.indicadorPaginacio.textContent = `Mensajes ${inici}–${final}`;

    elements.botoPaginaAnterior.disabled = offsetActual === 0;
    elements.botoPaginaSeguent.disabled = !teMes;
  }

  async function guardarEsborranyDesDeForm(esborrany: EsborranyPerGuardar): Promise<IdentificadorMissatgeCorreu> {
    const id = await guardarEsborrany(esborrany);

    // El backend pot haver generat un UID nou en actualitzar el borrador.
    missatgeSeleccionat = id.uid;

    await actualitzarLlistaSenseTancarLectura();

    return id;
  }

  function obrirRedaccio(opcions: OpcionsRedaccio = {}): void {
    alliberarRecursosInline();
    redaccioActiva = true;
    actualitzarBotoRedaccio();

    elements.contenidorLectura.replaceChildren(crearFormulariRedaccio(enviarMissatge, opcions, guardarEsborranyDesDeForm, enviarEsborrany));

    canviarVistaMobil('lectura');
  }

  function iniciarResposta(missatge: MissatgeDetall): void {
    obrirRedaccio(crearOpcionsResposta(missatge));
  }

  function iniciarRespostaTots(missatge: MissatgeDetall): void {
    obrirRedaccio(crearOpcionsRespostaTots(missatge));
  }

  function iniciarReenviament(missatge: MissatgeDetall): void {
    obrirRedaccio(crearOpcionsReenviament(missatge));
  }

  async function descarregarFitxerAdjunt(missatge: MissatgeDetall, identificadorAdjunt: string): Promise<void> {
    try {
      const adjunt = missatge.adjunts.find((item) => item.identificador === identificadorAdjunt);

      if (!adjunt) {
        throw new Error('No se ha encontrado el archivo adjunto.');
      }

      const blob = await descarregarAdjunt(
        missatge.carpeta,
        {
          uid: missatge.id.uid,
          uid_validity: missatge.id.uid_validity,
        },
        identificadorAdjunt
      );

      const url = URL.createObjectURL(blob);
      const enlace = document.createElement('a');

      enlace.href = url;
      enlace.download = adjunt.nom_fitxer;

      document.body.appendChild(enlace);
      enlace.click();
      enlace.remove();

      window.setTimeout(() => {
        URL.revokeObjectURL(url);
      }, 1000);
    } catch (error: unknown) {
      const mensaje = error instanceof Error ? error.message : 'No se ha podido descargar el archivo adjunto.';

      mostrarEstat(elements.contenidorLectura, mensaje);
    }
  }

  function editarEsborrany(missatge: MissatgeDetall): void {
    obrirRedaccio({
      idEsborrany: {
        uid: missatge.id.uid,
        uid_validity: missatge.id.uid_validity,
      },
      destinataris: missatge.destinataris,
      copia: missatge.copia,
      copiaOculta: missatge.copia_oculta,
      assumpte: missatge.assumpte,
      contingutInicialText: missatge.contingut_text,
      contingutInicialHtml: missatge.contingut_html,
    });
  }

  async function esborrarEsborrany(missatge: MissatgeDetall): Promise<void> {
    const confirmacio = window.confirm('¿Seguro que quieres eliminar este borrador?');

    if (!confirmacio) {
      return;
    }

    try {
      await eliminarEsborrany({
        uid: missatge.id.uid,
        uid_validity: missatge.id.uid_validity,
      });

      redaccioActiva = false;
      actualitzarBotoRedaccio();

      missatgeSeleccionat = null;

      await actualitzarLlistaSenseTancarLectura();

      mostrarEstat(elements.contenidorLectura, 'Borrador eliminado correctamente.');

      canviarVistaMobil('missatges');
    } catch (error: unknown) {
      const missatgeError = error instanceof Error ? error.message : 'No se ha podido eliminar el borrador.';

      mostrarEstat(elements.contenidorLectura, missatgeError);
    }
  }

  async function esborrarMissatge(missatge: MissatgeDetall): Promise<void> {
    const confirmacio = window.confirm('¿Seguro que quieres mover este mensaje a la papelera?');

    if (!confirmacio) {
      return;
    }

    try {
      await eliminarMissatge(carpetaActual, {
        uid: missatge.id.uid,
        uid_validity: missatge.id.uid_validity,
      });

      missatgeSeleccionat = null;

      await actualitzarLlistaSenseTancarLectura();

      mostrarEstat(elements.contenidorLectura, 'Mensaje movido a la papelera correctamente.');

      canviarVistaMobil('missatges');
    } catch (error: unknown) {
      const missatgeError = error instanceof Error ? error.message : 'No se ha podido mover el mensaje a la papelera.';

      mostrarEstat(elements.contenidorLectura, missatgeError);
    }
  }

  /**
   * Elimina definitivamente un mensaje de la papelera.
   */
  async function esborrarMissatgeDefinitivament(missatge: MissatgeDetall): Promise<void> {
    const confirmacio = window.confirm('¿Seguro que quieres eliminar definitivamente este mensaje? Esta acción no se puede deshacer.');

    if (!confirmacio) {
      return;
    }

    try {
      await eliminarMissatgeDefinitivament(carpetaActual, {
        uid: missatge.id.uid,
        uid_validity: missatge.id.uid_validity,
      });

      missatgeSeleccionat = null;

      await actualitzarLlistaSenseTancarLectura();

      mostrarEstat(elements.contenidorLectura, 'Mensaje eliminado definitivamente.');

      canviarVistaMobil('missatges');
    } catch (error: unknown) {
      const missatgeError = error instanceof Error ? error.message : 'No se ha podido eliminar definitivamente el mensaje.';

      mostrarEstat(elements.contenidorLectura, missatgeError);
    }
  }

  async function moureMissatgeACarpeta(missatge: MissatgeDetall, carpetaDesti: string): Promise<void> {
    const carpeta = carpetesActuals.find((item) => item.ruta === carpetaDesti);

    if (!carpeta) {
      mostrarEstat(elements.contenidorLectura, 'No se ha encontrado la carpeta de destino.');

      return;
    }

    const confirmacio = window.confirm(`¿Quieres mover este mensaje a "${carpeta.nom_visual}"?`);

    if (!confirmacio) {
      return;
    }

    try {
      await moureMissatge(missatge.carpeta, carpetaDesti, {
        uid: missatge.id.uid,
        uid_validity: missatge.id.uid_validity,
      });

      missatgeSeleccionat = null;

      // Actualizamos los contadores de las carpetas.
      await carregarCarpetes();

      // Actualizamos el listado de la carpeta de origen.
      await actualitzarLlistaSenseTancarLectura();

      mostrarEstat(elements.contenidorLectura, `Mensaje movido a "${carpeta.nom_visual}" correctamente.`);

      canviarVistaMobil('missatges');
    } catch (error: unknown) {
      const missatgeError = error instanceof Error ? error.message : 'No se ha podido mover el mensaje.';

      mostrarEstat(elements.contenidorLectura, missatgeError);
    }
  }

  function tancarRedaccio(): void {
    redaccioActiva = false;
    actualitzarBotoRedaccio();

    const missatgeAnterior = missatgesActuals.find((missatge) => missatge.id === missatgeSeleccionat);

    if (missatgeAnterior) {
      void obrirMissatge(missatgeAnterior);
      return;
    }

    mostrarEstat(elements.contenidorLectura, 'Selecciona un missatge per llegir-lo.');

    canviarVistaMobil('missatges');
  }

  async function carregarMissatges(carpeta: string): Promise<void> {
    redaccioActiva = false;
    actualitzarBotoRedaccio();
    alliberarRecursosInline();

    carpetaActual = carpeta;
    missatgesActuals = [];
    missatgeSeleccionat = null;

    elements.titolCarpeta.textContent = carpeta;

    mostrarEstat(elements.contenidorMissatges, 'Carregant missatges...');

    mostrarEstat(elements.contenidorLectura, 'Selecciona un missatge per llegir-lo.');

    try {
      const resultat = await obtenirMissatges(carpeta, limitPaginacio, offsetActual);

      missatgesActuals = resultat.missatges;
      teMes = resultat.meta.te_mes;

      actualitzarLlistaMissatges();
      actualitzarPaginacio();
    } catch (error: unknown) {
      const missatge = error instanceof Error ? error.message : 'No s’han pogut carregar els missatges.';

      mostrarEstat(elements.contenidorMissatges, missatge);
    }
  }

  async function obrirMissatge(missatge: MissatgeLlista): Promise<void> {
    redaccioActiva = false;
    actualitzarBotoRedaccio();

    missatgeSeleccionat = missatge.id;

    canviarVistaMobil('lectura');

    mostrarEstat(elements.contenidorLectura, 'Carregant missatge...');

    try {
      const detall: MissatgeDetall = await obtenirMissatge(carpetaActual, missatge);
      const recursosInline = await carregarRecursosInline(detall);

      if (tipusCarpetaActual === 'drafts') {
        renderitzarLectura(
          elements.contenidorLectura,
          detall,
          {
            editarEsborrany,
            eliminarEsborrany: (missatge) => {
              void esborrarEsborrany(missatge);
            },
            descarregarAdjunt: (missatge, identificador) => {
              void descarregarFitxerAdjunt(missatge, identificador);
            },
          },
          recursosInline
        );
      } else {
        renderitzarLectura(
          elements.contenidorLectura,
          detall,
          {
            respondre: iniciarResposta,
            respondreTots: iniciarRespostaTots,
            reenviar: iniciarReenviament,

            eliminarMissatge:
              tipusCarpetaActual !== 'trash'
                ? (missatge) => {
                    void esborrarMissatge(missatge);
                  }
                : undefined,

            eliminarMissatgeDefinitivament:
              tipusCarpetaActual === 'trash'
                ? (missatge) => {
                    void esborrarMissatgeDefinitivament(missatge);
                  }
                : undefined,

            moureMissatge: (missatge, carpetaDesti) => {
              void moureMissatgeACarpeta(missatge, carpetaDesti);
            },

            carpetes: carpetesActuals,

            descarregarAdjunt: (missatge, identificador) => {
              void descarregarFitxerAdjunt(missatge, identificador);
            },
          },
          recursosInline
        );
      }

      if (!missatge.llegit) {
        await marcarMissatgeLlegit(carpetaActual, missatge);

        missatge.llegit = true;
        actualitzarLlistaMissatges();
      }
    } catch (error: unknown) {
      const missatgeError = error instanceof Error ? error.message : 'No s’ha pogut obrir el missatge.';

      mostrarEstat(elements.contenidorLectura, missatgeError);
    }
  }

  async function carregarCarpetes(): Promise<void> {
    mostrarEstat(elements.contenidorCarpetes, 'Carregant carpetes...');

    try {
      const carpetes: CarpetaCorreu[] = await obtenirCarpetes();

      carpetesActuals = carpetes;

      renderitzarCarpetes(
        elements.contenidorCarpetes,
        carpetes,
        (carpeta) => {
          tipusCarpetaActual = carpeta.tipus;
          offsetActual = 0;

          canviarVistaMobil('missatges');

          void carregarMissatges(carpeta.ruta);
        },
        carpetaActual
      );
    } catch (error: unknown) {
      const missatge = error instanceof Error ? error.message : 'No s’han pogut carregar les carpetes.';

      mostrarEstat(elements.contenidorCarpetes, missatge);
    }
  }

  async function actualitzarGestorCorreu(): Promise<void> {
    elements.botoActualitzar.disabled = true;

    try {
      await carregarCarpetes();
      await actualitzarLlistaSenseTancarLectura();
    } finally {
      elements.botoActualitzar.disabled = false;
    }
  }

  function registrarEsdeveniments(): void {
    elements.campCerca.addEventListener('input', () => {
      actualitzarLlistaMissatges();
    });

    elements.botoActualitzar.addEventListener('click', () => {
      void actualitzarGestorCorreu();
    });

    elements.botoRedactar.addEventListener('click', () => {
      if (redaccioActiva) {
        tancarRedaccio();
      } else {
        obrirRedaccio();
      }
    });

    // Navegació mòbil: obrir carpetes.
    elements.botoCarpetes.addEventListener('click', () => {
      canviarVistaMobil('carpetes');
    });

    // Navegació mòbil: tornar de carpetes al llistat.
    elements.botoTornarDesDeCarpetes.addEventListener('click', () => {
      canviarVistaMobil('missatges');
    });

    // Navegació mòbil: tornar del correu obert al llistat.
    elements.botoTornarMissatges.addEventListener('click', () => {
      canviarVistaMobil('missatges');
    });

    elements.botoPaginaAnterior.addEventListener('click', () => {
      if (offsetActual >= limitPaginacio) {
        offsetActual -= limitPaginacio;
        void carregarMissatges(carpetaActual);
      }
    });

    elements.botoPaginaSeguent.addEventListener('click', () => {
      if (teMes) {
        offsetActual += limitPaginacio;
        void carregarMissatges(carpetaActual);
      }
    });
  }

  registrarEsdeveniments();

  canviarVistaMobil(vistaMobilActual);

  await carregarCarpetes();
  await carregarMissatges(carpetaActual);
}
