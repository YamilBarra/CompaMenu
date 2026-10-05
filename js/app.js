// CompaMenu - Programación Web | app.js: lógica de la página (filtros, CRUD de menús, reseñas y menú del celular)
// Nombres con los que guardamos los datos en el navegador (localStorage)
const claveMenus = "compamenu_menus_v2";
const claveResenas = "compamenu_resenas";

// Función corta para no escribir document.getElementById tantas veces
function porId(id) {
  return document.getElementById(id);
}

// Traemos los elementos del HTML que vamos a usar (si no existen en esta página quedan en null)
const contenedorResultados = porId("resultados");
const contenedorRemates = porId("remates");
const listaVal = porId("listaVal");
const resumen = porId("resumen");
const inputPrecio = porId("precio");
const precioValor = porId("precioValor");
const selectTipo = porId("tipo");
const selectDistancia = porId("distancia");
const inputTexto = porId("texto");
const selectLocal = porId("valLocal");
const listaLocales = porId("listaLocales");
const formMenu = porId("formMenu");
const cuerpoTabla = porId("cuerpoTabla");
const resumenTabla = porId("resumenTabla");
const avisoMenu = porId("avisoMenu");
const tituloForm = porId("tituloForm");
const btnGuardarMenu = porId("btnGuardarMenu");
const btnCancelarEdicion = porId("btnCancelarEdicion");
const campoFoto = porId("menuFoto");
const vistaPrevia = porId("vistaPrevia");
const btnQuitarFoto = porId("btnQuitarFoto");
const modalEliminar = porId("modalEliminar");
const textoEliminar = porId("textoEliminar");

// Campos del formulario del CRUD (solo existen en panel.html)
const campos = formMenu ? {
  nombre: porId("menuNombre"),
  local: porId("menuLocal"),
  precio: porId("menuPrecio"),
  distancia: porId("menuDistancia"),
  tipo: porId("menuTipo")
} : {};

// Para mostrar el tipo de plato con su nombre completo
const nombresTipo = {
  criollo: "Criollo",
  andino: "Andino",
  vegetariano: "Vegetariano",
  proteico: "Alto en proteínas"
};

// Hace una copia de un objeto para no modificar el original
function copiar(valor) {
  return JSON.parse(JSON.stringify(valor));
}

// Lee los datos guardados en el navegador; si no hay nada usa los datos de ejemplo
function leerGuardado(clave, valorInicial) {
  try {
    const texto = localStorage.getItem(clave);
    if (texto) {
      const datos = JSON.parse(texto);
      if (Array.isArray(datos)) {
        return datos;
      }
    }
  } catch (error) {
    return copiar(valorInicial);
  }
  return copiar(valorInicial);
}

// Guarda los datos en el navegador. Devuelve false si no hay espacio
function escribirGuardado(clave, valor) {
  try {
    localStorage.setItem(clave, JSON.stringify(valor));
    return true;
  } catch (error) {
    return false;
  }
}

// Variables que van cambiando mientras se usa la página
let platos = leerGuardado(claveMenus, platosIniciales);
let listaResenas = leerGuardado(claveResenas, resenas);
let idEnEdicion = null;
let idPorEliminar = null;
let fotoActual = "";

// Convierte un número a texto con formato, por ejemplo S/. 7.50
function formatoPrecio(valor) {
  return "S/. " + Number(valor).toFixed(2);
}

// Convierte un puntaje (ejemplo 4.5) en estrellas llenas y vacías
function estrellas(puntaje) {
  const llenas = Math.round(puntaje);
  return "★".repeat(llenas) + "☆".repeat(5 - llenas);
}

// Pone la foto del plato; si no tiene foto muestra un recuadro con texto
function ponerFoto(contenedor, clave, texto, textoVacio) {
  contenedor.innerHTML = "";
  contenedor.classList.remove("sin-foto");
  const origen = clave && clave.indexOf("data:") === 0 ? clave : imagenes[clave];
  if (origen) {
    const img = document.createElement("img");
    img.src = origen;
    img.alt = texto;
    img.loading = "lazy";
    contenedor.appendChild(img);
  } else {
    contenedor.classList.add("sin-foto");
    contenedor.textContent = textoVacio;
  }
}

// Arma la tarjeta de un plato (foto, precio, local y etiquetas)
function crearTarjetaPlato(plato) {
  const tarjeta = document.createElement("article");
  tarjeta.className = "tarjeta";
  tarjeta.innerHTML =
    '<div class="foto"></div>' +
    '<div class="tarjeta-cuerpo">' +
    '<div class="tarjeta-fila"><h3></h3><span class="precio"></span></div>' +
    '<p class="local"></p>' +
    '<div class="etiquetas"><span class="etiqueta"></span><span class="etiqueta dist"></span></div>' +
    '<p class="valor"></p></div>';
  ponerFoto(tarjeta.querySelector(".foto"), plato.imagen, plato.nombre, "Sin foto");
  tarjeta.querySelector("h3").textContent = plato.nombre;
  tarjeta.querySelector(".precio").textContent = formatoPrecio(plato.precio);
  tarjeta.querySelector(".local").textContent = plato.local;
  tarjeta.querySelector(".etiqueta").textContent = nombresTipo[plato.tipo];
  tarjeta.querySelector(".dist").textContent = plato.distancia + " m del campus";
  const valor = tarjeta.querySelector(".valor");
  if (plato.puntaje > 0) {
    valor.innerHTML = '<span class="estrellas"></span> <span class="num"></span>';
    valor.querySelector(".estrellas").textContent = estrellas(plato.puntaje);
    valor.querySelector(".num").textContent = plato.puntaje.toFixed(1);
  } else {
    valor.className = "sin-resenas";
    valor.textContent = "Aún sin reseñas";
  }
  return tarjeta;
}

// READ del CRUD: filtra los platos según el buscador y los muestra
function mostrarPlatos() {
  if (!contenedorResultados) {
    return;
  }
  const precioMax = parseFloat(inputPrecio.value);
  const tipo = selectTipo.value;
  const distanciaMax = parseInt(selectDistancia.value, 10);
  const texto = inputTexto.value.trim().toLowerCase();

  // Nos quedamos solo con los platos que cumplen todos los filtros
  const filtrados = platos.filter(function (p) {
    const cumplePrecio = p.precio <= precioMax;
    const cumpleTipo = tipo === "todos" || p.tipo === tipo;
    const cumpleDistancia = p.distancia <= distanciaMax;
    const cumpleTexto = p.nombre.toLowerCase().includes(texto) || p.local.toLowerCase().includes(texto);
    return cumplePrecio && cumpleTipo && cumpleDistancia && cumpleTexto;
  });

  // Ordenamos del más barato al más caro
  filtrados.sort(function (a, b) {
    return a.precio - b.precio;
  });

  contenedorResultados.innerHTML = "";

  if (filtrados.length === 0) {
    const vacio = document.createElement("p");
    vacio.className = "vacio";
    vacio.textContent = "No hay menús con esos filtros. Prueba subiendo el precio o ampliando la distancia.";
    contenedorResultados.appendChild(vacio);
  } else {
    filtrados.forEach(function (p) {
      contenedorResultados.appendChild(crearTarjetaPlato(p));
    });
  }

  resumen.textContent = filtrados.length + " resultado(s) hasta " + formatoPrecio(precioMax);
}

// Muestra las tarjetas del remate solidario
function mostrarRemates() {
  if (!contenedorRemates) {
    return;
  }
  contenedorRemates.innerHTML = "";
  remates.forEach(function (r) {
    const tarjeta = document.createElement("article");
    tarjeta.className = "tarjeta";
    tarjeta.innerHTML =
      '<div class="foto"></div>' +
      '<div class="tarjeta-cuerpo">' +
      '<div class="tarjeta-fila"><h3></h3><span class="precio"><small></small><b></b></span></div>' +
      '<p class="local"></p>' +
      '<div class="etiquetas"><span class="etiqueta alerta"></span><span class="etiqueta hora"></span></div></div>';
    ponerFoto(tarjeta.querySelector(".foto"), r.imagen, r.nombre, "Sin foto");
    tarjeta.querySelector("h3").textContent = r.nombre;
    tarjeta.querySelector("small").textContent = formatoPrecio(r.antes);
    tarjeta.querySelector("b").textContent = formatoPrecio(r.ahora);
    tarjeta.querySelector(".local").textContent = r.local;
    tarjeta.querySelector(".alerta").textContent = "Quedan " + r.quedan + " porciones";
    tarjeta.querySelector(".hora").textContent = r.hora;
    contenedorRemates.appendChild(tarjeta);
  });
}

// READ del CRUD: dibuja la tabla del panel con todos los menús
function mostrarTabla() {
  if (!cuerpoTabla) {
    return;
  }
  cuerpoTabla.innerHTML = "";
  platos.forEach(function (p) {
    const fila = document.createElement("tr");
    fila.innerHTML =
      '<td data-label="Foto"><div class="miniatura"></div></td>' +
      '<td data-label="Plato" class="c-nombre"></td>' +
      '<td data-label="Local" class="c-local"></td>' +
      '<td data-label="Precio" class="c-precio"></td>' +
      '<td data-label="Acciones" class="acciones">' +
      '<button type="button" class="btn btn-chico btn-editar">Editar</button>' +
      '<button type="button" class="btn btn-chico btn-borrar">Eliminar</button></td>';
    ponerFoto(fila.querySelector(".miniatura"), p.imagen, p.nombre, "Sin foto");
    fila.querySelector(".c-nombre").textContent = p.nombre;
    fila.querySelector(".c-local").textContent = p.local;
    fila.querySelector(".c-precio").textContent = formatoPrecio(p.precio);
    fila.querySelector(".btn-editar").dataset.id = p.id;
    fila.querySelector(".btn-borrar").dataset.id = p.id;
    cuerpoTabla.appendChild(fila);
  });
  resumenTabla.textContent = platos.length === 0
    ? "Todavía no hay menús. Agrega el primero con el formulario."
    : platos.length + " menú(s) registrados";
}

// Llena la lista de locales (para las reseñas y las sugerencias del formulario)
function actualizarLocales() {
  const nombres = [];
  platos.forEach(function (p) {
    if (!nombres.includes(p.local)) {
      nombres.push(p.local);
    }
  });
  if (selectLocal) {
    const elegido = selectLocal.value;
    selectLocal.innerHTML = "";
    nombres.forEach(function (n) {
      const opcion = document.createElement("option");
      opcion.value = n;
      opcion.textContent = n;
      selectLocal.appendChild(opcion);
    });
    if (nombres.includes(elegido)) {
      selectLocal.value = elegido;
    }
  }
  if (listaLocales) {
    listaLocales.innerHTML = "";
    nombres.forEach(function (n) {
      const sugerencia = document.createElement("option");
      sugerencia.value = n;
      listaLocales.appendChild(sugerencia);
    });
  }
  if (porId("contadorLocales")) {
    porId("contadorLocales").textContent = nombres.length;
    porId("contadorRemates").textContent = remates.length;
  }
}

// Vuelve a dibujar todo después de crear, editar o eliminar
function refrescarTodo() {
  mostrarPlatos();
  mostrarTabla();
  actualizarLocales();
}

// Muestra la foto elegida antes de guardar el menú
function mostrarVistaPrevia() {
  ponerFoto(vistaPrevia, fotoActual, "Vista previa de la foto", "Sin foto");
  btnQuitarFoto.hidden = fotoActual === "";
}

// Reduce la foto a 640 px de ancho como máximo para que pese poco
function reducirImagen(archivo, devolver) {
  const lector = new FileReader();
  lector.onload = function () {
    const img = new Image();
    img.onload = function () {
      const ancho = Math.min(640, img.width);
      const alto = Math.round(img.height * ancho / img.width);
      const lienzo = document.createElement("canvas");
      lienzo.width = ancho;
      lienzo.height = alto;
      lienzo.getContext("2d").drawImage(img, 0, 0, ancho, alto);
      devolver(lienzo.toDataURL("image/jpeg", 0.72));
    };
    img.src = lector.result;
  };
  lector.readAsDataURL(archivo);
}

// Quita el borde rojo de los campos que tenían error
function limpiarErrores() {
  Object.keys(campos).forEach(function (k) {
    campos[k].classList.remove("invalido");
  });
}

// Muestra un mensaje debajo del formulario (verde = bien, rojo = error)
function mostrarAvisoMenu(texto, esError) {
  avisoMenu.className = esError ? "aviso error" : "aviso";
  avisoMenu.textContent = texto;
}

// Revisa que los datos del formulario estén bien antes de guardar
function validarMenu() {
  limpiarErrores();
  const nombre = campos.nombre.value.trim();
  const local = campos.local.value.trim();
  const precio = parseFloat(campos.precio.value);
  const distancia = parseInt(campos.distancia.value, 10);
  let mensaje = "";

  if (nombre.length < 3) {
    campos.nombre.classList.add("invalido");
    mensaje = "Escribe el nombre del plato (mínimo 3 letras).";
  } else if (local.length < 3) {
    campos.local.classList.add("invalido");
    mensaje = "Escribe el nombre del local.";
  } else if (isNaN(precio) || precio < 1 || precio > 30) {
    campos.precio.classList.add("invalido");
    mensaje = "El precio debe estar entre S/. 1 y S/. 30.";
  } else if (isNaN(distancia) || distancia < 0 || distancia > 5000) {
    campos.distancia.classList.add("invalido");
    mensaje = "La distancia debe estar entre 0 y 5000 metros.";
  }

  if (mensaje !== "") {
    mostrarAvisoMenu(mensaje, true);
    return null;
  }
  return { nombre: nombre, local: local, precio: precio, distancia: distancia };
}

// Deja el formulario vacío y listo para crear un menú nuevo
function salirDeEdicion() {
  idEnEdicion = null;
  formMenu.reset();
  fotoActual = "";
  mostrarVistaPrevia();
  limpiarErrores();
  tituloForm.textContent = "Nuevo menú";
  btnGuardarMenu.textContent = "Agregar menú";
  btnCancelarEdicion.hidden = true;
}

// Todo el CRUD del panel (solo corre en panel.html)
if (formMenu) {
  mostrarVistaPrevia();

  // Cuando el usuario elige una foto revisamos el tipo y el tamaño
  campoFoto.addEventListener("change", function () {
    const archivo = campoFoto.files[0];
    if (!archivo) {
      return;
    }
    const permitidos = ["image/jpeg", "image/png", "image/webp"];
    if (!permitidos.includes(archivo.type)) {
      mostrarAvisoMenu("La foto debe ser JPG, PNG o WEBP.", true);
      campoFoto.value = "";
      return;
    }
    if (archivo.size > 5 * 1024 * 1024) {
      mostrarAvisoMenu("La foto pesa más de 5 MB. Elige una más liviana.", true);
      campoFoto.value = "";
      return;
    }
    mostrarAvisoMenu("", false);
    reducirImagen(archivo, function (url) {
      fotoActual = url;
      mostrarVistaPrevia();
    });
  });

  // Botón para quitar la foto elegida
  btnQuitarFoto.addEventListener("click", function () {
    fotoActual = "";
    campoFoto.value = "";
    mostrarVistaPrevia();
  });

  // CREATE y UPDATE: al enviar el formulario guardamos el menú
  formMenu.addEventListener("submit", function (e) {
    e.preventDefault();
    const datos = validarMenu();
    if (datos === null) {
      return;
    }
    datos.tipo = campos.tipo.value;
    datos.imagen = fotoActual;
    let mensaje = "";

    // Si no estamos editando es un menú nuevo; si estamos editando actualizamos el que ya existe
    if (idEnEdicion === null) {
      let mayor = 0;
      platos.forEach(function (p) {
        if (p.id > mayor) {
          mayor = p.id;
        }
      });
      datos.id = mayor + 1;
      datos.puntaje = 0;
      platos.push(datos);
      mensaje = "Menú agregado. Ya aparece en la página de inicio.";
    } else {
      const indice = platos.findIndex(function (p) {
        return p.id === idEnEdicion;
      });
      datos.id = idEnEdicion;
      datos.puntaje = platos[indice].puntaje;
      platos[indice] = datos;
      mensaje = "Cambios guardados.";
    }
    salirDeEdicion();
    refrescarTodo();
    if (escribirGuardado(claveMenus, platos)) {
      mostrarAvisoMenu(mensaje, false);
    } else {
      mostrarAvisoMenu("No se pudo guardar en el navegador (poco espacio). Prueba con fotos más pequeñas.", true);
    }
  });

  btnCancelarEdicion.addEventListener("click", function () {
    salirDeEdicion();
    mostrarAvisoMenu("", false);
  });

  // Botones Editar y Eliminar de la tabla (cada botón guarda el id de su menú)
  cuerpoTabla.addEventListener("click", function (e) {
    const boton = e.target.closest("button");
    if (!boton) {
      return;
    }
    const id = parseInt(boton.dataset.id, 10);
    const plato = platos.find(function (p) {
      return p.id === id;
    });
    if (!plato) {
      return;
    }
    // Editar: llenamos el formulario con los datos del menú elegido
    if (boton.classList.contains("btn-editar")) {
      idEnEdicion = id;
      campos.nombre.value = plato.nombre;
      campos.local.value = plato.local;
      campos.precio.value = plato.precio;
      campos.distancia.value = plato.distancia;
      campos.tipo.value = plato.tipo;
      campoFoto.value = "";
      fotoActual = plato.imagen;
      mostrarVistaPrevia();
      tituloForm.textContent = "Editar menú";
      btnGuardarMenu.textContent = "Guardar cambios";
      btnCancelarEdicion.hidden = false;
      limpiarErrores();
      mostrarAvisoMenu("", false);
      formMenu.scrollIntoView({ behavior: "smooth", block: "center" });
      campos.nombre.focus({ preventScroll: true });
    } else {
      idPorEliminar = id;
      textoEliminar.textContent = plato.nombre + " de " + plato.local + " se quitará de la lista.";
      modalEliminar.showModal();
    }
  });

  porId("btnNoEliminar").addEventListener("click", function () {
    idPorEliminar = null;
    modalEliminar.close();
  });

  // DELETE: si el usuario confirma, quitamos el menú de la lista
  porId("btnSiEliminar").addEventListener("click", function () {
    platos = platos.filter(function (p) {
      return p.id !== idPorEliminar;
    });
    if (idEnEdicion === idPorEliminar) {
      salirDeEdicion();
    }
    idPorEliminar = null;
    modalEliminar.close();
    escribirGuardado(claveMenus, platos);
    refrescarTodo();
    mostrarAvisoMenu("Menú eliminado.", false);
  });
}

// Crea la caja de una reseña y la agrega a la lista
function agregarResena(r, alInicio) {
  const caja = document.createElement("article");
  caja.className = "resena";
  caja.innerHTML =
    '<div class="resena-cab"><strong></strong><span class="estrellas"></span></div>' +
    '<p class="local"></p><p class="texto"></p>';
  caja.querySelector("strong").textContent = r.nombre;
  caja.querySelector(".estrellas").textContent = estrellas(r.puntaje);
  caja.querySelector(".local").textContent = r.local;
  caja.querySelector(".texto").textContent = r.texto;
  if (alInicio) {
    listaVal.prepend(caja);
  } else {
    listaVal.appendChild(caja);
  }
}

// Eventos del buscador (solo en index.html): cada cambio vuelve a filtrar los platos
if (inputPrecio) {
  inputPrecio.addEventListener("input", function () {
    precioValor.textContent = formatoPrecio(parseFloat(inputPrecio.value));
    mostrarPlatos();
  });
  selectTipo.addEventListener("change", mostrarPlatos);
  selectDistancia.addEventListener("change", mostrarPlatos);
  inputTexto.addEventListener("input", mostrarPlatos);
  porId("buscador").addEventListener("submit", function (e) {
    e.preventDefault();
  });
  porId("btnLimpiar").addEventListener("click", function () {
    inputPrecio.value = 7.5;
    precioValor.textContent = formatoPrecio(7.5);
    selectTipo.value = "todos";
    selectDistancia.value = "999999";
    inputTexto.value = "";
    mostrarPlatos();
  });
}

// Reseñas (solo en resenas.html): mostramos las guardadas y el formulario para agregar otra
if (listaVal) {
  listaResenas.forEach(function (r) {
    agregarResena(r, false);
  });
  porId("formVal").addEventListener("submit", function (e) {
    e.preventDefault();
    const aviso = porId("avisoVal");
    const nombre = porId("valNombre").value.trim();
    const texto = porId("valTexto").value.trim();
    if (nombre === "" || texto === "" || selectLocal.value === "") {
      aviso.className = "aviso error";
      aviso.textContent = "Completa tu nombre y comentario.";
      return;
    }
    const nueva = {
      local: selectLocal.value,
      nombre: nombre,
      puntaje: parseInt(porId("valPuntaje").value, 10),
      texto: texto
    };
    agregarResena(nueva, true);
    listaResenas.unshift(nueva);
    escribirGuardado(claveResenas, listaResenas);
    e.target.reset();
    aviso.className = "aviso";
    aviso.textContent = "Reseña publicada. Gracias por opinar.";
  });
}

// Menú del celular: el botón ☰ abre y cierra la navegación
const btnMenu = porId("btnMenu");
const nav = porId("nav");
btnMenu.addEventListener("click", function () {
  const abierto = nav.classList.toggle("abierto");
  btnMenu.setAttribute("aria-expanded", abierto);
});
nav.querySelectorAll("a").forEach(function (enlace) {
  enlace.addEventListener("click", function () {
    nav.classList.remove("abierto");
    btnMenu.setAttribute("aria-expanded", "false");
  });
});

// Ventana de ingresar o crear cuenta (solo diseño, todavía no hay base de datos)
const modal = porId("modalIngreso");
const pestanas = document.querySelectorAll(".pestana");
const campoNombre = porId("campoNombre");
const btnEnviar = porId("btnEnviarIngreso");
const avisoIngreso = porId("avisoIngreso");
let modo = "ingreso";

// Cambia entre las pestañas Ingresar y Crear cuenta
function cambiarModo(nuevo) {
  modo = nuevo;
  pestanas.forEach(function (p) {
    p.classList.toggle("activa", p.dataset.modo === nuevo);
  });
  campoNombre.hidden = nuevo !== "registro";
  btnEnviar.textContent = nuevo === "registro" ? "Crear cuenta" : "Ingresar";
  avisoIngreso.textContent = "";
}

pestanas.forEach(function (p) {
  p.addEventListener("click", function () {
    cambiarModo(p.dataset.modo);
  });
});
porId("btnIngresar").addEventListener("click", function () {
  nav.classList.remove("abierto");
  modal.showModal();
});
porId("btnCerrar").addEventListener("click", function () {
  modal.close();
});
porId("formIngreso").addEventListener("submit", function (e) {
  e.preventDefault();
  const clave = porId("ingClave").value;
  if (clave.length < 6) {
    avisoIngreso.className = "aviso error";
    avisoIngreso.textContent = "La contraseña debe tener al menos 6 caracteres.";
    return;
  }
  avisoIngreso.className = "aviso";
  avisoIngreso.textContent = modo === "registro" ? "Cuenta de prueba creada (solo diseño)." : "Sesión de prueba iniciada (solo diseño).";
});

// Al cargar la página dibujamos todo
refrescarTodo();
mostrarRemates();
