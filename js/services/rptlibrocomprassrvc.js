(function(){

    var rptlibcompsrvc = angular.module('cpm.rptlibcompsrvc', ['cpm.comunsrvc']);

    rptlibcompsrvc.factory('rptLibroComprasSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/rptlibrocompras.php';

        var rptLibroComprasSrvc = {
            rptLibroCompras: function(idempresa, mes, anio){
                return comunFact.doGET(urlBase + '/rptlibcomp/' + idempresa + '/' + mes + '/' + anio);
            }
        };
        return rptLibroComprasSrvc;
    }]);

}());
