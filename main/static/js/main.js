// /home/innovaol/girapp/main/static/js/main.js

console.log("🟢 main.js está corriendo");

function getCSRFToken() {
  const name = 'csrftoken';
  const cookies = document.cookie.split(';');
  for (let i = 0; i < cookies.length; i++) {
    const cookie = cookies[i].trim();
    if (cookie.startsWith(name + '=')) {
      return decodeURIComponent(cookie.substring(name.length + 1));
    }
  }
  return '';
}

function confirmDelete(itemId, deleteUrl, archiveUrl, itemName = "elemento") {
  if (confirm(`¿Estás seguro de que deseas eliminar este ${itemName}?`)) {
    fetch(deleteUrl, {
      method: "POST",
      headers: {
        "X-CSRFToken": getCSRFToken()
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        localStorage.setItem("flashMessage", `✅ ${itemName.charAt(0).toUpperCase() + itemName.slice(1)} eliminado correctamente.`);
        localStorage.setItem("flashMessageType", "success");
        setTimeout(() => location.reload(), 200);
      } else if (data.archivable) {
        if (confirm(data.error + " ¿Deseas archivarlo en su lugar?")) {
          fetch(archiveUrl, {
            method: "POST",
            headers: {
              "X-CSRFToken": getCSRFToken()
            }
          })
          .then(response => response.json())
          .then(result => {
            if (result.success) {
              localStorage.setItem("flashMessage", result.message || `✅ ${itemName} archivado correctamente.`);
              localStorage.setItem("flashMessageType", "success");
              setTimeout(() => location.reload(), 200);
            } else {
              showToast(result.error || "❌ No se pudo archivar.", "danger");
            }
          });
        }
      } else {
        showToast(data.error || "❌ No se pudo eliminar.", "danger");
      }
    })
    .catch(error => {
      console.error("Error al eliminar:", error);
      showToast("❌ Error de red al intentar eliminar.", "danger");
    });
  }
}


function showToast(message, type = "success") {
  const alertDiv = document.createElement("div");
  alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-2`;
  alertDiv.setAttribute("role", "alert");

  alertDiv.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
  `;

  const container = document.getElementById("flash-messages");

  if (container) {
    container.appendChild(alertDiv);
  }
}

// 🔁 Mostrar mensaje guardado luego del reload
document.addEventListener("DOMContentLoaded", function () {
  const msg = localStorage.getItem("flashMessage");
  const type = localStorage.getItem("flashMessageType");

  if (msg) {
    showToast(msg, type || "success");
    localStorage.removeItem("flashMessage");
    localStorage.removeItem("flashMessageType");
  }
});

