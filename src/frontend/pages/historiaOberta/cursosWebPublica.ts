import { api } from '../../Infrastructure/Api/Client/ApiClient';

interface Curs {
  curs: string;
  img: string;
  descripcio?: string;
  resum?: string;
  slug: string;
  extension: string;
  nameImg: string;
}

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

  coursesListContainer.innerHTML = '';

  const row = document.createElement('div');
  row.classList.add('row', 'g-4');

  courses.forEach((course) => {
    const courseLink = `/historia/curs/${encodeURIComponent(course.slug)}`;

    const column = document.createElement('div');
    column.classList.add('col-12', 'col-md-6', 'col-lg-3');

    column.innerHTML = `
      <div class="card h-100">
        <a href="${courseLink}">
          <img
            src="https://media.elliot.cat/img/historia-curs/${course.nameImg}.${course.extension}"
            class="card-img-top img-fluid"
            alt="${course.curs ?? ''}"
          >
        </a>

        <div class="card-body text-center d-flex flex-column">
          <h3 class="h5 card-title">
            <a
              href="${courseLink}"
              class="text-decoration-none text-reset"
            >
              ${course.curs ?? ''}
            </a>
          </h3>

          <p class="card-text">
            ${course.resum ?? ''}
          </p>

          <a href="${courseLink}" class="btn btn-warning mt-auto">
            Veure continguts
          </a>
        </div>
      </div>
    `;

    row.appendChild(column);
  });

  coursesListContainer.appendChild(row);
}
