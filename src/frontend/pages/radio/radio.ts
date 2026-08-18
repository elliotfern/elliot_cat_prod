import { getPageType } from '../../utils/urlPath';
import { initBbc4Player } from './bbc4';
import { initBbc6Player } from './bbc6';
import { initCatalunyaInformacioPlayer } from './catalunyaInformacio';
import { initCatalunyaMusicaPlayer } from './catMusica';
import { initFranceCulturePlayer } from './franceculture';
import { initFranceInterPlayer } from './franceinter';
import { initFranceMusiquePlayer } from './franceMusique';
import { initIcatFmPlayer } from './icatfm';
import { initRadioMunicipalTerrassaPlayer } from './radioMunicipalTerrassa';
import { initRaiRadio3Player } from './raiRadio3';

const url = window.location.href;
const pageType = getPageType(url);

export function radio() {
  if (pageType[2] === 'rai-radio-3') {
    initRaiRadio3Player();
  } else if (pageType[2] === 'catalunya-musica') {
    initCatalunyaMusicaPlayer();
  } else if (pageType[2] === 'icatfm') {
    initIcatFmPlayer();
  } else if (pageType[2] === 'catalunya-informacio') {
    initCatalunyaInformacioPlayer();
  } else if (pageType[2] === 'bbc-4') {
    initBbc4Player();
  } else if (pageType[2] === 'bbc-6') {
    initBbc6Player();
  } else if (pageType[2] === 'france-culture') {
    initFranceCulturePlayer();
  } else if (pageType[2] === 'france-inter') {
    initFranceInterPlayer();
  } else if (pageType[2] === 'france-musique') {
    initFranceMusiquePlayer();
  } else if (pageType[2] === 'radio-municipal-terrassa') {
    initRadioMunicipalTerrassaPlayer();
  } else if (pageType[1] === 'radio') {
    //
  }
}
