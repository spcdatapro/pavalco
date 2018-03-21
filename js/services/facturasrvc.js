(function(){

    var facturasrvc = angular.module('cpm.facturasrvc', ['cpm.comunsrvc']);

    facturasrvc.factory('facturaSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/factura.php';

        var facturaSrvc = {
            lstFacturas: function(todas){
                return comunFact.doGET(urlBase + '/lstfacturas/' + todas);
            },
            getFactura: function(idfactura){
                return comunFact.doGET(urlBase + '/getfactura/' + idfactura);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
        return facturaSrvc;
    }]);

}());
