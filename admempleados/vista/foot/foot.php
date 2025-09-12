<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
    integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous">
</script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
</script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/2.3.0/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.0/js/dataTables.bootstrap5.min.js"></script>

<script>
  // id_usuario (string) => "Apellido Nombre (usuario)" o "usuario"
  const EMP_USUARIOS = <?php
    $map = [];
    foreach (($usuarios ?? []) as $u) {
      $label = trim($u['nomyapellido'] ?? '') !== ''
        ? ($u['nomyapellido'].' ('.$u['usuario'].')')
        : $u['usuario'];
      $map[(string)$u['id']] = $label; // 👈 clave como STRING
    }
    echo json_encode($map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  ?>;

  // id_provincia (string) => "Provincia"
  const EMP_PROVINCIAS = <?php
    $provMap = [];
    foreach (($provincias ?? []) as $p) {
      $provMap[(string)$p['id_provincia']] = $p['provincia']; // 👈 clave como STRING
    }
    echo json_encode($provMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  ?>;
</script>


<script src="../../rsc/script/scriptEmpleado.js"></script>

<script>
    (function() {
        const selProv = document.getElementById('emp_provincia');
        const selLoc = document.getElementById('emp_localidad');
        if (!selProv || !selLoc) return;

        selProv.addEventListener('change', function(e) {
            const idProv = e.target.value;
            selLoc.innerHTML = '<option value="">Cargando localidades...</option>';

            const xhr = new XMLHttpRequest();
            // Usamos ruta ABSOLUTA al endpoint existente de Proveedor
            xhr.open('GET', '/Bakersoft/proveedores/controlador/traerLocalidades.php?id_provincia=' + encodeURIComponent(idProv), true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const cities = JSON.parse(xhr.responseText);
                            selLoc.innerHTML = '<option value="">-- Seleccionar --</option>';
                            cities.forEach(function(c) {
                                const opt = document.createElement('option');
                                opt.value = c.id_localidad;
                                opt.text = c.localidad;
                                selLoc.appendChild(opt);
                            });
                        } catch (e) {
                            selLoc.innerHTML = '<option value="">Error cargando localidades</option>';
                        }
                    } else {
                        selLoc.innerHTML = '<option value="">Error cargando localidades</option>';
                    }
                }
            };
            xhr.send();
        });
    })();
</script>


</body>

</html>