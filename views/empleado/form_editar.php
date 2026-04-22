<?php
use Core\Config;
use Core\Security;

$esAprendiz = $esAprendiz ?? false;
$jefes      = $jefes      ?? [];
$tipos      = $tipos      ?? [];
$hoy        = $hoy        ?? date('Y-m-d');
$s          = $solicitud;
$baseUrl    = Config::baseUrl();
?>
<div class="page-header animate-fade-down">
  <h1 class="page-title">Editar Solicitud #<?= $s['ID'] ?></h1>
  <a href="<?= $baseUrl ?>/dashboard" class="btn btn-outline btn-sm">Volver</a>
</div>
<div class="form-shell animate-fade-up">
<div class="form-card">
  <form method="post" action="<?= $baseUrl ?>/solicitud/<?= $s['ID'] ?>/editar" id="formEditar" enctype="multipart/form-data">
    <?= Security::csrfField() ?>
    <?php if ($esAprendiz): ?>
    <div class="form-group">
      <label>Jefe responsable *</label>
      <select name="nit_jefe_seleccionado" required>
        <option value="">-- Selecciona un jefe --</option>
        <?php foreach ($jefes as $j): ?>
          <option value="<?= htmlspecialchars($j['NIT']) ?>" <?= $j['NIT'] === $s['NIT_JEFE'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($j['NOMBRE_COMPLETO']) ?> — CC <?= htmlspecialchars($j['CENTRO_COSTO']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <div class="form-group">
      <label>Tipo de solicitud *</label>
      <select name="tipo_solicitud" required>
        <?php foreach ($tipos as $val => $lbl): ?><option value="<?= $val ?>" <?= $s['TIPO_SOLICITUD'] === $val ? 'selected' : '' ?>><?= $lbl ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Fecha inicio *</label><input type="date" name="fecha_inicio" id="fecha_inicio" value="<?= substr($s['FECHA_INICIO'], 0, 10) ?>" min="<?= $hoy ?>" required/></div>
      <div class="form-group"><label>Fecha fin *</label><input type="date" name="fecha_fin" id="fecha_fin" value="<?= substr($s['FECHA_FIN'], 0, 10) ?>" min="<?= $hoy ?>" required/></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Duración en horas</label><input type="number" name="duracion_horas" id="duracion_horas" value="<?= $s['DURACION_HORAS'] ?? '' ?>" min="0" max="8" step="0.5"/><span class="field-hint">Máximo permitido: 8 horas</span></div>
      <div class="form-group"><label>Duración en días <span class="field-autocalc">Calculado</span></label><input type="number" name="duracion_dias" id="duracion_dias" value="<?= $s['DURACION_DIAS'] ?? '' ?>" min="0" max="365" step="0.5" readonly/></div>
    </div>
    <div class="form-group"><label>Observaciones</label><textarea name="observaciones" rows="4"><?= htmlspecialchars($s['OBSERVACIONES'] ?? '') ?></textarea></div>
    <div class="form-group">
      <label>Documento adjunto (PDF, máx. 5MB)</label>
      <?php if (!empty($s['RUTA_COMPROBANTE'])): ?>
      <div class="archivo-actual">
        <div class="archivo-adjunto">
          <span class="archivo-icono">📑</span>
          <div class="archivo-info">
            <div class="archivo-nombre"><?php
              $rawName = pathinfo(basename($s['RUTA_COMPROBANTE']), PATHINFO_FILENAME);
              $parts = explode('_', $rawName);
              $label = count($parts) >= 4 ? implode('_', array_slice($parts, 3)) : $rawName;
              echo htmlspecialchars($label ?: 'Documento') . '.pdf';
            ?></div>
            <div class="archivo-actual-label">Archivo actual</div>
          </div>
          <a href="<?= $baseUrl ?>/<?= htmlspecialchars($s['RUTA_COMPROBANTE']) ?>" target="_blank" class="archivo-ver">Ver PDF</a>
        </div>
        <label class="reemplazar-label">
          <input type="checkbox" name="reemplazar_pdf" value="1" id="chkReemplazar">
          <span>Reemplazar archivo</span>
        </label>
      </div>
      <?php endif; ?>
      <div class="file-upload-container" id="uploadContainer" <?= !empty($s['RUTA_COMPROBANTE']) ? 'style="display:none;"' : '' ?>>
        <input type="file" name="documento_pdf" id="documento_pdf" accept=".pdf,application/pdf" <?= empty($s['RUTA_COMPROBANTE']) ? 'required' : '' ?>/>
        <div class="file-upload-hint">
          <span class="hint-icon">📄</span>
          <span>Formato permitido: PDF. Tamaño máximo: 5MB</span>
        </div>
      </div>
      <div id="pdf-preview-container" class="pdf-preview-container" style="display:none;">
        <div class="pdf-preview-header">
          <span class="pdf-icon">📑</span>
          <span id="pdf-filename" class="pdf-filename"></span>
          <span id="pdf-size" class="pdf-size"></span>
          <button type="button" id="remove-pdf" class="remove-pdf-btn" title="Eliminar archivo">&times;</button>
        </div>
        <div class="pdf-preview-content">
          <iframe id="pdf-iframe" src="" frameborder="0"></iframe>
        </div>
      </div>
      <div id="file-error" class="file-error" style="display:none;"></div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-green">Guardar cambios</button>
      <a href="<?= $baseUrl ?>/dashboard" class="btn btn-gray">Cancelar</a>
    </div>
  </form>
</div>
</div>
<script <?= Security::scriptNonceAttr() ?>>
(function(){
  var hoy=<?= json_encode($hoy) ?>,ini=document.getElementById('fecha_inicio'),fin=document.getElementById('fecha_fin'),dias=document.getElementById('duracion_dias'),horas=document.getElementById('duracion_horas');
  function calcularDias(){
    if(!ini.value||!fin.value||fin.value<ini.value){dias.value='';return;}
    var inicio=new Date(ini.value+'T00:00:00');
    var cierre=new Date(fin.value+'T00:00:00');
    var diff=Math.round((cierre-inicio)/86400000)+1;
    dias.value=diff>0?diff:'';
  }
  function validarHoras(){
    if(!horas||horas.value==='')return true;
    if(parseFloat(horas.value)>8){horas.value='8';alert('La duración en horas no puede ser mayor a 8.');return false;}
    return true;
  }
  ini.addEventListener('change',function(){fin.min=ini.value||hoy;if(fin.value&&fin.value<ini.value)fin.value=ini.value;calcularDias();});
  fin.addEventListener('change',calcularDias);
  horas.addEventListener('change',validarHoras);
  document.getElementById('formEditar').addEventListener('submit',function(e){
    if(fin.value&&fin.value<ini.value){e.preventDefault();alert('La fecha fin no puede ser anterior al inicio.');}
    if(!validarHoras()){e.preventDefault();return;}
  });
  calcularDias();

  // Manejar checkbox de reemplazar PDF
  var chkReemplazar = document.getElementById('chkReemplazar');
  var uploadContainer = document.getElementById('uploadContainer');
  var fileInput = document.getElementById('documento_pdf');

  if (chkReemplazar) {
    chkReemplazar.addEventListener('change', function() {
      if (this.checked) {
        uploadContainer.style.display = 'block';
        fileInput.required = true;
      } else {
        uploadContainer.style.display = 'none';
        fileInput.required = false;
        clearFile();
      }
    });
  }

  // File upload handling
  const MAX_FILE_SIZE = 5 * 1024 * 1024;
  var previewContainer = document.getElementById('pdf-preview-container');
  var fileError = document.getElementById('file-error');
  var pdfFilename = document.getElementById('pdf-filename');
  var pdfSize = document.getElementById('pdf-size');
  var pdfIframe = document.getElementById('pdf-iframe');
  var removeBtn = document.getElementById('remove-pdf');

  function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    var k = 1024;
    var sizes = ['Bytes', 'KB', 'MB'];
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }

  function showError(msg) {
    fileError.textContent = msg;
    fileError.style.display = 'block';
    fileInput.value = '';
    previewContainer.style.display = 'none';
  }

  function hideError() {
    fileError.style.display = 'none';
    fileError.textContent = '';
  }

  function clearFile() {
    fileInput.value = '';
    previewContainer.style.display = 'none';
    pdfIframe.src = '';
    hideError();
  }

  fileInput.addEventListener('change', function() {
    hideError();
    var file = this.files[0];
    if (!file) {
      clearFile();
      return;
    }

    if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
      showError('Error: Solo se permiten archivos PDF.');
      return;
    }

    if (file.size > MAX_FILE_SIZE) {
      showError('Error: El archivo excede el tamaño máximo de 5MB.');
      return;
    }

    var fileUrl = URL.createObjectURL(file);
    pdfFilename.textContent = file.name;
    pdfSize.textContent = formatFileSize(file.size);
    pdfIframe.src = fileUrl;
    previewContainer.style.display = 'block';
  });

  removeBtn.addEventListener('click', clearFile);
})();
</script>
