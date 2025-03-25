$(document).ready(function () {
  // Desactiva los mensajes de error de DataTables
  $.fn.dataTable.ext.errMode = 'none';
  
  // 1. Ordenación personalizada para fechas en formato dd/mm/yyyy (tipo "date-uk")
  $.fn.dataTable.ext.type.order['date-uk-pre'] = function(d) {
    if (d && d.trim() !== "") {
      var ukDatea = d.split('/');
      return new Date(ukDatea[2], ukDatea[1] - 1, ukDatea[0]).getTime();
    } else {
      return 0;
    }
  };

  // 2. Inicializar jQuery UI Datepicker en todos los inputs de tipo date
  $('input[type="date"]').each(function () {
    $(this).attr('type', 'text');
    $(this).datepicker({
      dateFormat: 'dd/mm/yy'
    });
  });

  // Inicializar DataTables solo en las tablas con la clase "sortable"
  $("table.sortable").each(function () {
    const $table = $(this);
    const tableId = $table.attr('id') || 'sin-id';

    console.log(`🔍 Verificando tabla: ${tableId}`);

    // Evitar doble inicialización
    if ($table.data('initialized')) {
      console.warn(`⚠️ La tabla '${tableId}' ya fue inicializada. Ignorando nueva inicialización.`);
      return;
    }

    // Construir columnDefs dinámicamente: cada columna (excepto ID) ordena por sí misma + ID
    const colCount = $table.find("thead tr th").length;
    let columnDefs = [];
    
    // Si la tabla NO tiene la clase 'allow-last-order', deshabilitar el ordenamiento en la última columna
    if (!$table.hasClass('allow-last-order')) {
      columnDefs.push({ orderable: false, targets: -1 });
    }
    
    // Asegurar que la primera columna es visible
    columnDefs.push({ visible: true, targets: 0 });
    
    // Para el resto de las columnas, agregar orderData, excepto la última si está deshabilitada
    for (let i = 1; i < colCount; i++) {
      // Si es la última columna y no se permite ordenarla, se omite
      if (i === colCount - 1 && !$table.hasClass('allow-last-order')) {
        continue;
      }
      columnDefs.push({ targets: i, orderData: [i, 0] });
    }

    console.log(`✅ Inicializando DataTable para la tabla: ${tableId}`);

    $table.DataTable({
      deferRender: true,
      responsive: true,
      destroy: true,
      pageLength: 25,
      lengthMenu: [10, 25, 50, 100],
      dom: '<"datatable-header d-flex justify-content-between align-items-center"lfB>rt<"datatable-footer d-flex justify-content-between align-items-center"ip>',
      language: {
        emptyTable: "No hay datos disponibles en la tabla.",
        zeroRecords: "No se encontraron resultados.",
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 a 0 de 0 registros",
        infoFiltered: "(filtrado de _MAX_ registros totales)",
        search: "Buscar:",
        lengthMenu: "Mostrar _MENU_ registros",
        paginate: {
          first: "Primero",
          last: "Último",
          next: "Siguiente",
          previous: "Anterior"
        }
      },
      columnDefs: columnDefs,
      initComplete: function (settings) {
        console.info(`✅ DataTable inicializado correctamente: ${settings.nTable.id}`);
        $table.data('initialized', true);
        $table.find("th:first-child, td:first-child").css("display", "table-cell");
      }
    });
  });
});
