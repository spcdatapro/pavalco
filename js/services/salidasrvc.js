(function(){

    var salidasrvc = angular.module('cpm.salidasrvc', ['cpm.comunsrvc']);

    salidasrvc.factory('salidaSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/salida.php';

        return {
            lstAllSalidas: function(){
                return comunFact.doGET(urlBase + '/lstallsalidas');
            },
            lstSalidasPorCaso: function(idcaso){
                return comunFact.doGET(urlBase + '/lstsalidasporcaso/' + idcaso);
            },
            getSalida: function(idsalida){
                return comunFact.doGET(urlBase + '/getsalida/' + idsalida);
            },
            lstDetalleSalida: function(idsalida){
                return comunFact.doGET(urlBase + '/lstdetsalida/' + idsalida);
            },
            getDetalleSalida: function(iddetsalida){
                return comunFact.doGET(urlBase + '/getdetsalida/' + iddetsalida);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());