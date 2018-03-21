(function(){

    var casosrvc = angular.module('cpm.casosrvc', ['cpm.comunsrvc']);

    casosrvc.factory('casoSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/caso.php';

        return {
            lstCasosAbiertos: function(){
                return comunFact.doPOST(urlBase + '/lstabiertos');
            },
            lstCasosCerrados: function(obj){
                return comunFact.doPOST(urlBase + '/lstcerrados', obj);
            },
            getCaso: function(idcaso){
                return comunFact.doGET(urlBase + '/getcaso/' + idcaso);
            },
            visitado: function(idcaso){
                return comunFact.doGET(urlBase + '/visitado/' + idcaso);
            },
            lstBitacoras: function(idcaso){
                return comunFact.doGET(urlBase + '/lstbitacoras/' + idcaso);
            },
            getBitacora: function(idbitacora){
                return comunFact.doGET(urlBase + '/getbitacora/' + idbitacora);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());
