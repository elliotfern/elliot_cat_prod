import { formatData } from '../../utils/formataData';
import { Esdeveniment } from '../../types/Esdeveniment';

// -------------------------
// API → VIEW
// -------------------------
export function mapEsdeveniment(api: Esdeveniment): Esdeveniment {
  return {
    ...api,
    id: api.id ?? '',
    slug: api.slug ?? '',
    esdeNom: api.esdeNom ?? '',
    ciutat: api.ciutat ?? '',
    pais: api.pais ?? '',
    etapaNom: api.etapaNom ?? '',
    nomSubEtapa: api.nomSubEtapa ?? '',
    esdeDataIDia: api.esdeDataIDia ?? 0,
    esdeDataIMes: api.esdeDataIMes ?? 0,
    esdeDataIAny: api.esdeDataIAny ?? 0,
    esdeDataFDia: api.esdeDataFDia ?? 0,
    esdeDataFMes: api.esdeDataFMes ?? 0,
    esdeDataFAny: api.esdeDataFAny ?? 0,
    ciutat_id: api.ciutat_id ?? '',
    sub_etapa_id: api.sub_etapa_id ?? '',
    imatge_id: api.imatge_id ?? '',
    nameImg: api.nameImg ?? null,
    alt: api.alt ?? null,
    descripcio: api.descripcio ?? '',
    dateCreated: api.dateCreated ?? '',
    dateModified: api.dateModified ?? '',
    persones: Array.isArray(api.persones) ? api.persones : api.persones ? [api.persones] : [],
    organitzacions: Array.isArray(api.organitzacions) ? api.organitzacions : api.organitzacions ? [api.organitzacions] : [],
  };
}

// -------------------------
// VIEW → FITXA UI
// -------------------------
export function mapEsdevenimentToFitxa(esdeveniment: Esdeveniment) {
  const formatDataEsdeveniment = (dia: number, mes: number, any: number): string => {
    if (!any) {
      return '';
    }

    if (!dia && !mes) {
      return String(any);
    }

    if (!dia || !mes) {
      return [dia || '', mes || '', any].filter(Boolean).join('/');
    }

    return formatData(`${any}-${String(mes).padStart(2, '0')}-${String(dia).padStart(2, '0')}`);
  };

  const dataInici = formatDataEsdeveniment(esdeveniment.esdeDataIDia, esdeveniment.esdeDataIMes, esdeveniment.esdeDataIAny);

  const dataFinal = formatDataEsdeveniment(esdeveniment.esdeDataFDia, esdeveniment.esdeDataFMes, esdeveniment.esdeDataFAny);

  const periode = dataInici && dataFinal && dataInici !== dataFinal ? `${dataInici} – ${dataFinal}` : dataInici || dataFinal;

  const fields = [
    {
      label: 'Data',
      value: periode,
    },
    {
      label: 'Ciutat',
      value: esdeveniment.ciutat ?? '',
    },
    {
      label: 'País',
      value: esdeveniment.pais ?? '',
    },
    {
      label: 'Etapa històrica',
      value: esdeveniment.etapaNom ?? '',
    },
    {
      label: 'Període',
      value: esdeveniment.nomSubEtapa ?? '',
    },
  ].filter((field) => field.value !== '');

  return {
    title: esdeveniment.esdeNom,
    fields,
    description: esdeveniment.descripcio ?? '',
    dateCreated: '',
    dateModified: '',
    image: esdeveniment.nameImg
      ? {
          src: `https://media.elliot.cat/img/historia-esdeveniment/${esdeveniment.nameImg}.jpg`,
          alt: esdeveniment.alt || esdeveniment.esdeNom,
        }
      : undefined,
  };
}
