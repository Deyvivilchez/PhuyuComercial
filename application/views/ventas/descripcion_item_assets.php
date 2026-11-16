<link rel="stylesheet" href="<?php echo base_url(); ?>public/plantilla_phuyu/libs/quill/quill.snow.css">
<style>
#modal_itemdetalle .phuyu-descripcion-editor { background:#fff; border-radius:6px; }
#modal_itemdetalle .ql-toolbar { border-color:#dce0eb; border-radius:6px 6px 0 0; }
#modal_itemdetalle .ql-container { border-color:#dce0eb; font-family:inherit; font-size:14px; }
#modal_itemdetalle .ql-editor { min-height:160px; max-height:280px; overflow-y:auto; line-height:1.6; }
#modal_itemdetalle .ql-editor.ql-blank::before { font-style:normal; color:#8c93a4; }
#modal_itemdetalle .modal-body { max-height:calc(100vh - 150px); overflow-y:auto; }
</style>
<script src="<?php echo base_url(); ?>public/plantilla_phuyu/libs/quill/quill.min.js"></script>
<script src="<?php echo base_url(); ?>phuyu/phuyu_ventas/descripcion_item.js?v=<?php echo filemtime(FCPATH . 'phuyu/phuyu_ventas/descripcion_item.js'); ?>"></script>
