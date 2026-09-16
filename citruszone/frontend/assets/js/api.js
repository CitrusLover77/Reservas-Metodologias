// ---------------------------------------------------------------------------
// Citrus Zone · cliente de API
// Maneja el token JWT (localStorage), arma los headers y normaliza errores.
// ---------------------------------------------------------------------------

const TOKEN_KEY = "cz_token";
const USER_KEY = "cz_user";

const Auth = {
  getToken() {
    return localStorage.getItem(TOKEN_KEY);
  },
  getUser() {
    const raw = localStorage.getItem(USER_KEY);
    return raw ? JSON.parse(raw) : null;
  },
  setSession(token, user) {
    localStorage.setItem(TOKEN_KEY, token);
    localStorage.setItem(USER_KEY, JSON.stringify(user));
  },
  clearSession() {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
  },
  isLoggedIn() {
    return !!this.getToken();
  },
  hasRole(...roles) {
    const user = this.getUser();
    return !!user && roles.includes(user.role);
  },
  logout() {
    this.clearSession();
    window.location.href = window.location.pathname.includes("/admin/") ? "../login.html" : "login.html";
  },
};

/**
 * Llama al backend y devuelve el JSON ya parseado.
 * Lanza un Error con .status y .payload cuando la respuesta no es ok,
 * para que cada pantalla pueda mostrar el mensaje que mande el backend.
 */
async function apiFetch(path, { method = "GET", body, auth = true } = {}) {
  const headers = { "Content-Type": "application/json" };

  if (auth) {
    const token = Auth.getToken();
    if (token) headers["Authorization"] = `Bearer ${token}`;
  }

  let response;
  try {
    response = await fetch(`${API_BASE_URL}${path}`, {
      method,
      headers,
      body: body !== undefined ? JSON.stringify(body) : undefined,
    });
  } catch (networkError) {
    const err = new Error("No se pudo conectar con el servidor. Verificá que el backend esté corriendo.");
    err.status = 0;
    throw err;
  }

  let payload = null;
  const text = await response.text();
  if (text) {
    try {
      payload = JSON.parse(text);
    } catch {
      payload = null;
    }
  }

  if (response.status === 401 && auth) {
    // Sesión vencida o inválida: forzamos re-login.
    Auth.clearSession();
    window.location.href = "login.html";
    return;
  }

  if (!response.ok) {
    const message = (payload && (payload.error || payload.message)) || `Error ${response.status}`;
    const err = new Error(message);
    err.status = response.status;
    err.payload = payload;
    throw err;
  }

  return payload;
}

const Api = {
  // ---- Auth ----
  login(email, password) {
    return apiFetch("/auth/login", { method: "POST", auth: false, body: { email, password } });
  },
  register(data) {
    return apiFetch("/auth/register", { method: "POST", auth: false, body: data });
  },
  me() {
    return apiFetch("/auth/me");
  },

  // ---- Servicios ----
  listServices({ onlyActive = false } = {}) {
    return apiFetch(`/servicios${onlyActive ? "?activo=1" : ""}`);
  },
  createService(data) {
    return apiFetch("/servicios", { method: "POST", body: data });
  },
  updateService(id, data) {
    return apiFetch(`/servicios/${id}`, { method: "PUT", body: data });
  },

  // ---- Profesionales ----
  listProfessionals({ servicioId } = {}) {
    const qs = servicioId ? `?servicio_id=${servicioId}` : "";
    return apiFetch(`/profesionales${qs}`);
  },

  // ---- Disponibilidad ----
  getAvailability({ servicioId, profesionalId, fecha }) {
    return apiFetch(`/disponibilidad?servicio_id=${servicioId}&profesional_id=${profesionalId}&fecha=${fecha}`);
  },

  // ---- Reservas ----
  listMyBookings() {
    return apiFetch("/reservas/mias");
  },
  createBooking(data) {
    return apiFetch("/reservas", { method: "POST", body: data });
  },
  cancelBooking(id) {
    return apiFetch(`/reservas/${id}/cancelar`, { method: "POST" });
  },
  listAllBookings() {
    return apiFetch("/reservas");
  },

  // ---- Horarios laborales ----
  listSchedules() {
    return apiFetch("/horarios");
  },
  createSchedule(data) {
    return apiFetch("/horarios", { method: "POST", body: data });
  },

  // ---- Bloqueos ----
  listBlocks() {
    return apiFetch("/bloqueos");
  },
  createBlock(data) {
    return apiFetch("/bloqueos", { method: "POST", body: data });
  },
  deleteBlock(id) {
    return apiFetch(`/bloqueos/${id}`, { method: "DELETE" });
  },
};
