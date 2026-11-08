import { MissatgeLlista } from '../../types/Correu';
import { crearElement, mostrarEstat } from '../../utils/dom';
import { formatarData } from '../../utils/formatData';

export function renderitzarMissatges(contenidor: HTMLElement, missatges: MissatgeLlista[], obrir: (missatge: MissatgeLlista) => void, filtre = '', missatgeSeleccionat: number | null = null): void {
  contenidor.replaceChildren();

  contenidor.classList.remove('list-group', 'list-group-flush');
  contenidor.classList.add('d-flex', 'flex-column', 'gap-2', 'p-2');

  const terme = filtre.trim().toLocaleLowerCase();

  const filtrats = missatges.filter((missatge) => {
    return missatge.assumpte.toLocaleLowerCase().includes(terme) || missatge.remitent.email.toLocaleLowerCase().includes(terme) || missatge.remitent.nom.toLocaleLowerCase().includes(terme);
  });

  if (filtrats.length === 0) {
    mostrarEstat(contenidor, 'No hi ha missatges.');
    return;
  }

  const actualitzarEstilMissatge = (boto: HTMLButtonElement, actiu: boolean, llegit: boolean): void => {
    boto.classList.toggle('active', actiu);
    boto.classList.toggle('btn-secondary', actiu);
    boto.classList.toggle('btn-outline-secondary', !actiu);
    boto.classList.toggle('btn-light', !actiu && !llegit);
    boto.classList.toggle('border-secondary-subtle', !actiu && !llegit);

    boto.setAttribute('aria-pressed', String(actiu));

    const colorText = actiu ? 'text-white' : 'text-body';
    const colorData = actiu ? 'text-white' : 'text-body-secondary';

    const remitent = boto.querySelector('[data-role="remitent"]');
    const assumpte = boto.querySelector('[data-role="assumpte"]');
    const data = boto.querySelector('[data-role="data"]');

    remitent?.classList.remove('text-white', 'text-body');
    remitent?.classList.add(colorText);

    assumpte?.classList.remove('text-white', 'text-body');
    assumpte?.classList.add(colorText);

    data?.classList.remove('text-white', 'text-body-secondary');
    data?.classList.add(colorData);

    remitent?.classList.toggle('fw-bold', !llegit);
    assumpte?.classList.toggle('fw-semibold', !llegit);
  };

  for (const missatge of filtrats) {
    const actiu = missatge.id === missatgeSeleccionat;

    const boto = crearElement('button', 'btn btn-sm text-start w-100 py-3');

    boto.type = 'button';
    boto.dataset.missatgeId = String(missatge.id);

    const capcalera = crearElement('div', 'd-flex justify-content-between align-items-center gap-2 mb-1');

    const remitent = crearElement('span', '', missatge.remitent.nom || missatge.remitent.email);
    remitent.dataset.role = 'remitent';

    const data = crearElement('small', '', formatarData(missatge.data));
    data.dataset.role = 'data';

    capcalera.append(remitent, data);

    const assumpte = crearElement('div', '', missatge.assumpte || '(Sense assumpte)');
    assumpte.dataset.role = 'assumpte';

    boto.append(capcalera, assumpte);

    const icones = crearElement('div', 'd-flex gap-2 mt-1');

    if (missatge.te_adjunts) {
      icones.appendChild(crearElement('i', 'bi bi-paperclip'));
    }

    if (missatge.destacat) {
      icones.appendChild(crearElement('i', 'bi bi-star-fill text-warning'));
    }

    if (icones.childElementCount > 0) {
      boto.appendChild(icones);
    }

    actualitzarEstilMissatge(boto, actiu, missatge.llegit);

    boto.addEventListener('click', () => {
      contenidor.querySelectorAll<HTMLButtonElement>('[data-missatge-id]').forEach((element) => {
        const seleccionat = Number(element.dataset.missatgeId) === missatge.id;

        const missatgeElement = filtrats.find((item) => item.id === Number(element.dataset.missatgeId));

        if (missatgeElement) {
          actualitzarEstilMissatge(element, seleccionat, missatgeElement.llegit);
        }
      });

      obrir(missatge);
    });

    contenidor.appendChild(boto);
  }
}
