// CompaMenu - Programación Web | datos.js: datos de ejemplo mientras no hay base de datos
// Rutas de las fotos. La clave (aji, lomo...) es el nombre corto que usamos en los datos
const imagenes = {
  aji: "img/aji-de-gallina.jpg",
  lomo: "img/lomo-saltado.jpg",
  ceviche: "img/ceviche.jpg",
  chairo: "img/chairo.jpg",
  menestron: "img/menestron.jpg"
};

// Menús de ejemplo. Cuando tengamos base de datos esto vendrá de MySQL
const platosIniciales = [
  { id: 1, nombre: "Ají de gallina con arroz", local: "Comedor Inti", precio: 7, tipo: "criollo", distancia: 300, puntaje: 4.5, imagen: "aji" },
  { id: 2, nombre: "Lomo saltado con papas fritas", local: "Pensión Doña Rosa", precio: 7.5, tipo: "criollo", distancia: 150, puntaje: 4.5, imagen: "lomo" },
  { id: 3, nombre: "Ceviche de trucha con camote y choclo", local: "Fit Campus", precio: 9, tipo: "proteico", distancia: 100, puntaje: 5, imagen: "ceviche" },
  { id: 4, nombre: "Menestrón y guiso de lentejas con huevo", local: "Kiosko Mama Juana", precio: 5.5, tipo: "vegetariano", distancia: 80, puntaje: 3.5, imagen: "menestron" },
  { id: 5, nombre: "Chairo cusqueño y estofado de res", local: "Comedor Los Andes", precio: 8, tipo: "andino", distancia: 450, puntaje: 4.5, imagen: "chairo" },
  { id: 6, nombre: "Crema de zapallo y tortilla de verduras", local: "Verde Sabor", precio: 7.5, tipo: "vegetariano", distancia: 220, puntaje: 4, imagen: "" },
  { id: 7, nombre: "Caldo de gallina y arroz con pollo", local: "Comedor Inti", precio: 6, tipo: "criollo", distancia: 300, puntaje: 4, imagen: "" }
];

// Remates solidarios de ejemplo
const remates = [
  { nombre: "Ají de gallina con arroz", local: "Comedor Inti", antes: 7, ahora: 4.5, quedan: 9, hora: "Desde las 3:00 pm", imagen: "aji" },
  { nombre: "Lomo saltado con papas fritas", local: "Pensión Doña Rosa", antes: 7.5, ahora: 5, quedan: 4, hora: "Desde las 4:00 pm", imagen: "lomo" },
  { nombre: "Estofado de res con arroz", local: "Comedor Los Andes", antes: 8, ahora: 5, quedan: 6, hora: "Desde las 3:30 pm", imagen: "" }
];

// Reseñas de ejemplo
const resenas = [
  { local: "Pensión Doña Rosa", nombre: "Lucía", puntaje: 5, texto: "Porciones generosas y el local siempre está limpio." },
  { local: "Comedor Inti", nombre: "Marco", puntaje: 4, texto: "Buen sabor y atienden rápido entre clases." },
  { local: "Kiosko Mama Juana", nombre: "Andrea", puntaje: 3, texto: "Es barato, pero la porción de arroz es pequeña." }
];
