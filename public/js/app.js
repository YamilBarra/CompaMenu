/* CompaMenu - Programación Web | app.js
   JavaScript de la interfaz (sin librerías: JavaScript puro, ES6).

   Contenido (busca el título con Ctrl+F):
     1. Menú ☰ (celular)
     2. Buscador de Inicio con fetch()        -> usa HomeController@index (responde JSON)
     3. Confirmación antes de eliminar        -> formularios con data-confirmar
     4. Panel de menús: platos dinámicos      -> plantilla #plantillaPlato
     5. Foto del menú: vista previa y reducción a 640 px
     6. Registro: datos del local solo para establecimientos
     7. Reserva: total según la cantidad
     8. Reseñas: contador de caracteres

   Cada bloque solo actúa si encuentra sus elementos en la página, así un mismo archivo sirve para todas.
   La seguridad NO depende de este archivo: toda regla importante se vuelve a validar en el servidor (Laravel).

   Referencias: Fetch API  https://developer.mozilla.org/es/docs/Web/API/Fetch_API
                <dialog>   https://developer.mozilla.org/es/docs/Web/HTML/Element/dialog
                FileReader https://developer.mozilla.org/es/docs/Web/API/FileReader */
(function () {
  "use strict";

  // Atajos: $ devuelve un elemento y $$ una lista de elementos que coinciden con el selector CSS
  const $ = (selector, contexto) => (contexto || document).querySelector(selector);
  const $$ = (selector, contexto) => Array.from((contexto || document).querySelectorAll(selector));

  /* ---------- Menú ☰ (celular) ---------- */
  const btnMenu = $("#btnMenu");
  const nav = $("#nav");
  if (btnMenu && nav) {
    btnMenu.addEventListener("click", () => {
      const abierto = nav.classList.toggle("abierto");
      btnMenu.setAttribute("aria-expanded", String(abierto));
      btnMenu.textContent = abierto ? "✕" : "☰";
    });
  }

  /* ---------- Buscador de Inicio: filtra con fetch sin recargar la página ---------- */
  const buscador = $("#buscador");
  if (buscador) {
    const resultados = $("#resultados");
    const resumen = $("#resumen");
    const precio = $("#precio");
    const precioValor = $("#precioValor");
    let temporizador = null;
    let solicitud = 0;

    // Actualiza el texto "S/. 7.50" junto al control deslizante de precio
    const mostrarPrecio = () => {
      precioValor.textContent = "S/. " + Number(precio.value).toFixed(2);
    };

    // Pide los resultados al servidor con los filtros del formulario y reemplaza las tarjetas.
    // "solicitud" numera las peticiones: si llegan respuestas desordenadas, solo se usa la más reciente.
    const buscar = async () => {
      const parametros = new URLSearchParams(new FormData(buscador));
      const actual = ++solicitud;
      resultados.classList.add("cargando");
      try {
        const respuesta = await fetch(buscador.action + "?" + parametros.toString(), {
          headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
        });
        if (!respuesta.ok) throw new Error("Error " + respuesta.status);
        const datos = await respuesta.json();
        if (actual !== solicitud) return; // llegó una respuesta más nueva
        resultados.innerHTML = datos.html;
        resumen.textContent = datos.resumen;
        history.replaceState(null, "", "?" + parametros.toString()); // la URL guarda los filtros
      } catch (error) {
        resumen.textContent = "No pudimos actualizar los resultados. Revisa tu conexión.";
      } finally {
        if (actual === solicitud) resultados.classList.remove("cargando");
      }
    };

    // "Debounce": espera 250 ms sin cambios antes de buscar, para no consultar en cada tecla
    const programar = () => {
      clearTimeout(temporizador);
      temporizador = setTimeout(buscar, 250); // espera a que termines de escribir
    };

    precio.addEventListener("input", () => { mostrarPrecio(); programar(); });
    buscador.addEventListener("input", (e) => { if (e.target !== precio) programar(); });
    buscador.addEventListener("change", programar);
    buscador.addEventListener("submit", (e) => { e.preventDefault(); buscar(); });

    $("#btnLimpiar").addEventListener("click", (e) => {
      e.preventDefault();
      buscador.reset();
      precio.value = 7.5;
      $("#tipo").value = "todos";
      $("#distancia").value = "999999";
      $("#q").value = "";
      $("#disp").checked = true;
      $("#rem").checked = false;
      mostrarPrecio();
      buscar();
    });

    mostrarPrecio();
  }

  /* ---------- Confirmación antes de eliminar (formularios con data-confirmar) ---------- */
  const dialogo = $("#dlgConfirmar");
  if (dialogo && typeof dialogo.showModal === "function") {
    let formularioPendiente = null;
    // Intercepta cualquier formulario con data-confirmar, muestra el diálogo y solo lo envía si se confirma
    document.addEventListener("submit", (e) => {
      const formulario = e.target;
      if (!(formulario instanceof HTMLFormElement) || !formulario.dataset.confirmar) return;
      if (formulario.dataset.confirmado === "1") return;
      e.preventDefault();
      formularioPendiente = formulario;
      $("#dlgTexto").textContent = formulario.dataset.confirmar;
      dialogo.showModal();
    });
    $("#dlgNo").addEventListener("click", () => { formularioPendiente = null; dialogo.close(); });
    $("#dlgSi").addEventListener("click", () => {
      dialogo.close();
      if (formularioPendiente) {
        formularioPendiente.dataset.confirmado = "1";
        formularioPendiente.submit();
      }
    });
  } else {
    // Sin <dialog>: se usa el cuadro de confirmación del navegador
    document.addEventListener("submit", (e) => {
      const formulario = e.target;
      if (formulario.dataset && formulario.dataset.confirmar && !window.confirm(formulario.dataset.confirmar)) e.preventDefault();
    });
  }

  /* ---------- Panel de menús: platos dinámicos ---------- */
  const contenedorPlatos = $("#platos");
  const plantilla = $("#plantillaPlato");
  if (contenedorPlatos && plantilla) {
    // El índice empieza alto para no repetir los nombres platos[0], platos[1]... de las filas ya impresas por Blade
    let indice = contenedorPlatos.querySelectorAll(".fila-plato").length + 100; // evita repetir índices
    $("#agregarPlato").addEventListener("click", () => {
      if (contenedorPlatos.querySelectorAll(".fila-plato").length >= 6) return;
      const html = plantilla.innerHTML.replaceAll("__I__", String(indice++));
      contenedorPlatos.insertAdjacentHTML("beforeend", html);
      const nuevos = $$(".fila-plato input", contenedorPlatos);
      nuevos[nuevos.length - 1].focus();
    });
    contenedorPlatos.addEventListener("click", (e) => {
      const boton = e.target.closest("[data-quitar-plato]");
      if (!boton) return;
      const filas = contenedorPlatos.querySelectorAll(".fila-plato");
      const fila = boton.closest(".fila-plato");
      if (filas.length > 1) fila.remove();
      else $$("input", fila).forEach((campo) => (campo.value = "")); // deja una fila vacía
    });
  }

  /* ---------- Foto del menú: vista previa y reducción a 640 px antes de enviarla ---------- */
  const inputFoto = $("#foto");
  const vistaPrevia = $("#vistaPrevia");
  if (inputFoto && vistaPrevia) {
    inputFoto.addEventListener("change", () => {
      const archivo = inputFoto.files && inputFoto.files[0];
      if (!archivo) return;
      if (!/^image\/(jpeg|png|webp)$/.test(archivo.type)) {
        alert("Elige una imagen JPG, PNG o WEBP.");
        inputFoto.value = "";
        return;
      }
      const lector = new FileReader();
      lector.onload = () => {
        const imagen = new Image();
        imagen.onload = () => {
          const escala = Math.min(1, 640 / Math.max(imagen.width, imagen.height));
          const lienzo = document.createElement("canvas");
          lienzo.width = Math.round(imagen.width * escala);
          lienzo.height = Math.round(imagen.height * escala);
          lienzo.getContext("2d").drawImage(imagen, 0, 0, lienzo.width, lienzo.height);
          vistaPrevia.src = lienzo.toDataURL("image/jpeg", 0.8);
          vistaPrevia.hidden = false;
          // Reemplaza el archivo elegido por la versión reducida (si el navegador lo permite).
          // Ahorra datos móviles y espacio; el servidor igualmente valida tipo y tamaño (MenuRequest).
          lienzo.toBlob((blob) => {
            try {
              if (!blob) return;
              const reducida = new File([blob], "menu.jpg", { type: "image/jpeg" });
              const transferencia = new DataTransfer();
              transferencia.items.add(reducida);
              inputFoto.files = transferencia.files;
            } catch (error) { /* se envía la foto original */ }
          }, "image/jpeg", 0.8);
        };
        imagen.src = lector.result;
      };
      lector.readAsDataURL(archivo);
    });
  }

  /* ---------- Registro: los datos del local solo aparecen para establecimientos ---------- */
  const formRegistro = $("#formRegistro");
  if (formRegistro) {
    const bloque = $("#bloqueLocal");
    const actualizar = () => {
      const rol = formRegistro.querySelector('input[name="rol"]:checked');
      bloque.hidden = !(rol && rol.value === "establecimiento");
    };
    formRegistro.addEventListener("change", (e) => { if (e.target.name === "rol") actualizar(); });
    actualizar();
  }

  /* ---------- Reserva: total según la cantidad ---------- */
  const formReserva = $("#formReserva");
  if (formReserva) {
    const cantidad = $("#campo-cantidad");
    const total = $("#totalReserva");
    const calcular = () => {
      const n = Math.max(1, parseInt(cantidad.value, 10) || 1);
      total.textContent = "S/. " + (n * parseFloat(formReserva.dataset.precio)).toFixed(2);
    };
    cantidad.addEventListener("input", calcular);
    calcular();
  }

  /* ---------- Reseñas: contador de caracteres ---------- */
  const comentario = $("#campo-comentario");
  const contador = $("#contadorComentario");
  if (comentario && contador) {
    const contar = () => { contador.textContent = comentario.value.length; };
    comentario.addEventListener("input", contar);
    contar();
  }
})();
