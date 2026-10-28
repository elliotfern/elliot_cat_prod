import { formatData } from '../../utils/formataData';
import { Organitzacio } from '../../types/Organitzacio';

// -------------------------
// API → VIEW
// -------------------------
export function mapOrganitzacio(api: Organitzacio): Organitzacio {
  return {
    ...api,
    organitzacio: String(api.organitzacio ?? ''),
    sigla: api.sigla ?? '',
    slug: api.slug ?? '',
    tipus: api.tipus ?? '',
    any_fundacio: api.any_fundacio ?? '',
    any_dissolucio: api.any_dissolucio ?? '',
    ideologia_id: api.ideologia_id ?? '',
    pais_id: api.pais_id ?? '',
    ciutat_id: api.ciutat_id ?? '',
    sub_etapa_id: api.sub_etapa_id ?? '',
    imatge_id: api.imatge_id ?? '',
    dateCreated: api.dateCreated ?? '',
    dateModified: api.dateModified ?? '',
    etapaNom: api.etapaNom ?? '',
    nomSubEtapa: api.nomSubEtapa ?? '',
    ideologia: api.ideologia ?? '',
    pais: api.pais ?? '',
    ciutat: api.ciutat ?? '',
    alt: api.alt ?? '',
    nameImg: api.nameImg ?? '',
  };
}

// -------------------------
// VIEW → FITXA UI
// -------------------------
export function mapOrganitzacioToFitxa(organitzacio: Organitzacio) {
  const anys = [organitzacio.any_fundacio && organitzacio.any_fundacio !== '0' ? organitzacio.any_fundacio : '', organitzacio.any_dissolucio && organitzacio.any_dissolucio !== '0' ? organitzacio.any_dissolucio : ''].filter(Boolean);

  const periode = anys.join(' – ');

  const fields = [
    {
      label: 'Nom',
      value: organitzacio.organitzacio,
    },
    {
      label: 'Sigla',
      value: organitzacio.sigla,
    },
    {
      label: 'Període',
      value: periode,
    },
    {
      label: 'Tipus',
      value: organitzacio.tipus,
    },
    {
      label: 'País',
      value: organitzacio.pais,
    },
    {
      label: 'Ciutat',
      value: organitzacio.ciutat,
    },
    {
      label: 'Etapa històrica',
      value: organitzacio.etapaNom,
    },
    {
      label: 'Període',
      value: organitzacio.nomSubEtapa,
    },
    {
      label: 'Ideologia',
      value: organitzacio.ideologia,
    },
  ].filter((field) => field.value !== '');

  return {
    title: organitzacio.organitzacio,

    image: organitzacio.nameImg
      ? {
          src: `https://media.elliot.cat/img/historia-organitzacio/${organitzacio.nameImg}.jpg`,
          alt: organitzacio.alt || organitzacio.organitzacio,
        }
      : undefined,

    fields,
    description: '',
    dateCreated: organitzacio.dateCreated ? formatData(organitzacio.dateCreated) : '',
    dateModified: organitzacio.dateModified ? formatData(organitzacio.dateModified) : '',
  };
}
