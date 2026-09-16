// ---------------------------------------------------------------------------
// Citrus Zone · helpers de UI compartidos entre pantallas
// ---------------------------------------------------------------------------

/**
 * Protege una pantalla: si no hay sesión, redirige a login.
 * Si se pasan roles y el usuario no tiene ninguno de ellos, redirige a inicio.
 * Llamar apenas carga el <script> de la página, antes de pintar nada sensible.
 */
function requireAuth(roles = null) {
  if (!Auth.isLoggedIn()) {
    window.location.href = window.location.pathname.includes("/admin/") ? "../login.html" : "login.html";
    return null;
  }
  const user = Auth.getUser();
  if (roles && !roles.includes(user.role)) {
    window.location.href = window.location.pathname.includes("/admin/") ? "../inicio.html" : "inicio.html";
    return null;
  }
  return user;
}

function pageUrl(page) {
  return window.location.pathname.includes("/admin/") ? `../${page}` : page;
}

/** Si ya hay sesión activa, saca al usuario de login/registro y lo manda a su home. */
function redirectIfLoggedIn() {
  if (Auth.isLoggedIn()) {
    const user = Auth.getUser();
    window.location.href = (user.role === "admin" || user.role === "superadmin") ? "admin/panel.html" : "inicio.html";
  }
}

/** Completa el "quién soy" del header y engancha el botón de salir. */
function paintAccountHeader() {
  const user = Auth.getUser();
  if (!user) return;
  document.querySelectorAll("[data-who]").forEach(el => {
    const roleLabel = user.role === "user" ? "" : ` (${user.role})`;
    el.textContent = `${user.name}${roleLabel}`;
  });
  document.querySelectorAll("[data-logout]").forEach(el => {
    el.addEventListener("click", (e) => {
      e.preventDefault();
      Auth.logout();
    });
  });
}

function showError(el, message) {
  if (!el) return;
  el.textContent = message;
  el.classList.add("visible");
}

function hideError(el) {
  if (!el) return;
  el.textContent = "";
  el.classList.remove("visible");
}

function showSuccess(el, message) {
  if (!el) return;
  el.textContent = message;
  el.classList.add("visible");
}

function formatDateTime(isoString) {
  const d = new Date(isoString);
  return d.toLocaleString("es-AR", { day: "2-digit", month: "2-digit", year: "numeric", hour: "2-digit", minute: "2-digit" });
}

function formatPrice(value) {
  return new Intl.NumberFormat("es-AR", { style: "currency", currency: "ARS", maximumFractionDigits: 0 }).format(value);
}

const STATUS_LABELS = {
  pendiente: { text: "Pendiente", cls: "warning" },
  confirmada: { text: "Confirmada", cls: "" },
  cancelada: { text: "Cancelada", cls: "muted-pill" },
};

function statusPillHtml(status) {
  const info = STATUS_LABELS[status] || { text: status, cls: "" };
  return `<span class="status-pill ${info.cls}">${info.text}</span>`;
}
