import { getPageType } from '../../utils/urlPath';
import { serveisVaultApi } from '../../components/vault/serveisVault';
import { formVault } from './formVault';

const url = window.location.href;
const pageType = getPageType(url);

export function vault() {
  if (pageType[2] === 'modifica-vault') {
    const id = pageType[3];
    formVault(true, Number(id));
  } else if (pageType[2] === 'nou-vault') {
    formVault(false);
  } else if (pageType[1] === 'claus-privades') {
    serveisVaultApi();
  }
}
