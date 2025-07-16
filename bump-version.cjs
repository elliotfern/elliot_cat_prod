const fs = require('fs');
const filePath = 'public/includes/footer-end.php';

// Leer contenido del archivo
let content = fs.readFileSync(filePath, 'utf-8');

// Buscar la versión actual con regex y aumentar el último número
content = content.replace(/bundle\.js\?v=(\d+)\.(\d+)\.(\d+)/, (_, major, minor, patch) => {
  const newPatch = parseInt(patch) + 1;
  return `bundle.js?v=${major}.${minor}.${newPatch}`;
});

// Escribir contenido modificado de nuevo
fs.writeFileSync(filePath, content);

console.log('✅ footer-end.php actualizado con nueva versión');
