const configTable = {
  language: language,
  paging: false, // Hace que la tabla sea responsiva
  autoWidth: false, // Desactiva el ajuste automático de ancho
  scrollX: false,
  scrollY: "330px",
  lengthChange: false,
  info: false,
  scrollCollapse: true,
  columnDefs: [{ orderable: false, targets: [0, 1] }],
};

const configEnvio = {
  language: language,
  paging: false, // Hace que la tabla sea responsiva
  autoWidth: false, // Desactiva el ajuste automático de ancho
  scrollX: false,
  lengthChange: false,
  info: false,
  scrollCollapse: true,
  columnDefs: [{ orderable: false, targets: [0, 1] }],
};

$("#tableContribuyentes").DataTable(configTable);

const btn = document.getElementById("btnEmojiTemplate");
const input = document.getElementById("editorTemplate");
const titulo = document.getElementById("titulo");
const pickerContainer = document.getElementById("pickerContainer");
const picker = pickerContainer.querySelector("emoji-picker");

const listContri = document.getElementById("listContri");

contribuyentesActivos();

function contribuyentesActivos() {
  const seleccionado = document.querySelector(
    'input[name="contribuyenteType"]:checked'
  );

  fetch(base_url + "contribuyentes/contribuyentesActivos/" + seleccionado.value)
    .then((res) => res.json())
    .then((data) => {
      viewContribuyentesActivos(data);
    });
}

function viewContribuyentesActivos(data) {
  let html = "";
  data.forEach((contri) => {
    html += `
    <tr>
        <td>
            <input type="checkbox" class="form-check-input" name="contribuyentes[]" value="${contri.id}" checked>
        </td>
        <td>${contri.razon_social}</td>
    </tr>`;
  });

  $("#tableContribuyentes").DataTable().destroy();

  listContri.innerHTML = html;

  $("#tableContribuyentes").DataTable(configTable);

  checkContribuyente();
}

document
  .querySelectorAll('input[name="contribuyenteType"]')
  .forEach((radio) => {
    radio.addEventListener("change", () => {
      contribuyentesActivos();
    });
  });

const formMessageMasivos = document.getElementById("formMessageMasivos");

formMessageMasivos.addEventListener("submit", (e) => {
  e.preventDefault();

  Swal.fire({
    title: "¿Estas seguro?",
    text: "No podrá revertir después!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Enviar!",
    cancelButtonText: "Cancelar"
  }).then((result) => {
    if (result.isConfirmed) {
      const formData = new FormData(formMessageMasivos);

      fetch(base_url + "mensajes/guardarMensajeMasivo", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.status) {
            Swal.fire({
              position: "top-center",
              icon: "success",
              title: data.message,
              showConfirmButton: false,
              timer: 1500,
            });

            input.value = "";
            titulo.value = "";

            return;
          }

          Swal.fire({
            position: "top-center",
            icon: "error",
            title: data.message,
          });
        });
    }
  });

});

const checkAll = document.getElementById("checkAll");

function checkContribuyente() {
  const checks = document.querySelectorAll('input[name="contribuyentes[]"]');

  checks.forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
      const todosSeleccionados = Array.from(checks).every(
        (checkbox) => checkbox.checked
      );

      if (todosSeleccionados) {
        checkAll.checked = true;
      } else {
        checkAll.checked = false;
      }
    });
  });
}

checkAll.addEventListener("change", (e) => {
  const checkboxes = document.querySelectorAll(
    'input[name="contribuyentes[]"]'
  );
  checkboxes.forEach((checkbox) => {
    checkbox.checked = e.target.checked;
  });
});

$("#tableEnvios").DataTable(configTable);

let cursorPosition = 0;

// Guardar la posición del cursor
input.addEventListener("mouseup", () => {
  cursorPosition = input.selectionStart;
});

input.addEventListener("keyup", () => {
  cursorPosition = input.selectionStart;
});

// Toggle del emoji picker
btn.addEventListener("click", () => {
  if (pickerContainer.style.display === "block") {
    pickerContainer.style.display = "none";
    return;
  }

  pickerContainer.style.display = "block";
  pickerContainer.style.position = "absolute";
  pickerContainer.style.top = `100px`;
  pickerContainer.style.left = `10px`;
});

// Insertar emoji en el input
picker.addEventListener("emoji-click", (event) => {
  const emoji = event.detail.unicode;
  const start = input.selectionStart;
  const end = input.selectionEnd;

  input.value = input.value.slice(0, start) + emoji + input.value.slice(end);
  input.focus();
  input.selectionStart = input.selectionEnd = start + emoji.length;

  pickerContainer.style.display = "none";
});

// Ocultar si se hace clic fuera
document.addEventListener("click", (e) => {
  if (!pickerContainer.contains(e.target) && e.target !== btn) {
    pickerContainer.style.display = "none";
  }
});

function wrapWithAsterisks() {
  const start = input.selectionStart;
  const end = input.selectionEnd;

  let selectedText = input.value.substring(start, end);
  if (!selectedText) return;

  const before = input.value.substring(0, start);
  const after = input.value.substring(end);

  const hasAsterisks =
    selectedText.startsWith("*") && selectedText.endsWith("*");

  if (hasAsterisks) {
    // Si ya tiene asteriscos, los quitamos
    selectedText = selectedText.slice(1, -1);
    input.value = before + selectedText + after;
    input.setSelectionRange(start, end - 2);
  } else {
    // Si no los tiene, los agregamos
    selectedText = `*${selectedText}*`;
    input.value = before + selectedText + after;
    input.setSelectionRange(start, end + 2);
  }

  input.focus();
}

function wrapWithCursive() {
  const start = input.selectionStart;
  const end = input.selectionEnd;

  let selectedText = input.value.substring(start, end);
  if (!selectedText) return;

  const before = input.value.substring(0, start);
  const after = input.value.substring(end);

  const hasAsterisks =
    selectedText.startsWith("_") && selectedText.endsWith("_");

  if (hasAsterisks) {
    // Si ya tiene asteriscos, los quitamos
    selectedText = selectedText.slice(1, -1);
    input.value = before + selectedText + after;
    input.setSelectionRange(start, end - 2);
  } else {
    // Si no los tiene, los agregamos
    selectedText = `_${selectedText}_`;
    input.value = before + selectedText + after;
    input.setSelectionRange(start, end + 2);
  }

  input.focus();
}

function wrapWithCross() {
  const start = input.selectionStart;
  const end = input.selectionEnd;

  let selectedText = input.value.substring(start, end);
  if (!selectedText) return;

  const before = input.value.substring(0, start);
  const after = input.value.substring(end);

  const hasAsterisks =
    selectedText.startsWith("~") && selectedText.endsWith("~");

  if (hasAsterisks) {
    // Si ya tiene asteriscos, los quitamos
    selectedText = selectedText.slice(1, -1);
    input.value = before + selectedText + after;
    input.setSelectionRange(start, end - 2);
  } else {
    // Si no los tiene, los agregamos
    selectedText = `~${selectedText}~`;
    input.value = before + selectedText + after;
    input.setSelectionRange(start, end + 2);
  }

  input.focus();
}

const addVariable = document.getElementById("addVariable");

addVariable.addEventListener("click", () => {
  $("#modalVariables").modal("show");
  cursorPosition = input.selectionStart;
});

const modalBody = document.getElementById("modalBody");

modalBody.addEventListener("click", (e) => {
  if (e.target.classList.contains("variable")) {
    let variable = e.target.getAttribute("data-info");
    variable = `{{${variable}}}`;

    const originalText = input.value;
    input.value =
      originalText.slice(0, cursorPosition) +
      variable +
      originalText.slice(cursorPosition);
    // Actualizar posición del cursor
    cursorPosition += variable.length;
    input.focus();
    input.setSelectionRange(cursorPosition, cursorPosition);

    $("#modalVariables").modal("hide");
  }
});

const schedulingImmediateRadio = document.getElementById('schedulingImmediate');
    const schedulingProgrammedRadio = document.getElementById('schedulingProgrammed');
    const schedulingOptions = document.getElementById('schedulingOptions');
    
    // Función para mostrar/ocultar el panel de programación
    function toggleSchedulingOptions() {
        if (schedulingProgrammedRadio.checked) {
            schedulingOptions.style.display = 'flex';
        } else {
            schedulingOptions.style.display = 'none';
        }
    }
    
    // Eventos para detectar cambios en los radios
    schedulingImmediateRadio.addEventListener('change', toggleSchedulingOptions);
    schedulingProgrammedRadio.addEventListener('change', toggleSchedulingOptions);
    
    // Configurar fecha mínima al día actual
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const formattedToday = `${yyyy}-${mm}-${dd}`;
    
    document.getElementById('scheduledDate').min = formattedToday;
