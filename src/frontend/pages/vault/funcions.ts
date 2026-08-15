import { getPageType } from '../../utils/urlPath';
import { serveisVaultApi } from '../../components/vault/serveisVault';
import { formVault } from './formVault';

const url = window.location.href;
const pageType = getPageType(url);

export function vault() {
  if (pageType[2] === 'modifica-vault') {
    const uuid = pageType[3];
    formVault(true, String(uuid));
  } else if (pageType[2] === 'nou-vault') {
    formVault(false);
  } else if (pageType[1] === 'claus-privades') {
    serveisVaultApi();
  }
}
