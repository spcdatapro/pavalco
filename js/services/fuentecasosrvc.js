(function(){

    var fuentecasosrvc = angular.module('cpm.fuentecasosrvc', ['cpm.comunsrvc']);

    fuentecasosrvc.factory('fuenteCasoSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/fuentecaso.php';

        return {
            lstFuentesCaso: function(){
                return comunFact.doGET(urlBase + '/lstfuentescaso');
            },
            getFuenteCaso: function(idfuentecaso){
                return comunFact.doGET(urlBase + '/getfuentecaso/' + idfuentecaso);
            },
            lstAllTiposCaso: function(){
                return comunFact.doGET(urlBase + '/lstalltiposcaso');
            },
            lstTiposCasoByFuente: function(idfuentecaso){
                return comunFact.doGET(urlBase + '/lsttiposcasobyfuente/' + idfuentecaso);
            },
            getTipoCaso: function(idtipocaso){
                return comunFact.doGET(urlBase + '/gettipocaso/' + idtipocaso);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());