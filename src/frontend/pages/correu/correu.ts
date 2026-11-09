import { crearInterficie } from './components/layout/interficieCorreu';
import { inicialitzarGestorCorreuController } from './controllers/gestorCorreuController';

export async function inicialitzarGestorCorreu(): Promise<void> {
  const contenidor = document.getElementById('gestorCorreu');

  if (!contenidor) {
    console.error('No s’ha trobat el contenidor #gestorCorreu.');
    return;
  }

  const elements = crearInterficie(contenidor);

  await inicialitzarGestorCorreuController(elements);
}
