import { api } from '../../core/api/client';
import { renderCapcalera, SelectorComptabilitat, onCanviEmissor, onCanviExercici, actualizarExercicis, actualizarCapcalera } from './ResumFacturacio/capcalera';
import { getEmissor, getEmissorsComptabilitat, getExercicisDisponibles } from './ResumFacturacio/emissors';
import { EvolucioMensualResponse, renderEvolucioMensual } from './ResumFacturacio/evolucioMensual';
import { renderGraficaEvolucio } from './ResumFacturacio/graficaEvolucio';
import { getResum, renderResum } from './ResumFacturacio/resum';

export async function renderComptabilitat(): Promise<void> {
  const capcalera = document.getElementById('capcaleraComptabilitat');
  const resumContainer = document.getElementById('resumComptabilitat');
  const evolucio = document.getElementById('evolucioMensual');
  const grafica = document.getElementById('graficaEvolucio');
  const resumAnual = document.getElementById('resumAnual');

  if (!capcalera || !resumContainer || !evolucio || !grafica || !resumAnual) {
    return;
  }

  capcalera.innerHTML = '';
  resumContainer.innerHTML = '';
  evolucio.innerHTML = '';
  grafica.innerHTML = '';
  resumAnual.innerHTML = '';

  // 1. Capçalera

  const emissors = await getEmissorsComptabilitat();

  const EMISSOR_PARTITA_IVA_ITALIA = '019e3eba-f713-70c2-860a-40a7a15db129';

  const emissorSeleccionat = emissors.find((item) => item.id === EMISSOR_PARTITA_IVA_ITALIA);

  if (!emissorSeleccionat) {
    return;
  }

  const emissor = await getEmissor(EMISSOR_PARTITA_IVA_ITALIA);

  const exercicis = getExercicisDisponibles(emissorSeleccionat);

  const exerciciSeleccionat = exercicis[0];

  const selector: SelectorComptabilitat = {
    emissors,
    emissorSeleccionat: EMISSOR_PARTITA_IVA_ITALIA,
    exercicis,
    exerciciSeleccionat,
  };

  let emissorSeleccionatId = EMISSOR_PARTITA_IVA_ITALIA;

  renderCapcalera(capcalera, emissor, selector);

  onCanviEmissor(async (emissorId) => {
    emissorSeleccionatId = emissorId;

    const emissor = await getEmissor(emissorId);

    actualizarCapcalera(emissor);

    const exercicis = getExercicisDisponibles(emissor);

    const exerciciSeleccionat = exercicis[0];

    actualizarExercicis(exercicis, exerciciSeleccionat);

    const resumDades = await getResum(emissorId, exerciciSeleccionat);

    renderResum(resumContainer, resumDades);

    const evolucioDades = await getEvolucioMensual(emissorId, exerciciSeleccionat);

    renderEvolucioMensual(evolucio, evolucioDades.mesos);

    renderGraficaEvolucio(grafica, evolucioDades.mesos);
  });

  onCanviExercici(async (exercici) => {
    const resumDades = await getResum(emissorSeleccionatId, exercici);

    renderResum(resumContainer, resumDades);

    const evolucioDades = await getEvolucioMensual(emissorSeleccionatId, exercici);

    renderEvolucioMensual(evolucio, evolucioDades.mesos);

    renderGraficaEvolucio(grafica, evolucioDades.mesos);
  });

  // 2. Resum dades

  const resumDades = await getResum(emissor.id, exerciciSeleccionat);

  renderResum(resumContainer, resumDades);

  // 3. Dades mensuals

  const evolucioDades = await getEvolucioMensual(emissor.id, exerciciSeleccionat);

  renderEvolucioMensual(evolucio, evolucioDades.mesos);

  // 4. Grafica
  renderGraficaEvolucio(grafica, evolucioDades.mesos);
}

async function getEvolucioMensual(emissorId: string, exercici: number): Promise<EvolucioMensualResponse> {
  return api.get<EvolucioMensualResponse>('comptabilitat/get/evolucioMensual', {
    emissor_id: emissorId,
    exercici,
  });
}
