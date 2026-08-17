import { getPageType } from '../../utils/urlPath';
import { formFacultatiu } from './formFacultatiu';
import { formMedicament } from './formMedicament';
import { formPatologia } from './formPatologia';
import { taulaLlistatFacultatius } from './llistatFacultatius';
import { taulaLlistatMedicaments } from './llistatMedicaments';
import { taulaLlistatPatologies } from './llistatPatologies';

const url = window.location.href;
const pageType = getPageType(url);

export function salut() {
  const id = pageType[3];

  if (pageType[2] === 'llistat-patologies') {
    taulaLlistatPatologies();
  } else if (pageType[2] === 'nou-facultatiu') {
    formFacultatiu(false);
  } else if (pageType[2] === 'modifica-facultatiu') {
    formFacultatiu(true, id);
  } else if (pageType[2] === 'llistat-facultatius') {
    taulaLlistatFacultatius();
  } else if (pageType[2] === 'nou-medicament') {
    formMedicament(false);
  } else if (pageType[2] === 'modifica-medicament') {
    formMedicament(true, id);
  } else if (pageType[2] === 'llistat-medicaments') {
    taulaLlistatMedicaments();
  } else if (pageType[2] === 'nova-patologia') {
    formPatologia(false);
  } else if (pageType[2] === 'modifica-patologia') {
    formPatologia(true, id);
  }
}
