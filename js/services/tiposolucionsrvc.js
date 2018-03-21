(function(){

    var tiposolucionsrvc = angular.module('cpm.tiposolucionsrvc', ['cpm.comunsrvc']);

    tiposolucionsrvc.factory('tipoSolucionSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/tiposolucion.php';

        return {
            lstTiposSolucion: function(){
                return comunFact.doGET(urlBase + '/lsttipossolucion');
            },
            getTipoSolucion: function(idtiposolucion){
                return comunFact.doGET(urlBase + '/gettiposolucion/' + idtiposolucion);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());