const fs = require('fs');

const filePath = 'public/includes/footer-end.php';
let content = fs.readFileSync(filePath, 'utf-8');

const re = /bundle\.js\?v=(\d+)\.(\d+)\.(\d+)/;
const m = content.match(re);

if (!m) {
  console.error(`❌ No se encontró patrón bundle.js?v=x.y.z en ${filePath}`);
  process.exit(2);
}

content = content.replace(re, (_, major, minor, patch) => {
  const newPatch = Number(patch) + 1;
  return `bundle.js?v=${major}.${minor}.${newPatch}`;
});

fs.writeFileSync(filePath, content);
console.log('✅ footer-end.php actualizado con nueva versión');
