            <div v-show="descripcion_editor_activo" class="phuyu-descripcion-editor">
              <div id="descripcion_item_editor" aria-label="Descripcion detallada del producto o servicio"></div>
            </div>
            <textarea v-if="!descripcion_editor_activo" class="form-control" v-model="item.descripcion" rows="6" maxlength="5000" placeholder="Detalle del servicio, alcance, condiciones y observaciones..."></textarea>
            <div class="d-flex justify-content-between gap-2 mt-1 small text-muted">
              <span>Párrafos y listas para detallar productos o servicios.</span>
              <span v-bind:class="{'text-danger': (item.descripcion || '').length > 5000}">{{ (item.descripcion || '').length }} / 5000</span>
            </div>
