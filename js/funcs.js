//Funciones para todo el sitio...
function rowCallback(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
    if(aData[0] == 'Total de partida'){
        var debe = parseFloat(parseFloat(aData[1].replace(',', '')).toFixed(2));
        var haber = parseFloat(parseFloat(aData[2].replace(',', '')).toFixed(2));
        var qClase = debe === haber ? 'partida-cuadrada' : 'partida-descuadrada';
        $('td:eq(0)', nRow).addClass(qClase);
        $('td:eq(1)', nRow).addClass(qClase);
        $('td:eq(2)', nRow).addClass(qClase);
        $('td:eq(3)', nRow).addClass(qClase);
    }
}

function PrintElem(elem, tituloPagina){
    //Popup($(elem).html(), tituloPagina);
    setTimeout(function(){ Popup($(elem).html(), tituloPagina); }, 1000);
}

function Popup(data, tituloPagina){
    var mywindow = window.open('', 'PrintVer', 'height=400,width=600');
    mywindow.document.write('<html><head><title>' + tituloPagina + '</title>');
    mywindow.document.write('<link rel="stylesheet" href="libs/bootstrap/css/bootstrap.min.css">');
    mywindow.document.write('<link rel="stylesheet" href="libs/bootstrap/css/bootstrap-theme.min.css" >');
    mywindow.document.write('<link rel="stylesheet" href="libs/node_modules/angularjs-toaster/toaster.min.css">');
    mywindow.document.write('<link rel="stylesheet" href="libs/angularxeditable/css/xeditable.css">');
    mywindow.document.write('<link rel="stylesheet" href="libs/datatables/css/jquery.dataTables.min.css">');
    mywindow.document.write('<link rel="stylesheet" href="libs/datatables/css/datatables.bootstrap.min.css">');
    mywindow.document.write('<link rel="stylesheet" href="css/styles.css">');
    mywindow.document.write('<style>div, table, tr, td, th, h1, h2, h3, h4 { font-size: x-small; }</style>');
    mywindow.document.write('</head><body>');
    mywindow.document.write(data);
    mywindow.document.write('<script type="text/javascript" src="libs/jquery/jquery-1.11.3.min.js"></script>');
    mywindow.document.write('<script type="text/javascript" src="libs/angularjs/angular.min.js"></script>');
    mywindow.document.write('<script type="text/javascript" src="libs/bootstrap/js/bootstrap.min.js"></script>');
    mywindow.document.write('<script type="text/javascript" src="libs/uibootstrap/ui-bootstrap-tpls-1.1.0.min.js"></script>');
    mywindow.document.write('<script type="text/javascript" src="js/funcs.js"></script>');
    mywindow.document.write('<script type="text/javascript">hideTableInPrintIdEndsWith("_1");</script>');
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10

    //mywindow.print();
    //mywindow.close();
    setTimeout(function(){mywindow.print();}, 500);
    setTimeout(function(){mywindow.close();}, 1000);

    return true;
}

function hideTableInPrintIdEndsWith(val){ $("table[id$='" + val + "']").hide(); }

function goTop(){ $('html, body').animate({ scrollTop: 0 }, 'fast'); }

function pad(num, size) {
    var s = num+"";
    while (s.length < size) s = "0" + s;
    return s;
}

