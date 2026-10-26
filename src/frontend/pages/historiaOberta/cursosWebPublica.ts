import { api } from '../../Infrastructure/Api/Client/ApiClient';

type TipusRecurs = 'curs' | 'banc_dades' | 'cronologia' | 'mapa' | 'recurs_digital';

interface Curs {
  curs: string;
  img: string;
  descripcio?: string;
  resum?: string;
  slug: string;
  extension: string;
  nameImg: string;
  tipus: TipusRecurs;
}

interface ConfiguracioTipus {
  titolSeccio: string;
  etiqueta: string;
  prefixUrl: string;
  color: string;
  boto: string;
}

const configuracioTipus: Record<TipusRecurs, ConfiguracioTipus> = {
  curs: {
    titolSeccio: 'Cursos',
    etiqueta: 'Curs',
    prefixUrl: '/historia/curs',
    color: '#133b7c',
    boto: 'Veure continguts',
  },
  banc_dades: {
    titolSeccio: 'Bancs de dades',
    etiqueta: 'Banc de dades',
    prefixUrl: '/historia/banc-dades',
    color: '#237a57',
    boto: 'Accedir al recurs',
  },
  cronologia: {
    titolSeccio: 'Cronologies',
    etiqueta: 'Cronologia',
    prefixUrl: '/historia/cronologia',
    color: '#a85d19',
    boto: 'Accedir al recurs',
  },
  mapa: {
    titolSeccio: 'Mapes',
    etiqueta: 'Mapa',
    prefixUrl: '/historia/mapa',
    color: '#6550a4',
    boto: 'Accedir al recurs',
  },
  recurs_digital: {
    titolSeccio: 'Recursos digitals',
    etiqueta: 'Recurs digital',
    prefixUrl: '/historia/recurs-digital',
    color: '#52616b',
    boto: 'Accedir al recurs',
  },
};

const ordreTipus: TipusRecurs[] = ['curs', 'banc_dades', 'cronologia', 'mapa', 'recurs_digital'];

const URL_IMATGES = 'https://media.elliot.cat/img/historia-curs';

export async function getCoursesList(): Promise<void> {
  try {
    const courses = await api.get<Curs[]>('historia/get/llistatCursos');

    displayCourses(courses);
  } catch (error: unknown) {
    console.error('Error fetching data:', error);
  }
}

function displayCourses(courses: Curs[]): void {
  const coursesListContainer = document.getElementById('coursesList');

  if (!coursesListContainer) {
    return;
  }

  coursesListContainer.replaceChildren();

  ordreTipus.forEach((tipus) => {
    const recursos = courses.filter((recurs) => recurs.tipus === tipus);

    if (recursos.length === 0) {
      return;
    }

    const configuracio = configuracioTipus[tipus];

    const section = document.createElement('section');
    section.classList.add('mb-5');

    const heading = document.createElement('h2');
    heading.classList.add('h3', 'mb-4');
    heading.textContent = configuracio.titolSeccio;

    const row = document.createElement('div');
    row.classList.add('row', 'g-4');

    recursos.forEach((recurs) => {
      row.appendChild(createResourceCard(recurs, configuracio));
    });

    section.append(heading, row);
    coursesListContainer.appendChild(section);
  });
}

function createResourceCard(recurs: Curs, configuracio: ConfiguracioTipus): HTMLDivElement {
  const column = document.createElement('div');
  column.classList.add('col-12', 'col-md-6', 'col-lg-3');

  const url = `${configuracio.prefixUrl}/` + encodeURIComponent(recurs.slug);

  const card = document.createElement('div');
  card.classList.add('card', 'h-100', 'overflow-hidden');

  // Franja de color identificativa de la tipologia.
  const franja = document.createElement('div');
  franja.style.height = '5px';
  franja.style.backgroundColor = configuracio.color;

  // Caràtula del recurs.
  const imageLink = document.createElement('a');
  imageLink.href = url;

  const image = document.createElement('img');
  image.src = `${URL_IMATGES}/${encodeURIComponent(recurs.nameImg)}.` + encodeURIComponent(recurs.extension);
  image.classList.add('card-img-top', 'img-fluid');
  image.alt = recurs.curs;
  image.loading = 'lazy';

  imageLink.appendChild(image);

  // Contingut de la targeta.
  const cardBody = document.createElement('div');
  cardBody.classList.add('card-body', 'text-center', 'd-flex', 'flex-column');

  const title = document.createElement('h3');
  title.classList.add('h5', 'card-title');

  const titleLink = document.createElement('a');
  titleLink.href = url;
  titleLink.classList.add('text-decoration-none', 'text-reset');
  titleLink.textContent = recurs.curs;

  title.appendChild(titleLink);

  // Etiqueta amb el tipus de recurs.
  const etiqueta = document.createElement('span');
  etiqueta.classList.add('badge', 'align-self-center', 'mb-2');
  etiqueta.style.backgroundColor = configuracio.color;
  etiqueta.textContent = configuracio.etiqueta;

  const summary = document.createElement('p');
  summary.classList.add('card-text');
  summary.textContent = recurs.resum ?? '';

  // Botó d'accés.
  const button = document.createElement('a');
  button.href = url;
  button.classList.add('btn', 'btn-dark', 'mt-auto');
  button.textContent = configuracio.boto;

  cardBody.append(title, etiqueta, summary, button);
  card.append(franja, imageLink, cardBody);
  column.appendChild(card);

  return column;
}
