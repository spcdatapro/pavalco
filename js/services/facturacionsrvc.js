(function(){

    var facturacionsrvc = angular.module('cpm.facturacionsrvc', ['cpm.comunsrvc']);

    facturacionsrvc.factory('facturacionSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/facturacion.php';

        return {
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            },
            getCargos: function(obj){
                return comunFact.doPOST(urlBase + '/getcargos', obj);
            },
            facturar: function(obj){
                return comunFact.doPOST(urlBase + '/facturar', obj);
            }
        };
    }]);

}());
