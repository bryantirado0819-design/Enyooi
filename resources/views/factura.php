<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Factura Zafiros - ENYOOI</title>
<link href="public/assets/css/factura.css" rel="stylesheet">
</head>
<body>
<div id="modal-factura" class="flex">
  <div class="modal-content">
    <button id="cerrar-modal">Cerrar</button>
    <iframe id="iframe-factura"></iframe>
  </div>
</div>

<script>
function mostrarFactura(compra) {
    const modal = document.getElementById('modal-factura');
    const iframe = document.getElementById('iframe-factura');

    fetch('app/Controllers/FacturaController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ compra: compra })
    })
    .then(res => res.blob())
    .then(blob => {
        const url = URL.createObjectURL(blob);
        iframe.src = url;
        modal.style.display = 'flex';
    });

    document.getElementById('cerrar-modal').onclick = () => {
        modal.style.display = 'none';
        iframe.src = '';
    };
}

// Ejemplo de uso (simulación de compra)
document.addEventListener('DOMContentLoaded', ()=>{
    const compra = {usuario:'Alice', email:'alice@example.com', cantidad:100, precio:0.1};
    mostrarFactura(compra);
});
</script>
</body>
</html>
