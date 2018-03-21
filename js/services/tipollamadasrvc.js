(function(){

    var tipollamadasrvc = angular.module('cpm.tipollamadasrvc', ['cpm.comunsrvc']);

    tipollamadasrvc.factory('tipoLlamadaSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/tipollamada.php';

        return {
            lstTiposLlamada: function(){
                return comunFact.doGET(urlBase + '/lsttiposllamada');
            },
            getTipoLlamada: function(idtipollamada){
                return comunFact.doGET(urlBase + '/gettipollamada/' + idtipollamada);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());
