<!-- ========== THEME SETTINGS MODAL (DEFAULT: light-blue + nav light + vertical) ========== -->
<div
  class="modal fade modal-right scroll-out-negative"
  id="settings"
  data-bs-backdrop="true"
  tabindex="-1"
  role="dialog"
  aria-labelledby="settings"
  aria-hidden="true"
>
  <div class="modal-dialog modal-dialog-scrollable full" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Theme Settings</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="scroll-track-visible">

          <!-- Botón Reset dentro del modal -->
          <div class="row my-2 mb-4">
            <button class="btn btn-success" id="resetThemeBtn">Resetear tema a valores por defecto</button>
          </div>

          <!-- Color -->
          <div class="mb-5" id="color">
            <label class="mb-3 d-inline-block form-label">Color</label>

            <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
              <a href="#" class="flex-grow-1 w-50 option col" data-value="light-blue" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="blue-light"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">LIGHT BLUE</span>
                </div>
              </a>
              <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-blue" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="blue-dark"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">DARK BLUE</span>
                </div>
              </a>
            </div>

            <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
              <a href="#" class="flex-grow-1 w-50 option col" data-value="light-teal" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="teal-light"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">LIGHT TEAL</span>
                </div>
              </a>
              <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-teal" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="teal-dark"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">DARK TEAL</span>
                </div>
              </a>
            </div>

            <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
              <a href="#" class="flex-grow-1 w-50 option col" data-value="light-sky" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="sky-light"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">LIGHT SKY</span>
                </div>
              </a>
              <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-sky" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="sky-dark"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">DARK SKY</span>
                </div>
              </a>
            </div>

            <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
              <a href="#" class="flex-grow-1 w-50 option col" data-value="light-red" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="red-light"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">LIGHT RED</span>
                </div>
              </a>
              <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-red" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="red-dark"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">DARK RED</span>
                </div>
              </a>
            </div>

            <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
              <a href="#" class="flex-grow-1 w-50 option col" data-value="light-green" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="green-light"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">LIGHT GREEN</span>
                </div>
              </a>
              <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-green" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="green-dark"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">DARK GREEN</span>
                </div>
              </a>
            </div>

            <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
              <a href="#" class="flex-grow-1 w-50 option col" data-value="light-lime" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="lime-light"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">LIGHT LIME</span>
                </div>
              </a>
              <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-lime" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="lime-dark"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">DARK LIME</span>
                </div>
              </a>
            </div>

            <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
              <a href="#" class="flex-grow-1 w-50 option col" data-value="light-pink" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="pink-light"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">LIGHT PINK</span>
                </div>
              </a>
              <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-pink" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="pink-dark"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">DARK PINK</span>
                </div>
              </a>
            </div>

            <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
              <a href="#" class="flex-grow-1 w-50 option col" data-value="light-purple" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="purple-light"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">LIGHT PURPLE</span>
                </div>
              </a>
              <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-purple" data-parent="color">
                <div class="card rounded-md p-3 mb-1 no-shadow color">
                  <div class="purple-dark"></div>
                </div>
                <div class="text-muted text-part">
                  <span class="text-extra-small align-middle">DARK PURPLE</span>
                </div>
              </a>
            </div>
          </div>

          <!-- Nav Palette -->
          <div class="mb-5" id="navcolor">
            <label class="mb-3 d-inline-block form-label">Override Nav Palette</label>
            <div class="row d-flex g-3 justify-content-between flex-wrap">
              <a href="#" class="flex-grow-1 w-33 option col" data-value="default" data-parent="navcolor">
                <div class="card rounded-md p-3 mb-1 no-shadow">
                  <div class="figure figure-primary top"></div>
                  <div class="figure figure-secondary bottom"></div>
                </div>
                <div class="text-muted text-part"><span class="text-extra-small align-middle">DEFAULT</span></div>
              </a>
              <a href="#" class="flex-grow-1 w-33 option col" data-value="light" data-parent="navcolor">
                <div class="card rounded-md p-3 mb-1 no-shadow">
                  <div class="figure figure-secondary figure-light top"></div>
                  <div class="figure figure-secondary bottom"></div>
                </div>
                <div class="text-muted text-part"><span class="text-extra-small align-middle">LIGHT</span></div>
              </a>
              <a href="#" class="flex-grow-1 w-33 option col" data-value="dark" data-parent="navcolor">
                <div class="card rounded-md p-3 mb-1 no-shadow">
                  <div class="figure figure-muted figure-dark top"></div>
                  <div class="figure figure-secondary bottom"></div>
                </div>
                <div class="text-muted text-part"><span class="text-extra-small align-middle">DARK</span></div>
              </a>
            </div>
          </div>

          <!-- Placement -->
          <div class="mb-5" id="placement">
            <label class="mb-3 d-inline-block form-label">Menu Placement</label>
            <div class="row d-flex g-3 justify-content-between flex-wrap">
              <a href="#" class="flex-grow-1 w-50 option col" data-value="horizontal" data-parent="placement">
                <div class="card rounded-md p-3 mb-1 no-shadow">
                  <div class="figure figure-primary top"></div>
                  <div class="figure figure-secondary bottom"></div>
                </div>
                <div class="text-muted text-part"><span class="text-extra-small align-middle">HORIZONTAL</span></div>
              </a>
              <a href="#" class="flex-grow-1 w-50 option col" data-value="vertical" data-parent="placement">
                <div class="card rounded-md p-3 mb-1 no-shadow">
                  <div class="figure figure-primary left"></div>
                  <div class="figure figure-secondary right"></div>
                </div>
                <div class="text-muted text-part"><span class="text-extra-small align-middle">VERTICAL</span></div>
              </a>
            </div>
          </div>

          <!-- Behaviour -->
          <div class="mb-5" id="behaviour">
            <label class="mb-3 d-inline-block form-label">Menu Behaviour</label>
            <div class="row d-flex g-3 justify-content-between flex-wrap">
              <a href="#" class="flex-grow-1 w-50 option col" data-value="pinned" data-parent="behaviour">
                <div class="card rounded-md p-3 mb-1 no-shadow">
                  <div class="figure figure-primary left large"></div>
                  <div class="figure figure-secondary right small"></div>
                </div>
                <div class="text-muted text-part"><span class="text-extra-small align-middle">PINNED</span></div>
              </a>
              <a href="#" class="flex-grow-1 w-50 option col" data-value="unpinned" data-parent="behaviour">
                <div class="card rounded-md p-3 mb-1 no-shadow">
                  <div class="figure figure-primary left"></div>
                  <div class="figure figure-secondary right"></div>
                </div>
                <div class="text-muted text-part"><span class="text-extra-small align-middle">UNPINNED</span></div>
              </a>
            </div>
          </div>

          <!-- Layout -->
          <div class="mb-5" id="layout">
            <label class="mb-3 d-inline-block form-label">Layout</label>
            <div class="row d-flex g-3 justify-content-between flex-wrap">
              <a href="#" class="flex-grow-1 w-50 option col" data-value="fluid" data-parent="layout">
                <div class="card rounded-md p-3 mb-1 no-shadow">
                  <div class="figure figure-primary top"></div>
                  <div class="figure figure-secondary bottom"></div>
                </div>
                <div class="text-muted text-part"><span class="text-extra-small align-middle">FLUID</span></div>
              </a>
              <a href="#" class="flex-grow-1 w-50 option col" data-value="boxed" data-parent="layout">
                <div class="card rounded-md p-3 mb-1 no-shadow">
                  <div class="figure figure-primary top"></div>
                  <div class="figure figure-secondary bottom small"></div>
                </div>
                <div class="text-muted text-part"><span class="text-extra-small align-middle">BOXED</span></div>
              </a>
            </div>
          </div>

          <!-- Radius -->
          <div class="mb-5" id="radius">
            <label class="mb-3 d-inline-block form-label">Radius</label>
            <div class="row d-flex g-3 justify-content-between flex-wrap">
              <a href="#" class="flex-grow-1 w-33 option col" data-value="rounded" data-parent="radius">
                <div class="card rounded-md radius-rounded p-3 mb-1 no-shadow">
                  <div class="figure figure-primary top"></div>
                  <div class="figure figure-secondary bottom"></div>
                </div>
                <div class="text-muted text-part"><span class="text-extra-small align-middle">ROUNDED</span></div>
              </a>
              <a href="#" class="flex-grow-1 w-33 option col" data-value="standard" data-parent="radius">
                <div class="card rounded-md radius-regular p-3 mb-1 no-shadow">
                  <div class="figure figure-primary top"></div>
                  <div class="figure figure-secondary bottom"></div>
                </div>
                <div class="text-muted text-part"><span class="text-extra-small align-middle">STANDARD</span></div>
              </a>
              <a href="#" class="flex-grow-1 w-33 option col" data-value="flat" data-parent="radius">
                <div class="card rounded-md radius-flat p-3 mb-1 no-shadow">
                  <div class="figure figure-primary top"></div>
                  <div class="figure figure-secondary bottom"></div>
                </div>
                <div class="text-muted text-part"><span class="text-extra-small align-middle">FLAT</span></div>
              </a>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>

<!-- ===== Estilos extra (opcional) ===== -->
<style>
  /* Variables globales por defecto (coinciden con login/administrar) */
  :root{
    --accent-1:#38bdf8; /* celeste */
    --accent-2:#6366f1; /* violeta */
    --bg-soft-start:#f8fafc;
    --bg-soft-end:#e0f2fe;
    --text-strong:#111827;
    --text-muted:#6b7280;
  }
  /* Marca la opción activa dentro del modal */
  #settings .option { cursor: pointer; }
  #settings .option.active .card { outline: 3px solid rgba(0,0,0,.2); }
  [data-bs-theme="dark"] #settings .option.active .card { outline-color: rgba(255,255,255,.35); }

  /* Botón Reset dentro del modal */
  #resetThemeBtn {
    width: 100%;
    margin-bottom: 1rem;
  }

  /* Botón Reset flotante (COMENTADO)
  #themeResetBtn{
    position: fixed; right: 20px; bottom: 20px; z-index: 1050;
    background: var(--accent-2); color: #fff; border: 0; border-radius: 999px;
    padding: 10px 16px; font-weight: 800; box-shadow: 0 6px 16px rgba(0,0,0,.2);
    cursor: pointer; transition: transform .15s ease, filter .15s ease;
  }
  #themeResetBtn:hover{ transform: translateY(-1px); filter: brightness(.97); }
  */
</style>

<!-- ===== Lógica del Theme Settings (DEFAULTS vertical + light-blue + nav light) ===== -->
<script>
(() => {
  const STORAGE_KEY = 'uiThemeConfig';
  const DEFAULTS = {
    color: 'light-blue',   // igual a login/administrar
    navcolor: 'light',     // nav claro por defecto
    placement: 'vertical', // menú vertical por defecto
    behaviour: 'pinned',
    layout: 'boxed',
    radius: 'rounded'
  };

  const ATTR_MAP = {
    color: 'data-color',
    navcolor: 'data-navcolor',
    placement: 'data-placement',
    behaviour: 'data-behaviour',
    layout: 'data-layout',
    radius: 'data-radius'
  };

  const htmlEl = document.documentElement;

  function getConfig() {
    try { return { ...DEFAULTS, ...(JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}')) }; }
    catch { return { ...DEFAULTS }; }
  }

  function applyTheme(cfg) {
    // Aplica atributos en <html>
    for (const [key, val] of Object.entries(cfg)) {
      const attr = ATTR_MAP[key];
      if (attr) htmlEl.setAttribute(attr, String(val));
    }

    // Persistencia
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cfg));
    markActive(cfg);
  }

  function markActive(cfg) {
    document.querySelectorAll('#settings .option.active').forEach(el => el.classList.remove('active'));
    Object.entries(cfg).forEach(([key,val]) => {
      const el = document.querySelector(`#settings .option[data-parent="${key}"][data-value="${val}"]`);
      if (el) el.classList.add('active');
    });
  }

  function init() {
    applyTheme(getConfig());

    // Delegación de eventos en el modal
    document.addEventListener('click', (e) => {
      const a = e.target.closest('#settings .option');
      if (a) {
        e.preventDefault();
        const parent = a.dataset.parent;
        const value  = a.dataset.value;
        if (!(parent in ATTR_MAP)) return;

        const next = { ...getConfig(), [parent]: value };
        applyTheme(next);
      }

      // Botón Reset dentro del modal
      if (e.target.id === 'resetThemeBtn' || e.target.closest('#resetThemeBtn')) {
        e.preventDefault();
        applyTheme({ ...DEFAULTS });
      }
    });

    // Botón Reset flotante (COMENTADO)
    // ensureResetBtn();
  }

  // Función para botón flotante (COMENTADA)
  /*
  function ensureResetBtn() {
    if (document.getElementById('themeResetBtn')) return;
    const btn = document.createElement('button');
    btn.id = 'themeResetBtn';
    btn.type = 'button';
    btn.textContent = '⟳ Reset tema';
    btn.onclick = () => applyTheme({ ...DEFAULTS });
    document.body.appendChild(btn);
  }
  */

  document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', init)
    : init();
})();
</script>