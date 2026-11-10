import { obtenirCarpetes, obtenirMissatges, obtenirMissatge, marcarMissatgeLlegit, enviarMissatge, guardarEsborrany, enviarEsborrany, eliminarEsborrany } from '../services/correuApi';

import { crearOpcionsResposta, crearOpcionsRespostaTots, crearOpcionsReenviament } from '../services/conversacioCorreu';

import { renderitzarCarpetes } from '../components/carpetes/carpetes';
import { renderitzarMissatges } from '../components/missatges/llistaMissatges';
import { renderitzarLectura } from '../components/missatges/lecturaMissatge';
import { crearFormulariRedaccio } from '../components/redaccio/formulariRedaccio';

import type { ElementsGestorCorreu } from '../components/layout/interficieCorreu';

import type { CarpetaCorreu, EsborranyPerGuardar, IdentificadorMissatgeCorreu, MissatgeDetall, MissatgeLlista } from '../types/Correu';

import type { OpcionsRedaccio } from '../types/Redaccio';

import { mostrarEstat } from '../utils/dom';

export async function inicialitzarGestorCorreuController(elements: ElementsGestorCorreu): Promise<void> {
  let carpetaActual = 'INBOX';
  let tipusCarpetaActual = 'inbox';

  let missatgesActuals: MissatgeLlista[] = [];
  let missatgeSeleccionat: number | null = null;
  let redaccioActiva = false;

  function actualitzarBotoRedaccio(): void {
    elements.botoRedactar.textContent = redaccioActiva ? 'Tancar redacció' : 'Redactar';

    elements.botoRedactar.classList.toggle('btn-outline-secondary', redaccioActiva);

    elements.botoRedactar.classList.toggle('btn-outline-primary', !redaccioActiva);
  }

  function actualitzarLlistaMissatges(): void {
    renderitzarMissatges(elements.contenidorMissatges, missatgesActuals, obrirMissatge, elements.campCerca.value, missatgeSeleccionat);
  }

  async function actualitzarLlistaSenseTancarLectura(): Promise<void> {
    missatgesActuals = await obtenirMissatges(carpetaActual);
    actualitzarLlistaMissatges();
  }

  async function guardarEsborranyDesDeForm(esborrany: EsborranyPerGuardar): Promise<IdentificadorMissatgeCorreu> {
    const id = await guardarEsborrany(esborrany);

    // El backend pot haver generat un UID nou en actualitzar el borrador.
    missatgeSeleccionat = id.uid;

    await actualitzarLlistaSenseTancarLectura();

    return id;
  }

  function obrirRedaccio(opcions: OpcionsRedaccio = {}): void {
    redaccioActiva = true;
    actualitzarBotoRedaccio();

    elements.contenidorLectura.replaceChildren(crearFormulariRedaccio(enviarMissatge, opcions, guardarEsborranyDesDeForm, enviarEsborrany));
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
    } catch (error: unknown) {
      const missatgeError = error instanceof Error ? error.message : 'No se ha podido eliminar el borrador.';

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
  }

  async function carregarMissatges(carpeta: string): Promise<void> {
    redaccioActiva = false;
    actualitzarBotoRedaccio();

    carpetaActual = carpeta;
    missatgesActuals = [];
    missatgeSeleccionat = null;

    elements.titolCarpeta.textContent = carpeta;

    mostrarEstat(elements.contenidorMissatges, 'Carregant missatges...');

    mostrarEstat(elements.contenidorLectura, 'Selecciona un missatge per llegir-lo.');

    try {
      missatgesActuals = await obtenirMissatges(carpeta);

      actualitzarLlistaMissatges();
    } catch (error: unknown) {
      const missatge = error instanceof Error ? error.message : 'No s’han pogut carregar els missatges.';

      mostrarEstat(elements.contenidorMissatges, missatge);
    }
  }

  async function obrirMissatge(missatge: MissatgeLlista): Promise<void> {
    redaccioActiva = false;
    actualitzarBotoRedaccio();

    missatgeSeleccionat = missatge.id;

    mostrarEstat(elements.contenidorLectura, 'Carregant missatge...');

    try {
      const detall: MissatgeDetall = await obtenirMissatge(carpetaActual, missatge);

      if (tipusCarpetaActual === 'drafts') {
        renderitzarLectura(elements.contenidorLectura, detall, {
          editarEsborrany,
          eliminarEsborrany: (missatge) => {
            void esborrarEsborrany(missatge);
          },
        });
      } else {
        renderitzarLectura(elements.contenidorLectura, detall, {
          respondre: iniciarResposta,
          respondreTots: iniciarRespostaTots,
          reenviar: iniciarReenviament,
        });
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

      renderitzarCarpetes(
        elements.contenidorCarpetes,
        carpetes,
        (carpeta) => {
          tipusCarpetaActual = carpeta.tipus;
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
      await carregarMissatges(carpetaActual);
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
  }

  registrarEsdeveniments();

  await carregarCarpetes();
  await carregarMissatges(carpetaActual);
}
